
<?php
require_once "database.php";

$registration = null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = filter_input(INPUT_POST, "token_number", FILTER_VALIDATE_INT);
    $campId = filter_input(INPUT_POST, "camp_id", FILTER_VALIDATE_INT);

    if (!$token || $token < 1 || !$campId || $campId < 1) {
        $message = "Please enter a valid token number and select a camp.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT r.token_number, r.queue_status,
                    r.registration_type, h.camp_name,
                    h.camp_date
             FROM registrations r
             JOIN health_camps h ON r.camp_id = h.camp_id
             WHERE r.token_number = ? AND r.camp_id = ?"
        );

        $stmt->execute([$token, $campId]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$registration) {
            $message = "Registration not found. Check your camp and token number.";
        }
    }
}

$stmt = $pdo->query(
    "SELECT camp_id, camp_name, camp_date
     FROM health_camps
     ORDER BY camp_date DESC"
);
$camps = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token and Queue Status</title>
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

<main style="max-width: 650px; margin: 30px auto; padding: 20px;">
    <div class="card">
        <h1>Check Your Queue Status</h1>
        <p>Enter your token number and select the camp to check your status.</p>

        <?php if ($message !== ""): ?>
            <p role="status">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="queue-status.php">
            <label for="camp_id">Select Health Camp</label>
            <select id="camp_id" name="camp_id" required>
                <option value="">Choose a camp</option>
                <?php foreach ($camps as $camp): ?>
                    <option value="<?= (int)$camp["camp_id"] ?>"
                        <?= isset($campId) && $campId === (int)$camp["camp_id"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($camp["camp_name"]) ?>
                        — <?= htmlspecialchars($camp["camp_date"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="token_number">Token Number</label>
            <input
                type="number"
                id="token_number"
                name="token_number"
                min="1"
                required
            >

            <button type="submit" class="button">
                Check Status
            </button>
        </form>

        <?php if ($registration): ?>
            <hr>
            <h2>Your Queue Details</h2>
            <p><strong>Health Camp:</strong>
                <?= htmlspecialchars($registration["camp_name"]) ?></p>
            <p><strong>Camp Date:</strong>
                <?= htmlspecialchars($registration["camp_date"]) ?></p>
            <p><strong>Token Number:</strong>
                <?= (int)$registration["token_number"] ?></p>
            <p><strong>Registration Type:</strong>
                <?= htmlspecialchars($registration["registration_type"]) ?></p>
            <p><strong>Queue Status:</strong>
                <?= htmlspecialchars($registration["queue_status"]) ?></p>
        <?php endif; ?>
    </div>
</main>

<footer>
    <p>Smart Community Health Camp Monitoring System</p>
</footer>

</body>
</html>
