<?php
require_once '../../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Validate required fields
    if (empty($_POST['title']) || empty($_POST['author'])) {
        throw new Exception('Title and author are required');
    }

    // Handle file upload
    $cover_image = null;
    if (!empty($_FILES['cover_image']['name'])) {
        $upload_dir = '../../uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = $file_name;
        }
    }

    // Prepare data for database
    $sql = "INSERT INTO books (title, author, category_id, isbn, price, stock_quantity, 
            description, cover_image, publisher, published_date) 
            VALUES (:title, :author, :category_id, :isbn, :price, :stock_quantity, 
            :description, :cover_image, :publisher, :published_date)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'title' => $_POST['title'],
        'author' => $_POST['author'],
        'category_id' => $_POST['category_id'],
        'isbn' => $_POST['isbn'],
        'price' => $_POST['price'],
        'stock_quantity' => $_POST['stock_quantity'],
        'description' => $_POST['description'],
        'cover_image' => $cover_image,
        'publisher' => $_POST['publisher'],
        'published_date' => $_POST['published_date']
    ]);

    // Redirect back to books page
    header('Location: ../dashboard.php?success=1');
    exit;

} catch (Exception $e) {
    // Redirect with error
    header('Location: ../pages/books.php?error=' . urlencode($e->getMessage()));
    exit;
}
