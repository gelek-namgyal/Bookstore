<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$_GET['order_id'], $_SESSION['user_id']]);
$order = $stmt->fetch();

// If order not found or doesn't belong to user
if (!$order) {
    header('Location: index.php');
    exit();
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, b.title, b.cover_image
    FROM order_items oi
    JOIN books b ON oi.book_id = b.id
    WHERE oi.order_id = ?
");
$stmt->execute([$_GET['order_id']]);
$order_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                        <h2 class="card-title">Thank You for Your Order!</h2>
                        <p class="card-text">Your order has been successfully placed.</p>
                        <p class="mb-0">Order ID: #<?php echo $order['id']; ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Order Date:</strong><br>
                                <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Order Status:</strong><br>
                                <span class="badge bg-<?php 
                                    echo $order['status'] === 'completed' ? 'success' : 
                                        ($order['status'] === 'pending' ? 'warning' : 'secondary'); 
                                ?>"><?php echo ucfirst($order['status']); ?></span></p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Shipping Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Payment Method:</strong><br>
                                <?php echo ucfirst($order['payment_method']); ?></p>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['cover_image']): ?>
                                                    <img src="uploads/books/<?php echo $item['cover_image']; ?>" 
                                                         alt="Book Cover" style="width: 50px; margin-right: 10px;">
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($item['title']); ?>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price_per_unit'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="orders.php" class="btn btn-outline-primary">View All Orders</a>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 