<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: cart.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Get cart items with book prices
    $stmt = $pdo->prepare("
        SELECT c.*, b.price as book_price, b.stock_quantity 
        FROM cart c 
        JOIN books b ON c.book_id = b.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll();

    if (empty($cart_items)) {
        throw new Exception('Cart is empty');
    }

    // Calculate total
    $total_amount = 0;
    foreach ($cart_items as $item) {
        $total_amount += $item['book_price'] * $item['quantity'];
    }

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) 
        VALUES (?, ?, 'pending', ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $_POST['address'],
        $_POST['payment_method']
    ]);
    $order_id = $pdo->lastInsertId();

    // Add order items and update stock
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, book_id, quantity, price_per_unit, subtotal, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    foreach ($cart_items as $item) {
        $subtotal = $item['book_price'] * $item['quantity'];
        
        // Insert order item
        $stmt->execute([
            $order_id,
            $item['book_id'],
            $item['quantity'],
            $item['book_price'],
            $subtotal
        ]);

        // Update book stock
        $new_stock = $item['stock_quantity'] - $item['quantity'];
        $pdo->prepare("UPDATE books SET stock_quantity = ? WHERE id = ?")
            ->execute([$new_stock, $item['book_id']]);
    }

    // Clear cart
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);

    $pdo->commit();
    
    // Redirect to order confirmation
    header('Location: order_confirmation.php?order_id=' . $order_id);

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Failed to process order: " . $e->getMessage();
    header('Location: checkout.php');
}
exit();
