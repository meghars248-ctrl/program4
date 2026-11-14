
<?php
session_start();
require_once "database.php";

// Only logged-in staff can view the volunteer list.
$allowedRoles = ["Admin", "Doctor", "Volunteer"];

if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    !in_array($_SESSION["role"], $allowedRoles, true)
) {
    http_response_code(403);
    exit("Access denied. Please log in first.");
}

$stmt = $pdo->query(
    "SELECT full_name, username, created_at
     FROM users
     WHERE role = 'Volunteer'
     ORDER BY full_name ASC"
);

$volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Smart Community Health Camp</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="camp-details.php">Camp Details</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:900px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Volunteer List</h1>
        <p>Registered volunteers who support health camp activities.</p>

        <p><strong>Total Volunteers:</strong> <?= count($volunteers) ?></p>

        <?php if (count($volunteers) === 0): ?>
            <p>No volunteers have been registered yet.</p>
            <p>The Admin can create volunteer accounts from Manage Users.</p>
        <?php else: ?>
            <?php foreach ($volunteers as $index => $volunteer): ?>
                <div style="border-bottom:1px solid #ddd; padding:15px 0;">
                    <h3>
                        Volunteer <?= $index + 1 ?>:
                        <?= htmlspecialchars($volunteer["full_name"]) ?>
                    </h3>
                    <p>
                        Username:
                        <?= htmlspecialchars($volunteer["username"]) ?>
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
