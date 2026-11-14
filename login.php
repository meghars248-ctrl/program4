
<?php
session_start();
require_once "database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT user_id, full_name, username, password_hash, role
         FROM users WHERE username = ?"
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {
        session_regenerate_id(true);

        $_SESSION["user_id"] = (int)$user["user_id"];
        $_SESSION["full_name"] = $user["full_name"];
        $_SESSION["role"] = $user["role"];

        header("Location: dashboard.php");
        exit;
    }

    $error = "Incorrect username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h2>Smart Community Health Camp</h2>
    <nav><a href="index.php">Home</a></nav>
</header>

<main style="max-width:600px; margin:40px auto; padding:20px;">
    <div class="card">
        <h1>User Login</h1>
        <p>Log in to access your dashboard.</p>

        <?php if ($error !== ""): ?>
            <p role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label for="username">Username</label>
            <input id="username" name="username"
                   autocomplete="username" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password"
                   autocomplete="current-password" required>

            <button class="button" type="submit">Login</button>
        </form>
    </div>
</main>
</body>
</html>
