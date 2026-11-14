
<?php
require_once "database.php";

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";

$allowedStatuses = ["Planned", "Active", "Completed"];

$sql = "SELECT * FROM health_camps WHERE 1=1";
$params = [];

if ($search !== "") {
    $sql .= " AND (camp_name LIKE ? OR location LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY camp_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$camps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health Camp Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Smart Community Health Camp</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="camp-details.php">Camp Details</a>
        <a href="register.php">Registration</a>
        <a href="queue-status.php">Queue Status</a>
    </nav>
</header>

<main style="max-width:1000px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Find a Health Camp</h1>

        <form method="get" action="camp-details.php">
            <label for="search">Search by camp name or location</label>
            <input
                type="search"
                id="search"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Enter camp name or location"
            >

            <label for="status">Filter by Status</label>
            <select id="status" name="status">
                <option value="">All Statuses</option>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option value="<?= $option ?>"
                        <?= $status === $option ? "selected" : "" ?>>
                        <?= $option ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="button" type="submit">Search Camps</button>
            <a href="camp-details.php">Clear Filters</a>
        </form>
    </div>

    <h2>Available Health Camps</h2>

    <?php if (!$camps): ?>
        <div class="card">
            <p>No camps match your search.</p>
        </div>
    <?php else: ?>
        <?php foreach ($camps as $camp): ?>
            <div class="card" style="margin-bottom:15px;">
                <h3><?= htmlspecialchars($camp["camp_name"]) ?></h3>

                <p>
                    <strong>Location:</strong>
                    <?= htmlspecialchars($camp["location"]) ?><br>

                    <strong>Date:</strong>
                    <?= htmlspecialchars($camp["camp_date"]) ?><br>

                    <strong>Time:</strong>
                    <?= htmlspecialchars(substr($camp["start_time"], 0, 5)) ?>
                    – <?= htmlspecialchars(substr($camp["end_time"], 0, 5)) ?><br>

                    <strong>Organizer:</strong>
                    <?= htmlspecialchars($camp["organizer"]) ?><br>

                    <strong>Status:</strong>
                    <?= htmlspecialchars($camp["status"]) ?>
                </p>

                <p><?= nl2br(htmlspecialchars($camp["description"] ?? "")) ?></p>

                <?php if (
                    in_array($camp["status"], ["Planned", "Active"], true)
                    && $camp["camp_date"] >= date("Y-m-d")
                ): ?>
                    <a class="button"
                       href="register.php?camp_id=<?= (int)$camp["camp_id"] ?>">
                        Register for This Camp
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer>
    <p>Smart Community Health Camp Monitoring System</p>
</footer>

</body>
</html>
