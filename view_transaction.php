<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination setup
$limit = 5; // records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Base query
$query = "SELECT * FROM transactions WHERE user_id = :user_id";
$params = ['user_id' => $_SESSION['user_id']];

// Search filter
if (!empty($search)) {
    $query .= " AND description LIKE :search";
    $params['search'] = "%$search%";
}

// Type filter
if (!empty($type)) {
    $query .= " AND type = :type";
    $params['type'] = $type;
}

// Date range filter
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND trans_date BETWEEN :start_date AND :end_date";
    $params['start_date'] = $start_date;
    $params['end_date'] = $end_date;
}

// Count total records
$count_stmt = $pdo->prepare($query);
$count_stmt->execute($params);
$total_records = $count_stmt->rowCount();
$total_pages = ceil($total_records / $limit);

// Totals for Income, Expense, Balance
$total_query = "SELECT 
    SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS total_expense
    FROM transactions WHERE user_id = :user_id";

$total_params = ['user_id' => $_SESSION['user_id']];

// Apply same filters for totals
if (!empty($search)) {
    $total_query .= " AND description LIKE :search";
    $total_params['search'] = "%$search%";
}
if (!empty($type)) {
    $total_query .= " AND type = :type";
    $total_params['type'] = $type;
}
if (!empty($start_date) && !empty($end_date)) {
    $total_query .= " AND trans_date BETWEEN :start_date AND :end_date";
    $total_params['start_date'] = $start_date;
    $total_params['end_date'] = $end_date;
}

$total_stmt = $pdo->prepare($total_query);
$total_stmt->execute($total_params);
$totals = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_income = $totals['total_income'] ?? 0;
$total_expense = $totals['total_expense'] ?? 0;
$balance = $total_income - $total_expense;

// Add order and limit for main query
$query .= " ORDER BY trans_date DESC LIMIT :limit OFFSET :offset";
$params['limit'] = $limit;
$params['offset'] = $offset;

$stmt = $pdo->prepare($query);

// Bind parameters manually for LIMIT/OFFSET
foreach ($params as $key => &$val) {
    if ($key == 'limit' || $key == 'offset') {
        $stmt->bindParam(":$key", $val, PDO::PARAM_INT);
    } else {
        $stmt->bindParam(":$key", $val);
    }
}

$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Transactions</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .totals { margin-bottom: 20px; font-size: 1.1rem; }
        .income { color: green; font-weight: bold; }
        .expense { color: red; font-weight: bold; }
        .balance { color: blue; font-weight: bold; }
        .amount-income { color: green; font-weight: bold; }
        .amount-expense { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Your Transactions</h1>

    <!-- Totals Display -->
    <div class="totals">
        <p class="income">Total Income: ₹<?= number_format($total_income, 2) ?></p>
        <p class="expense">Total Expense: ₹<?= number_format($total_expense, 2) ?></p>
        <p class="balance">Balance: ₹<?= number_format($balance, 2) ?></p>
    </div>

    <!-- Filters -->
    <form method="GET" action="" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search description..." value="<?= htmlspecialchars($search) ?>">
        <select name="type">
            <option value="">All Types</option>
            <option value="income" <?= $type == 'income' ? 'selected' : '' ?>>Income</option>
            <option value="expense" <?= $type == 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
        <button type="submit">Filter</button>
        <a href="view_transaction.php"><button type="button">Reset</button></a>
    </form>

    <!-- Table -->
    <table>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Type</th>
            <th>Amount</th>
        </tr>
        <?php if ($transactions): ?>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['trans_date']) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['type']) ?></td>
                    <td class="<?= $t['type'] == 'income' ? 'amount-income' : 'amount-expense' ?>">
                        ₹<?= htmlspecialchars($t['amount']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No transactions found.</td></tr>
        <?php endif; ?>
    </table>

    <!-- Pagination -->
    <div style="margin-top: 20px;">
        <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
        <?php endif; ?>
        
        Page <?= $page ?> of <?= $total_pages ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
        <?php endif; ?>
    </div>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>