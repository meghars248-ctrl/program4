
<?php
session_start();
require_once "database.php";

// Allow only Admin, Doctor, and Volunteer.
$allowedRoles = ["Admin", "Doctor", "Volunteer"];

if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    !in_array($_SESSION["role"], $allowedRoles, true)
) {
    http_response_code(403);
    exit("Access denied. Please log in with an authorized account.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $registrationId = filter_input(
        INPUT_POST,
        "registration_id",
        FILTER_VALIDATE_INT
    );
    $newStatus = $_POST["queue_status"] ?? "";

    $validStatuses = ["Waiting", "In Consultation", "Completed"];

    if (
        !$registrationId ||
        !in_array($newStatus, $validStatuses, true)
    ) {
        $message = "Please select a valid queue status.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE registrations
             SET queue_status = ?
             WHERE registration_id = ?"
        );
        $stmt->execute([$newStatus, $registrationId]);

        $message = $stmt->rowCount() > 0
            ? "Queue status updated successfully."
            : "No change was made, or the registration was not found.";
    }
}

$stmt = $pdo->query(
    "SELECT r.registration_id, r.token_number,
            r.registration_type, r.queue_status,
            p.full_name, p.age, p.gender,
            h.camp_name, h.camp_date
     FROM registrations r
     JOIN patients p ON r.patient_id = p.patient_id
     JOIN health_camps h ON r.camp_id = h.camp_id
     ORDER BY h.camp_date DESC, r.token_number ASC"
);

$registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Patient Queue Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Health Camp Queue Management</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="queue-status.php">Patient Queue Status</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:1000px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Patient Queue</h1>
        <p>View registered patients and update their consultation status.</p>

        <?php if ($message !== ""): ?>
            <p role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!$registrations): ?>
            <p>No patient registrations found.</p>
        <?php else: ?>

            <?php foreach ($registrations as $patient): ?>
                <div style="border-bottom:1px solid #ddd; padding:15px 0;">
                    <h3>
                        Token #<?= (int)$patient["token_number"] ?>
                        — <?= htmlspecialchars($patient["full_name"]) ?>
                    </h3>

                    <p>
                        <strong>Camp:</strong>
                        <?= htmlspecialchars($patient["camp_name"]) ?><br>

                        <strong>Camp Date:</strong>
                        <?= htmlspecialchars($patient["camp_date"]) ?><br>

                        <strong>Age:</strong>
                        <?= (int)$patient["age"] ?><br>

                        <strong>Gender:</strong>
                        <?= htmlspecialchars($patient["gender"]) ?><br>

                        <strong>Registration:</strong>
                        <?= htmlspecialchars($patient["registration_type"]) ?><br>

                        <strong>Current Status:</strong>
                        <?= htmlspecialchars($patient["queue_status"]) ?>
                    </p>

                    <form method="post" action="manage-queue.php">
                        <input type="hidden" name="registration_id"
                               value="<?= (int)$patient["registration_id"] ?>">

                        <label>Update Queue Status</label>
                        <select name="queue_status" required>
                            <?php foreach (
                                ["Waiting", "In Consultation", "Completed"]
                                as $status
                            ): ?>
                                <option value="<?= $status ?>"
                                    <?= $patient["queue_status"] === $status
                                        ? "selected" : "" ?>>
                                    <?= $status ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button class="button" type="submit">
                            Update Status
                        </button>
                    </form>
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
