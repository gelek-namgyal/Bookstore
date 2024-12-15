<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    
    try {
        // Add to cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        
        $_SESSION['success'] = 'Item moved to cart!';
        header('Location: wishlist.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error moving item to cart';
    }
}

// Handle removing from wishlist
if (isset($_POST['remove_from_wishlist'])) {
    $book_id = $_POST['book_id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book_id]);
        
        $_SESSION['success'] = 'Item removed from wishlist!';
        header('Location: wishlist.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error removing item';
    }
}

// Fetch wishlist items
$stmt = $pdo->prepare("
    SELECT b.*, w.created_at as added_date 
    FROM wishlist w 
    JOIN books b ON w.book_id = b.id 
    WHERE w.user_id = ? 
    ORDER BY w.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$wishlist_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container py-5">
        <h2 class="mb-4">My Wishlist</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (empty($wishlist_items)): ?>
            <div class="alert alert-info">
                Your wishlist is empty. <a href="books.php">Browse books</a> to add some!
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo htmlspecialchars($item['cover_image'] ?? 'assets/images/default-book.jpg'); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                <p class="card-text">By <?php echo htmlspecialchars($item['author']); ?></p>
                                <p class="card-text text-primary">$<?php echo number_format($item['price'], 2); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                    
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="book_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                Added on <?php echo date('M d, Y', strtotime($item['added_date'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 