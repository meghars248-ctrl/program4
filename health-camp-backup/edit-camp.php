
<?php
session_start();
require_once "database.php";

if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    http_response_code(403);
    exit("Access denied. Please log in as Admin.");
}

$campId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $campId = filter_input(INPUT_POST, "camp_id", FILTER_VALIDATE_INT);
    $name = trim($_POST["camp_name"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $date = $_POST["camp_date"] ?? "";
    $start = $_POST["start_time"] ?? "";
    $end = $_POST["end_time"] ?? "";
    $organizer = trim($_POST["organizer"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "";

    $validDate = DateTime::createFromFormat("!Y-m-d", $date);
    $validStart = DateTime::createFromFormat("!H:i", $start);
    $validEnd = DateTime::createFromFormat("!H:i", $end);

    if (
        !$campId || $name === "" || $location === "" ||
        $organizer === "" ||
        !$validDate || $validDate->format("Y-m-d") !== $date ||
        !$validStart || $validStart->format("H:i") !== $start ||
        !$validEnd || $validEnd->format("H:i") !== $end ||
        $end <= $start ||
        !in_array($status, ["Planned", "Active", "Completed"], true)
    ) {
        exit("Invalid camp details. Go back and check the form.");
    }

    $stmt = $pdo->prepare(
        "UPDATE health_camps
         SET camp_name = ?, location = ?, camp_date = ?,
             start_time = ?, end_time = ?, organizer = ?,
             description = ?, status = ?
         WHERE camp_id = ?"
    );

    $stmt->execute([
        $name, $location, $date, $start, $end,
        $organizer, $description, $status, $campId
    ]);

    header("Location: manage-camps.php?updated=1");
    exit;
}

if (!$campId) {
    exit("Invalid camp ID.");
}

$stmt = $pdo->prepare("SELECT * FROM health_camps WHERE camp_id = ?");
$stmt->execute([$campId]);
$camp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$camp) {
    exit("Health camp not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Health Camp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2>Edit Health Camp</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:700px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Update Camp Details</h1>

        <form method="post">
            <input type="hidden" name="camp_id"
                   value="<?= (int)$camp["camp_id"] ?>">

            <label>Camp Name</label>
            <input name="camp_name" maxlength="150"
                   value="<?= htmlspecialchars($camp["camp_name"]) ?>" required>

            <label>Location</label>
            <input name="location" maxlength="200"
                   value="<?= htmlspecialchars($camp["location"]) ?>" required>

            <label>Camp Date</label>
            <input type="date" name="camp_date"
                   value="<?= htmlspecialchars($camp["camp_date"]) ?>" required>

            <label>Start Time</label>
            <input type="time" name="start_time"
                   value="<?= htmlspecialchars(substr($camp["start_time"], 0, 5)) ?>" required>

            <label>End Time</label>
            <input type="time" name="end_time"
                   value="<?= htmlspecialchars(substr($camp["end_time"], 0, 5)) ?>" required>

            <label>Organizer</label>
            <input name="organizer" maxlength="150"
                   value="<?= htmlspecialchars($camp["organizer"]) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4"><?= htmlspecialchars($camp["description"] ?? "") ?></textarea>

            <label>Status</label>
            <select name="status" required>
                <?php foreach (["Planned", "Active", "Completed"] as $option): ?>
                    <option value="<?= $option ?>"
                        <?= $camp["status"] === $option ? "selected" : "" ?>>
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Save Changes</button>
        </form>
    </div>
</main>
</body>
</html>
