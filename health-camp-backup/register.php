
<?php
require_once "database.php";

$message = "";
$campId = filter_input(INPUT_GET, "camp_id", FILTER_VALIDATE_INT);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $campId = filter_input(INPUT_POST, "camp_id", FILTER_VALIDATE_INT);
    $name = trim($_POST["full_name"] ?? "");
    $age = filter_input(INPUT_POST, "age", FILTER_VALIDATE_INT);
    $gender = $_POST["gender"] ?? "";
    $phone = trim($_POST["phone"] ?? "");
    $type = $_POST["registration_type"] ?? "Online";

    if (
        !$campId || $name === "" ||
        $age === false || $age === null || $age < 0 || $age > 120 ||
        !in_array($gender, ["Female", "Male", "Other"], true) ||
        !preg_match('/^[0-9+\-\s]{7,20}$/', $phone) ||
        !in_array($type, ["Online", "On-Spot"], true)
    ) {
        $message = "Please enter valid registration details.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT camp_id FROM health_camps
                 WHERE camp_id = ?
                 AND status IN ('Planned', 'Active')
                 AND camp_date >= CURDATE()
                 FOR UPDATE"
            );
            $stmt->execute([$campId]);

            if (!$stmt->fetch()) {
                throw new RuntimeException(
                    "This camp is not available for registration."
                );
            }

            $stmt = $pdo->prepare(
                "INSERT INTO patients (full_name, age, gender, phone)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$name, $age, $gender, $phone]);
            $patientId = (int)$pdo->lastInsertId();

            // Lock the camp row so concurrent registrations
            // cannot receive the same token.
            $stmt = $pdo->prepare(
                "SELECT camp_id FROM health_camps
                 WHERE camp_id = ? FOR UPDATE"
            );
            $stmt->execute([$campId]);

            $stmt = $pdo->prepare(
                "SELECT COALESCE(MAX(token_number), 0) + 1
                 FROM registrations WHERE camp_id = ?"
            );
            $stmt->execute([$campId]);
            $token = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare(
                "INSERT INTO registrations
                 (patient_id, camp_id, token_number, registration_type)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$patientId, $campId, $token, $type]);

            $pdo->commit();

            $message = "Registration successful! Your token number is: "
                . $token;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = $e instanceof RuntimeException
                ? $e->getMessage()
                : "Registration failed. Please try again or contact the camp volunteer.";
        }
    }
}

$stmt = $pdo->query(
    "SELECT camp_id, camp_name, camp_date, status
     FROM health_camps
     WHERE status IN ('Planned', 'Active')
       AND camp_date >= CURDATE()
     ORDER BY camp_date"
);
$camps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Smart Community Health Camp</h2>
    <nav>
        <a href="index.php">Home</a>
        <a href="camp-details.php">Camp Details</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main style="max-width:650px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Patient Registration</h1>
        <p>Register online or at the health camp counter.</p>

        <?php if ($message !== ""): ?>
            <p role="status">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <label>Patient Name</label>
            <input type="text" name="full_name"
                   maxlength="100" required>

            <label>Age</label>
            <input type="number" name="age"
                   min="0" max="120" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select gender</option>
                <option>Female</option>
                <option>Male</option>
                <option>Other</option>
            </select>

            <label>Phone Number</label>
            <input type="tel" name="phone"
                   maxlength="20" required>

            <label>Select Health Camp</label>
            <select name="camp_id" required>
                <option value="">Choose a camp</option>
                <?php foreach ($camps as $camp): ?>
                    <option value="<?= (int)$camp["camp_id"] ?>"
                        <?= $campId === (int)$camp["camp_id"]
                            ? "selected" : "" ?>>
                        <?= htmlspecialchars($camp["camp_name"]) ?>
                        — <?= htmlspecialchars($camp["camp_date"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Registration Type</label>
            <select name="registration_type" required>
                <option value="Online">Online Registration</option>
                <option value="On-Spot">On-Spot Registration</option>
            </select>

            <button type="submit" class="button">
                Register and Get Token
            </button>
        </form>
    </div>
</main>

</body>
</html>
