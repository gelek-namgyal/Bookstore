<?php
require_once '../../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Check if username or email already exists for other users
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE (username = ? OR email = ?) 
        AND id != ?
    ");
    $stmt->execute([$username, $email, $user_id]);
    if ($stmt->fetch()) {
        throw new Exception('Username or email already exists');
    }

    // Update user
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, password = ?, role = ? 
            WHERE id = ?
        ");
        $stmt->execute([$username, $email, $password, $role, $user_id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, role = ? 
            WHERE id = ?
        ");
        $stmt->execute([$username, $email, $role, $user_id]);
    }

    header('Location: ../pages/users.php?success=User updated successfully');
} catch (Exception $e) {
    header('Location: ../pages/users.php?error=' . urlencode($e->getMessage()));
}
exit; 