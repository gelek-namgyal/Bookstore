<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Book ID is required');
    }

    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        throw new Exception('Book not found');
    }

    echo json_encode($book);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
