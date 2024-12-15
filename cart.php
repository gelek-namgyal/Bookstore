<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    $quantity = $_POST['quantity'] ?? 1;

    try {
        // Check if item already exists in cart
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        $existing_item = $stmt->fetch();

        if ($existing_item) {
            // Update quantity
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
            $stmt->execute([$quantity, $existing_item['id']]);
        } else {
            // Insert new item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $book_id, $quantity]);
        }
        
        $_SESSION['success'] = 'Item added to cart successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error adding item to cart.';
    }
}

// Remove from cart
if (isset($_POST['remove_from_cart'])) {
    $cart_id = $_POST['cart_id'];
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
}

// Update quantity
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
}

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.*, b.title, b.price, b.stock_quantity, (b.price * c.quantity) as subtotal 
    FROM cart c 
    JOIN books b ON c.book_id = b.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = array_sum(array_column($cart_items, 'subtotal'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .cart-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .quantity-input {
            width: 70px;
        }
    </style>
</head>
<body>

    <div class="container my-5">
        <h2 class="mb-4">Shopping Cart</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">Your cart is empty.</div>
            <a href="books.php" class="btn btn-primary">Continue Shopping</a>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="text-muted">Price: $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                            <div class="col-md-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock_quantity']; ?>" 
                                           class="form-control quantity-input">
                                    <button type="submit" name="update_quantity" class="btn btn-sm btn-secondary mt-2">
                                        Update
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-2">
                                <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                            </div>
                            <div class="col-md-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="remove_from_cart" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="cart-summary mt-4">
                    <div class="row">
                        <div class="col-md-8">
                            <a href="books.php" class="btn btn-secondary">Continue Shopping</a>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Cart Summary</h5>
                                    <p class="card-text">Total: $<?php echo number_format($total, 2); ?></p>
                                    <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 