<?php
session_start();
require_once 'config/database.php';

// Fetch featured books
$stmt = $pdo->query("SELECT b.*, c.name as category_name 
                     FROM books b 
                     LEFT JOIN categories c ON b.category_id = c.id 
                     ORDER BY b.created_at DESC LIMIT 6");
$featuredBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch stats
$statsQuery = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM books) as total_books,
    (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_customers,
    (SELECT COUNT(*) FROM categories) as total_categories
");
$stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

// Get cart count if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $cartStmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $cartStmt->execute([$_SESSION['user_id']]);
    $cartCount = $cartStmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookStore - Your Online Book Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">BookStore</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#featured">Featured</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#featured">All Books</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Cart Icon -->
                        <li class="nav-item me-2">
                            <a class="nav-link cart-icon" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <?php if ($cartCount > 0): ?>
                                    <span class="badge bg-danger cart-badge"><?php echo $cartCount; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <!-- Profile Icon - Opens Sidebar -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="offcanvas" data-bs-target="#profileSidebar">
                                <i class="fas fa-user"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary me-2" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Discover Your Next Favorite Book</h1>
                    <p class="lead">Browse through our collection of books and find your perfect read.</p>
                    <a href="#featured" class="btn btn-primary btn-lg">Start Shopping</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Books -->
    <section id="featured" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Books</h2>
            <div class="row">
                <?php foreach ($featuredBooks as $book): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card book-card">
                            <?php if ($book['cover_image']): ?>
                                <img src="uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                     class="card-img-top book-image" alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     style="height: 300px; object-fit: cover;">
                            <?php else: ?>
                                <img src="assets/images/no-cover.png" 
                                     class="card-img-top book-image" alt="No Cover Available"
                                     style="height: 300px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text">By <?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($book['category_name']); ?></small></p>
                                <p class="book-price">$<?php echo number_format($book['price'], 2); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-primary">View Details</a>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <form method="POST" action="cart.php" class="d-inline">
                                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                            <button type="submit" name="add_to_cart" class="btn btn-primary">
                                                <i class="fas fa-cart-plus"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4">Browse Categories</h2>
            <div class="row">
                <?php
                $categoryQuery = $pdo->query("SELECT c.*, COUNT(b.id) as book_count 
                                            FROM categories c 
                                            LEFT JOIN books b ON c.id = b.category_id 
                                            GROUP BY c.id 
                                            ORDER BY book_count DESC 
                                            LIMIT 6");
                $categories = $categoryQuery->fetchAll();
                
                foreach ($categories as $category):
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="category-card">
                            <h5><i class="fas fa-book-open me-2"></i><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="text-muted"><?php echo $category['book_count']; ?> Books Available</p>
                            <a href="category.php?id=<?php echo $category['id']; ?>" class="btn btn-outline-primary">Browse Category</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About BookStore</h5>
                    <p>Your one-stop destination for all types of books.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light">About Us</a></li>
                        <li><a href="#" class="text-light">Contact</a></li>
                        <li><a href="#" class="text-light">Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Connect With Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <p class="text-center mb-0">&copy; 2024 BookStore. All rights reserved.</p>
        </div>
    </footer>

    <!-- Profile Sidebar -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="offcanvas offcanvas-end" tabindex="-1" id="profileSidebar">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">My Account</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <!-- User Profile Section -->
            <div class="user-profile p-4 text-center border-bottom">
                <div class="profile-icon mb-3">
                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                </div>
                <h6 class="mb-1"><?php echo htmlspecialchars($_SESSION['full_name']); ?></h6>
                <p class="text-muted small mb-0">@<?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>

            <!-- Menu Items -->
            <div class="list-group list-group-flush">
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user me-2"></i>
                    <span>My Profile</span>
                </a>
                <a href="orders.php" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span>My Orders</span>
                    <?php
                    $orderStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
                    $orderStmt->execute([$_SESSION['user_id']]);
                    $orderCount = $orderStmt->fetchColumn();
                    if ($orderCount > 0): ?>
                        <span class="badge bg-primary"><?php echo $orderCount; ?></span>
                    <?php endif; ?>
                </a>

                <a href="wishlist.php" class="menu-item">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                    <?php
                    $wishStmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                    $wishStmt->execute([$_SESSION['user_id']]);
                    $wishCount = $wishStmt->fetchColumn();
                    if ($wishCount > 0): ?>
                        <span class="badge bg-primary"><?php echo $wishCount; ?></span>
                    <?php endif; ?>
                </a>

                <a href="cart.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Shopping Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="badge bg-primary"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>

                <div class="border-top my-3"></div>

                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>

                <a href="logout.php" class="menu-item text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
