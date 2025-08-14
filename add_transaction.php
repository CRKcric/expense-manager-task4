<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $date = trim($_POST['date']);
    $type = trim($_POST['type']);    

    if (empty($amount) || empty($description) || empty($date) || empty($type)) {
        $message = "All fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO transactions (user_id, amount, description, trans_date, type) 
                VALUES (:user_id, :amount, :description, :trans_date, :type)
            ");
            $stmt->execute([
                'user_id'    => $user_id,
                'amount'     => $amount,
                'description'=> $description,
                'trans_date' => $date,
                'type'       => $type
            ]);
            $message = "✅ Transaction added successfully!";
        } catch (PDOException $e) {
            $message = "❌ Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Transaction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-success { background: #e6f4ea; color: #1b5e20; padding: 10px; border-radius: 4px; }
        .alert-error { background: #fdecea; color: #b71c1c; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Add Transaction</h1>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Amount:</label>
        <input type="number" name="amount" step="0.01" required>

        <label>Description:</label>
        <input type="text" name="description" required>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Type:</label>
        <select name="type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
        </select>

        <button type="submit">Add Transaction</button>
    </form>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
