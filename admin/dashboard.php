<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BookStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <i class="fas fa-book-open fa-2x"></i>
            <h4>BookStore Admin</h4>
        </div>
        <div class="sidebar-menu">
            <a href="#" class="menu-item active" data-page="dashboard">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="menu-item" data-page="books">
                <i class="fas fa-book"></i>
                <span>Books</span>
            </a>
            <a href="#" class="menu-item" data-page="order">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
            <a href="#" class="menu-item" data-page="user">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="#" class="menu-item" data-page="categories">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="../logout.php" class="menu-item text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-content" id="mainContent">
        <!-- Content will be loaded here -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load dashboard by default
    loadPage('dashboard');

    // Add click handlers to menu items
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                
                // Update active state
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                loadPage(page);
            }
        });
    });
});

function loadPage(page) {
    const contentDiv = document.getElementById('mainContent');
    contentDiv.innerHTML = '<div class="loading"></div>'; // Show loading state

    fetch(`pages/${page}.php`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            contentDiv.innerHTML = '<div class="alert alert-danger">Error loading content</div>';
        });
}
</script>
</body>
</html>