<?php
require_once '../../config/database.php';

// Fetch statistics
$stats = [
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_books' => $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn()
];

// Fetch recent orders
$recent_orders = $pdo->query("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

// Fetch low stock books
$low_stock = $pdo->query("
    SELECT * FROM books 
    WHERE stock_quantity <= 5 
    ORDER BY stock_quantity ASC 
    LIMIT 5
")->fetchAll();
?>

<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3><?php echo $stats['total_orders']; ?></h3>
                <p>Total Orders</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3><?php echo $stats['total_books']; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card danger">
                <div class="icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Low Stock -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-8">
            <div class="activity-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="#" class="btn btn-sm btn-primary" onclick="loadPage('orders')">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-md-4">
            <div class="activity-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Low Stock Alert</h5>
                    <a href="#" class="btn btn-sm btn-warning" onclick="loadPage('books')">Manage Stock</a>
                </div>
                <div class="list-group">
                    <?php foreach ($low_stock as $book): ?>
                        <div class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                <small class="text-danger"><?php echo $book['stock_quantity']; ?> left</small>
                            </div>
                            <p class="mb-1">By <?php echo htmlspecialchars($book['author']); ?></p>
                            <small class="text-muted">
                                Price: $<?php echo number_format($book['price'], 2); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="chart-card">
                <h5 class="mb-4">Monthly Sales Overview</h5>
                <div id="salesChart" style="height: 300px;">
                    <!-- Add your preferred charting library here -->
                </div>
            </div>
        </div>
    </div>
</div>