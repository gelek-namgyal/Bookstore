<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = $error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation logic here (same as before)
    // ... 

    if (empty($error)) {
        try {
            if (!empty($new_password)) {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $full_name, $email, password_hash($new_password, PASSWORD_DEFAULT), $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$username, $full_name, $email, $_SESSION['user_id']]);
            }
            
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully';
            
            header('Location: profile.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Error updating profile';
        }
    }
}
?>

<!-- Rest of the edit form HTML (same as the previous profile.php form) --> 