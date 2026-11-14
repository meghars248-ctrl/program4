
<?php
session_start();
require_once "database.php";

if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    http_response_code(403);
    exit("Access denied. Only Admin can manage users.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    if (
        $name === "" ||
        !preg_match('/^[a-zA-Z0-9_]{4,50}$/', $username) ||
        strlen($password) < 8 ||
        !in_array($role, ["Doctor", "Volunteer"], true)
    ) {
        $message = "Enter valid details. Password must have at least 8 characters.";
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, username, password_hash, role)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([$name, $username, $hash, $role]);
            $message = $role . " account created successfully.";
        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $message = "This username already exists. Choose another.";
            } else {
                $message = "Unable to create the account. Please try again.";
            }
        }
    }
}

$stmt = $pdo->query(
    "SELECT user_id, full_name, username, role, created_at
     FROM users ORDER BY user_id DESC"
);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Health Camp Admin Panel</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-camps.php">Manage Camps</a>
        <a href="manage-queue.php">Manage Queue</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:900px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Create Staff Account</h1>
        <p>Create login accounts for Doctors and Volunteers.</p>

        <?php if ($message !== ""): ?>
            <p role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post" action="manage-users.php">
            <label>Full Name</label>
            <input type="text" name="full_name" maxlength="100" required>

            <label>Username (4–50 letters, numbers, or underscores)</label>
            <input type="text" name="username"
                   pattern="[a-zA-Z0-9_]{4,50}"
                   minlength="4" maxlength="50" required>

            <label>Temporary Password (minimum 8 characters)</label>
            <input type="password" name="password"
                   minlength="8" required>

            <label>Staff Role</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="Doctor">Doctor</option>
                <option value="Volunteer">Volunteer</option>
            </select>

            <button type="submit" class="button">Create Account</button>
        </form>
    </div>

    <div class="card" style="margin-top:25px;">
        <h2>Registered Users</h2>

        <?php foreach ($users as $user): ?>
            <div style="border-bottom:1px solid #ddd; padding:12px 0;">
                <strong><?= htmlspecialchars($user["full_name"]) ?></strong>
                <p>
                    Username: <?= htmlspecialchars($user["username"]) ?><br>
                    Role: <?= htmlspecialchars($user["role"]) ?><br>
                    Created: <?= htmlspecialchars($user["created_at"]) ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</main>

</body>
</html>
