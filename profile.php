<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user data and statistics
$stmt = $pdo->prepare("
    SELECT u.*, 
    (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
    (SELECT COUNT(*) FROM wishlist WHERE user_id = u.id) as total_wishlist,
    (SELECT COUNT(*) FROM cart WHERE user_id = u.id) as cart_items
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get total spent
$stmt = $pdo->prepare("
    SELECT SUM(total_amount) as total_spent 
    FROM orders 
    WHERE user_id = ? AND status = 'completed'
");
$stmt->execute([$_SESSION['user_id']]);
$total_spent = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-circle fa-5x text-primary"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <p class="text-muted">
                            <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <a href="edit-profile.php" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-md-8">
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Orders</h6>
                                        <h2 class="mb-0"><?php echo $user['total_orders']; ?></h2>
                                    </div>
                                    <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Total Spent</h6>
                                        <h2 class="mb-0">$<?php echo number_format($total_spent, 2); ?></h2>
                                    </div>
                                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Wishlist Items</h6>
                                        <h2 class="mb-0"><?php echo $user['total_wishlist']; ?></h2>
                                    </div>
                                    <i class="fas fa-heart fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">Cart Items</h6>
                                        <h2 class="mb-0"><?php echo $user['cart_items']; ?></h2>
                                    </div>
                                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $orderStmt = $pdo->prepare("
                            SELECT * FROM orders 
                            WHERE user_id = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $orderStmt->execute([$_SESSION['user_id']]);
                        $recent_orders = $orderStmt->fetchAll();

                        if (empty($recent_orders)): ?>
                            <p class="text-muted">No orders yet</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recent_orders as $order): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">Order #<?php echo $order['id']; ?></h6>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div>
                                                <span class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                                <span class="ms-2">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="orders.php" class="btn btn-outline-primary btn-sm">View All Orders</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 