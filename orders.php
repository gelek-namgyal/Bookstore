<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total orders count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Get orders for current page
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(2, $per_page, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .order-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease-in-out;
        }
        .status-badge {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <div class="container my-5">
        <h2 class="mb-4">My Orders</h2>

        <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
            <h4>No orders yet</h4>
            <p class="text-muted">Start shopping to place your first order!</p>
            <a href="index.php" class="btn btn-primary">Browse Books</a>
        </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
            <div class="card order-card mb-3">
                <div class="card-header bg-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                        </div>
                        <div class="col text-end">
                            <span class="badge bg-<?php 
                                echo $order['status'] === 'completed' ? 'success' : 
                                    ($order['status'] === 'pending' ? 'warning' : 'secondary'); 
                            ?> status-badge">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    // Get order items
                    $stmt = $pdo->prepare("
                        SELECT oi.*, b.title, b.cover_image 
                        FROM order_items oi
                        JOIN books b ON oi.book_id = b.id
                        WHERE oi.order_id = ?
                    ");
                    $stmt->execute([$order['id']]);
                    $items = $stmt->fetchAll();
                    ?>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <?php foreach ($items as $item): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <?php if ($item['cover_image']): ?>
                                            <img src="uploads/books/<?php echo $item['cover_image']; ?>" 
                                                 alt="Book Cover" style="width: 50px; margin-right: 10px;">
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                            <small class="text-muted">
                                                Qty: <?php echo $item['quantity']; ?> × 
                                                $<?php echo number_format($item['price_per_unit'], 2); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-md-end">
                                <p class="mb-1">
                                    <strong>Total Amount:</strong> 
                                    $<?php echo number_format($order['total_amount'], 2); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Order Date:</strong><br>
                                    <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                                </p>
                                <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-outline-primary mt-2">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ($total_pages > 1): ?>
            <nav aria-label="Orders pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 