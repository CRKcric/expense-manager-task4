<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .dashboard-actions a button {
            min-width: 160px;
            padding: 12px;
            font-size: 1rem;
        }
        .welcome {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Dashboard</h1>
    <div class="welcome">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> 👋</h2>
        <p>You are logged in to the Expense Manager.</p>
    </div>

    <div class="dashboard-actions">
        <a href="add_transaction.php"><button>➕ Add Transaction</button></a>
        <a href="view_transaction.php"><button>📜 View Transactions</button></a>
        <a href="logout.php"><button style="background: #b71c1c;">🚪 Logout</button></a>
    </div>
</div>
</body>
</html>
