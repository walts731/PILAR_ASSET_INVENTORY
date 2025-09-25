<?php
$pageTitle = 'Login - Pilar Inventory Management System';
require_once "includes/header.php";
require_once "engine/login_engine.php"; // Include login engine
?>

<style>
    /* Additional dark mode styles for login page */
    .login-container {
        background-color: var(--dark-card);
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .dark-mode .login-container {
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    }
    
    .dark-mode .form-control {
        background-color: #333;
        border-color: #444;
        color: #fff;
    }
    
    .dark-mode .form-control:focus {
        background-color: #333;
        color: #fff;
    }
    
    .dark-mode .btn-outline-primary {
        color: #fff;
        border-color: #0d6efd;
    }
    
    .dark-mode .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }
    
    .dark-mode .text-muted {
        color: #adb5bd !important;
    }
    
    /* Ensure the login form is properly centered */
    .login_body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
    
    .dark-mode .login_body {
        background-color: #121212;
    }
    
    /* Typing animation styles */
    .typing-text {
        min-height: 1.5em; /* Ensure consistent height while typing */
    }
</style>

<body class="login_body">