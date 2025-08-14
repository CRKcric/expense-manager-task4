<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$message = "";

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
        $message = "✅ Transaction deleted successfully!";
    } catch (PDOException $e) {
        $message = "❌ Error deleting transaction: " . $e->getMessage();
    }
}

try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY trans_date DESC");
    $stmt->execute(['user_id' => $user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "❌ Error fetching transactions: " . $e->getMessage();
}

$total_income = 0;
$total_expense = 0;
foreach ($transactions as $t) {
    if ($t['type'] === 'income') {
        $total_income += $t['amount'];
    } else {
        $total_expense += $t['amount'];
    }
}
$balance = $total_income - $total_expense;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Transactions</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .alert-success { background: #e6f4ea; color: #1b5e20; padding: 10px; border-radius: 4px; }
        .alert-error { background: #fdecea; color: #b71c1c; padding: 10px; border-radius: 4px; }
        .summary { background: #f5f5f5; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .summary h3 { margin: 5px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Transactions</h1>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="summary">
        <h3>💰 Total Income: ₹<?php echo number_format($total_income, 2); ?></h3>
        <h3>💸 Total Expenses: ₹<?php echo number_format($total_expense, 2); ?></h3>
        <h3 style="color: <?php echo $balance >= 0 ? '#1b5e20' : '#b71c1c'; ?>;">
            🧾 Balance: ₹<?php echo number_format($balance, 2); ?>
        </h3>
    </div>

    <?php if (!empty($transactions)): ?>
        <table>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?php echo htmlspecialchars($t['trans_date']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($t['type'])); ?></td>
                    <td><?php echo number_format($t['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($t['description']); ?></td>
                    <td>
                        <a href="edit_transaction.php?id=<?php echo $t['id']; ?>"><button>Edit</button></a>
                        <a href="view_transaction.php?delete=<?php echo $t['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this transaction?');">
                           <button>Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No transactions found.</p>
    <?php endif; ?>

    <br>
    <a href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>
