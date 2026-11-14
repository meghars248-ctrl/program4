
<?php
session_start();
require_once "database.php";

// Only Admin can manage medicine inventory.
if (
    !isset($_SESSION["user_id"], $_SESSION["role"]) ||
    $_SESSION["role"] !== "Admin"
) {
    http_response_code(403);
    exit("Access denied. Please log in as Admin.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add") {
        $name = trim($_POST["medicine_name"] ?? "");
        $quantity = filter_input(
            INPUT_POST, "quantity", FILTER_VALIDATE_INT
        );
        $expiry = $_POST["expiry_date"] ?? "";

        $expiryDate = DateTime::createFromFormat("!Y-m-d", $expiry);

        if (
            $name === "" ||
            $quantity === false || $quantity === null || $quantity < 0 ||
            !$expiryDate || $expiryDate->format("Y-m-d") !== $expiry
        ) {
            $message = "Please enter valid medicine details.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO medicines
                 (medicine_name, quantity, expiry_date)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $quantity, $expiry]);
            $message = "Medicine added successfully.";
        }
    } elseif ($action === "update") {
        $medicineId = filter_input(
            INPUT_POST, "medicine_id", FILTER_VALIDATE_INT
        );
        $quantity = filter_input(
            INPUT_POST, "quantity", FILTER_VALIDATE_INT
        );

        if (
            !$medicineId ||
            $quantity === false || $quantity === null || $quantity < 0
        ) {
            $message = "Please enter a valid quantity.";
        } else {
            $stmt = $pdo->prepare(
                "UPDATE medicines SET quantity = ? WHERE medicine_id = ?"
            );
            $stmt->execute([$quantity, $medicineId]);
            $message = $stmt->rowCount() > 0
                ? "Medicine quantity updated."
                : "No quantity change was made, or the medicine was not found.";
        }
    }
}

$stmt = $pdo->query(
    "SELECT * FROM medicines ORDER BY medicine_name ASC"
);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Medicine Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h2>Health Camp Medicine Inventory</h2>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-camps.php">Manage Camps</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main style="max-width:900px; margin:30px auto; padding:20px;">
    <div class="card">
        <h1>Add Medicine</h1>

        <?php if ($message !== ""): ?>
            <p role="status"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="add">

            <label>Medicine Name</label>
            <input type="text" name="medicine_name"
                   maxlength="150" required>

            <label>Available Quantity</label>
            <input type="number" name="quantity"
                   min="0" required>

            <label>Expiry Date</label>
            <input type="date" name="expiry_date" required>

            <button class="button" type="submit">Add Medicine</button>
        </form>
    </div>

    <div class="card" style="margin-top:25px;">
        <h2>Available Medicines</h2>

        <?php if (!$medicines): ?>
            <p>No medicines have been added yet.</p>
        <?php else: ?>
            <?php foreach ($medicines as $medicine): ?>
                <div style="border-bottom:1px solid #ddd; padding:15px 0;">
                    <h3><?= htmlspecialchars($medicine["medicine_name"]) ?></h3>

                    <p>
                        Expiry Date:
                        <?= htmlspecialchars($medicine["expiry_date"]) ?>
                    </p>

                    <?php if ($medicine["expiry_date"] < date("Y-m-d")): ?>
                        <p><strong>Warning: Expired medicine — do not use.</strong></p>
                    <?php elseif ($medicine["quantity"] == 0): ?>
                        <p><strong>Out of stock</strong></p>
                    <?php else: ?>
                        <p>
                            Available Quantity:
                            <?= (int)$medicine["quantity"] ?>
                        </p>
                    <?php endif; ?>

                    <form method="post">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="medicine_id"
                               value="<?= (int)$medicine["medicine_id"] ?>">

                        <label>Set New Quantity</label>
                        <input type="number" name="quantity"
                               min="0" value="<?= (int)$medicine["quantity"] ?>"
                               required>

                        <button class="button" type="submit">
                            Update Quantity
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
