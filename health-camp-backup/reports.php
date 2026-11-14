
<?php
session_start();
require_once "database.php";

// Only Admin can view reports.
if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    http_response_code(403);
    exit("Access denied. Please log in as Admin.");
}

// Total camps
$totalCamps = (int)$pdo->query(
    "SELECT COUNT(*) FROM health_camps"
)->fetchColumn();

// Total registered patients
$totalPatients = (int)$pdo->query(
    "SELECT COUNT(*) FROM registrations"
)->fetchColumn();

// Queue status summary
$stmt = $pdo->query(
    "SELECT queue_status, COUNT(*) AS total
     FROM registrations
     GROUP BY queue_status"
);
$queueSummary = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Medicine inventory summary
$medicineSummary = $pdo->query(
    "SELECT COUNT(*) AS medicine_types,
            COALESCE(SUM(quantity), 0) AS total_stock
     FROM medicines"
)->fetch(PDO::FETCH_ASSOC);

// Recent camp registration summary
$stmt = $pdo->query(
    "SELECT h.camp_name, h.camp_date,
            COUNT(r.registration_id) AS patient_count
     FROM health_camps h
     LEFT JOIN registrations r ON h.camp_id = r.camp_id
     GROUP BY h.camp_id, h.camp_name, h.camp_date
     ORDER BY h.camp_date DESC"
);
$campReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Camp Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Health Camp Reports</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-camps.php">Manage Camps</a>
        <a href="manage-queue.php">Manage Queue</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:1000px; margin:30px auto; padding:20px;">
    <h1>System Summary</h1>

    <section class="features">
        <div class="card">
            <h3>Total Camps</h3>
            <h2><?= $totalCamps ?></h2>
        </div>

        <div class="card">
            <h3>Total Registrations</h3>
            <h2><?= $totalPatients ?></h2>
        </div>

        <div class="card">
            <h3>Waiting Patients</h3>
            <h2><?= (int)($queueSummary["Waiting"] ?? 0) ?></h2>
        </div>

        <div class="card">
            <h3>In Consultation</h3>
            <h2><?= (int)($queueSummary["In Consultation"] ?? 0) ?></h2>
        </div>

        <div class="card">
            <h3>Completed</h3>
            <h2><?= (int)($queueSummary["Completed"] ?? 0) ?></h2>
        </div>

        <div class="card">
            <h3>Medicine Types</h3>
            <h2><?= (int)$medicineSummary["medicine_types"] ?></h2>
            <p>Total stock: <?= (int)$medicineSummary["total_stock"] ?></p>
        </div>
    </section>

    <div class="card" style="margin-top:25px;">
        <h2>Camp-wise Patient Registrations</h2>

        <?php if (!$campReports): ?>
            <p>No camp records available.</p>
        <?php else: ?>
            <?php foreach ($campReports as $camp): ?>
                <div style="border-bottom:1px solid #ddd; padding:12px 0;">
                    <h3><?= htmlspecialchars($camp["camp_name"]) ?></h3>
                    <p>
                        Date: <?= htmlspecialchars($camp["camp_date"]) ?><br>
                        Registered Patients: <?= (int)$camp["patient_count"] ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>Smart Community Health Camp Monitoring System</p>
</footer>

</body>
</html>
