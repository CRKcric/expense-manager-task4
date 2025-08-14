<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_transaction.php");
    exit();
}

$transaction_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $transaction_id, 'user_id' => $user_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        header("Location: view_transaction.php");
        exit();
    }
} catch (PDOException $e) {
    $message = "❌ Error fetching transaction: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount']);
    $description = trim($_POST['description']);
    $date = trim($_POST['date']);
    $type = trim($_POST['type']);

    if (empty($amount) || empty($description) || empty($date) || empty($type)) {
        $message = "All fields are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE transactions SET amount = :amount, description = :description, trans_date = :date, type = :type WHERE id = :id AND user_id = :user_id");
            $stmt->execute([
                'amount' => $amount,
                'description' => $description,
                'date' => $date,
                'type' => $type,
                'id' => $transaction_id,
                'user_id' => $user_id
            ]);
            header("Location: view_transaction.php");
            exit();
        } catch (PDOException $e) {
            $message = "❌ Error updating transaction: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-success { background: #e6f4ea; color: #1b5e20; padding: 10px; border-radius: 4px; }
        .alert-error { background: #fdecea; color: #b71c1c; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Edit Transaction</h1>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, '❌') !== false ? 'alert-error' : 'alert-success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Amount:</label>
        <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($transaction['amount']); ?>" required>

        <label>Description:</label>
        <input type="text" name="description" value="<?php echo htmlspecialchars($transaction['description']); ?>" required>

        <label>Date:</label>
        <input type="date" name="date" value="<?php echo htmlspecialchars($transaction['trans_date']); ?>" required>

        <label>Type:</label>
        <select name="type" required>
            <option value="income" <?php echo $transaction['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
            <option value="expense" <?php echo $transaction['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
        </select>

        <button type="submit">Update Transaction</button>
    </form>

    <br>
    <a href="view_transaction.php">Back to Transactions</a>
</div>
</body>
</html>
