<?php
require_once '../../config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if book_id is provided
    if (!isset($_POST['book_id']) || empty($_POST['book_id'])) {
        throw new Exception('Book ID is required');
    }

    $book_id = $_POST['book_id'];

    // First, get the book details to delete the cover image if exists
    $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();

    // Delete the book from database
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $result = $stmt->execute([$book_id]);

    if ($result) {
        // Delete the cover image if exists
        if ($book && $book['cover_image']) {
            $image_path = '../../uploads/books/' . $book['cover_image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        header('Location: ../dashboard.php?success=Book deleted successfully');
    } else {
        throw new Exception('Failed to delete book');
    }

} catch (Exception $e) {
    header('Location: ../pages/books.php?error=' . urlencode($e->getMessage()));
}
exit;
