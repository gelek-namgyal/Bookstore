<?php
require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Validate role
    if (!in_array($role, ['admin', 'customer'])) {
        throw new Exception('Invalid role specified');
    }

    // Check if username or email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        throw new Exception('Username or email already exists');
    }

    // Insert new user
    $stmt = $pdo->prepare("
        INSERT INTO users (username, full_name, email, password, role, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$username, $full_name, $email, $password, $role]);

    header('Location: ../dashboard.php?success=User added successfully');
} catch (Exception $e) {
    header('Location: ../pages/users.php?error=' . urlencode($e->getMessage()));
}
exit; 