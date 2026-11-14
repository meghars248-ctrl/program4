
<?php
session_start();
require_once "database.php";

// Only Admin users can manage camps.
if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    http_response_code(403);
    exit("Access denied. Please log in as Admin.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = trim($_POST["camp_name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $date = $_POST["camp_date"] ?? "";
        $start = $_POST["start_time"] ?? "";
        $end = $_POST["end_time"] ?? "";
        $organizer = trim($_POST["organizer"] ?? "");
        $description = trim($_POST["description"] ?? "");

        $validDate = DateTime::createFromFormat("!Y-m-d", $date);
        $validStart = DateTime::createFromFormat("!H:i", $start);
        $validEnd = DateTime::createFromFormat("!H:i", $end);

        if (
            $name === "" || $location === "" || $organizer === "" ||
            !$validDate || $validDate->format("Y-m-d") !== $date ||
            $date < date("Y-m-d") ||
            !$validStart || $validStart->format("H:i") !== $start ||
            !$validEnd || $validEnd->format("H:i") !== $end ||
            $end <= $start
        ) {
            $message = "Please enter valid camp details and timings.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO health_camps
                 (camp_name, location, camp_date, start_time, end_time,
                  organizer, description, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'Planned')"
            );

            $stmt->execute([
                $name, $location, $date, $start, $end,
                $organizer, $description
            ]);

            $message = "Health camp added successfully!";
        }
    } elseif ($action === "status") {
        $campId = filter_input(INPUT_POST, "camp_id", FILTER_VALIDATE_INT);
        $status = $_POST["status"] ?? "";

        if (
            !$campId ||
            !in_array($status, ["Planned", "Active", "Completed"], true)
        ) {
            $message = "Invalid camp status.";
        } else {
            $stmt = $pdo->prepare(
                "UPDATE health_camps SET status = ? WHERE camp_id = ?"
            );
            $stmt->execute([$status, $campId]);
            $message = "Camp status updated.";
        }
    }
}

$stmt = $pdo->query(
    "SELECT * FROM health_camps ORDER BY camp_date DESC"
);
$camps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Health Camps</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2>Health Camp Admin Panel</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="camp-details.php">Camp Details</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:1000px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Add a New Health Camp</h1>

        <?php if ($message !== ""): ?>
            <p role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="add">

            <label>Camp Name</label>
            <input name="camp_name" maxlength="150" required>

            <label>Location</label>
            <input name="location" maxlength="200" required>

            <label>Camp Date</label>
            <input type="date" name="camp_date"
                   min="<?= date('Y-m-d') ?>" required>

            <label>Start Time</label>
            <input type="time" name="start_time" required>

            <label>End Time</label>
            <input type="time" name="end_time" required>

            <label>Organizer</label>
            <input name="organizer" maxlength="150" required>

            <label>Description</label>
            <textarea name="description" rows="4"></textarea>

            <button class="button" type="submit">Add Health Camp</button>
        </form>
    </div>

    <div class="card" style="margin-top:25px;">
        <h2>Existing Health Camps</h2>

        <?php if (!$camps): ?>
            <p>No camps have been added yet.</p>
        <?php else: ?>
            <?php foreach ($camps as $camp): ?>
                <div style="border-bottom:1px solid #ddd; padding:15px 0;">
                    <h3><?= htmlspecialchars($camp["camp_name"]) ?></h3>
                    <p>
                        Location: <?= htmlspecialchars($camp["location"]) ?><br>
                        Date: <?= htmlspecialchars($camp["camp_date"]) ?><br>
                        Time: <?= htmlspecialchars(substr($camp["start_time"], 0, 5)) ?>
                        – <?= htmlspecialchars(substr($camp["end_time"], 0, 5)) ?><br>
                        Current status: <?= htmlspecialchars($camp["status"]) ?>
                    </p>
                    

                    <form method="post">
                        <input type="hidden" name="action" value="status">
                        <input type="hidden" name="camp_id"
                               value="<?= (int)$camp["camp_id"] ?>">

                        <label>Update Camp Status</label>
                        <select name="status" required>
                            <?php foreach (["Planned", "Active", "Completed"] as $status): ?>
                                <option value="<?= $status ?>"
                                    <?= $camp["status"] === $status ? "selected" : "" ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="button" type="submit">
                            Update Status
                        </button>
                    </form>
                    <p>
    <a class="button"
       href="edit-camp.php?id=<?= (int)$camp['camp_id'] ?>">
        Edit Camp
    </a>
</p>
<form method="post" action="delete-camp.php"
      onsubmit="return confirm('Are you sure you want to delete this camp?');">
    <input type="hidden" name="camp_id"
           value="<?= (int)$camp['camp_id'] ?>">
    <input type="hidden" name="confirm" value="yes">
    <button type="submit">Delete Camp</button>
</form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
