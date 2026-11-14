
<?php
session_start();

if (!isset($_SESSION["user_id"], $_SESSION["role"])) {
    header("Location: login.php");
    exit;
}

$allowedRoles = ["Admin", "Doctor", "Volunteer"];

if (!in_array($_SESSION["role"], $allowedRoles, true)) {
    http_response_code(403);
    exit("Access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2>Health Camp Dashboard</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="camp-details.php">Camp Details</a>
        <a href="register.php">Patient Registration</a>
        <a href="queue-status.php">Queue Status</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:800px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Welcome, <?= htmlspecialchars($_SESSION["full_name"]) ?>!</h1>
        <p>Your role: <strong><?= htmlspecialchars($_SESSION["role"]) ?></strong></p>
        
        <?php if ($_SESSION["role"] === "Admin"): ?>
            <h2>Admin Dashboard</h2>
            <p>Manage health camp information and oversee registrations.</p>
            <p><a href="manage-camps.php">Manage Health Camps</a></p>
            <p><a href="manage-queue.php">Manage Patient Queue</a></p>
            <p><a href="manage-users.php">Manage Users</a></p>
            <p><a href="volunteer-list.php">View Volunteer List</a></p>
            <p><a href="manage-medicines.php">Manage Medicine Inventory</a></p>
            <p><a href="reports.php">View Health Camp Reports</a></p>
        <?php elseif ($_SESSION["role"] === "Doctor"): ?>
            <h2>Doctor Dashboard</h2>
            <p>View patient queue information for consultation.</p>
            <p><a href="manage-queue.php">Manage Patient Queue</a></p>
        <?php else: ?>
            <h2>Volunteer Dashboard</h2>
            <p>Assist with patient registration and camp activities.</p>
            <p><a href="manage-queue.php">Manage Patient Queue</a></p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
