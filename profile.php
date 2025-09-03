<?php
session_start();
require 'db.php';
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$message = '';
// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "❌ Error: " . htmlspecialchars($e->getMessage());
}
// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    if (empty($new_username) || empty($new_email) || empty($current_password)) {
        $message = "Username, Email and Current Password are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $message = "Current password is incorrect.";
            } else {
                // Update details
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$new_username, $new_email, $hashed_password, $_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$new_username, $new_email, $_SESSION['user_id']]);
                }
                $_SESSION['username'] = $new_username; // Update session username
                $message = "✅ Profile updated successfully.";
                // Refresh user data
                $stmt = $pdo->prepare("SELECT username, email, role, created_at FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $message = "❌ Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Expense Manager</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .profile-container h2 {
            text-align: center;
        }
        .profile-details, .profile-form {
            margin-top: 20px;
        }
        .profile-details p {
            margin: 8px 0;
        }
        .profile-form label {
            display: block;
            margin-top: 10px;
        }
        .profile-form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        .profile-form button, .back-btn {
            margin-top: 15px;
            padding: 10px 15px;
            width: 100%;
            background-color: #1976d2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .profile-form button:hover, .back-btn:hover {
            background-color: #1565c0;
        }
        .back-btn {
            background: #555;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>My Profile</h1>
    <?php if (!empty($message)) echo "<div class='alert'>" . htmlspecialchars($message) . "</div>"; ?>
    <form method="POST" class="profile-form">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        <label>Role:</label>
        <input type="text" value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" disabled>
        <label>Member Since:</label>
        <input type="text" value="<?php echo htmlspecialchars($user['created_at'] ?? ''); ?>" disabled>
        <label>Current Password:</label>
        <input type="password" name="current_password" required autocomplete="off">
        <label>New Password (leave blank to keep current):</label>
        <input type="password" name="new_password" autocomplete="off">
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" autocomplete="off">
        <button type="submit">Update Profile</button>
    </form>
    <a href="dashboard.php"><button type="button" class="back-btn">⬅️ Back to Dashboard</button></a>
</div>
</body>
</html>