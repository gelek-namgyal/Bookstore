<?php
require_once '../../config/database.php';

try {
    if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
        throw new Exception('Order ID and status are required');
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $result = $stmt->execute([$_POST['status'], $_POST['order_id']]);

    if ($result) {
        header('Location: ../pages/orders.php?success=Status updated successfully');
    } else {
        throw new Exception('Failed to update order status');
    }

} catch (Exception $e) {
    header('Location: ../pages/orders.php?error=' . urlencode($e->getMessage()));
}
exit;