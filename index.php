<?php
session_start();
require_once "connect.php"; // Include database connection

require_once "engine/login_engine.php"; // Include login engine
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Pilar Inventory Management System</title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        #sidebar {
            transition: all 0.3s ease-in-out;
        }

        .d-none {
            transform: translateX(-100%);
        }

        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            margin-top: -1px;
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
        }

        .preview-box {
            padding: 15px;
            border: 1px solid #ccc;
            background-color: #f8f9fa;
        }

        .paper-preview {
            width: 100%;
            height: 500px;
            border: 1px solid #ccc;
            background: white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: 'Arial', sans-serif;
            position: relative;
        }

        .paper-header {
            text-align: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .paper-header img {
            height: 50px;
            display: block;
            margin: 0 auto 10px auto;
        }

        .paper-content {
            flex-grow: 1;
            padding: 20px;
            font-size: 14px;
            color: #444;
            text-align: center;
        }

        .paper-footer {
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-style: italic;
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 576px) {
            .card-body h3 {
                font-size: 1.5rem;
            }

            input.form-control,
            .btn {
                font-size: 0.95rem;
            }
        }

        body {
            background: linear-gradient(135deg,rgb(43, 85, 201),rgb(142, 55, 213));
        }

        .card {
            backdrop-filter: blur(8px);
            background-color: rgba(255, 255, 255, 0.85);
            border: none;
            border-radius: 20px;
        }

        .card-body h3 {
            color: #0d6efd;
        }

        .input-group .form-control {
            border-radius: 10px 0 0 10px;
            border-right: none;
        }

        .input-group .input-group-text {
            border-radius: 0 10px 10px 0;
            border-left: none;
        }

        input.form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .btn-primary {
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056d2;
        }

        a.text-primary:hover {
            text-decoration: underline;
        }

        /* Body styles */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        /* Container styles */
        .container {
            background: rgba(22, 79, 211, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            animation: fadeIn 0.5s ease-in;
            transition: all 0.3s ease;
        }

        .bg-cover {
            background-size: cover;
        }

        .bg-center {
            background-position: center;
        }

        #typing-text::after {
            content: '|';
            animation: blink 0.8s step-start infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0;
            }
        }
    </style>
</head>

<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center login_body">

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left Container (Vacant Space with Background and Typing Text) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center text-white position-relative bg-cover bg-center" style="background-image: url('img/pilar.jpg');">
                <div class="text-center px-4 z-1">
                    <h1 class="fw-bold mb-3">Welcome!</h1>
                    <p id="typing-text" class="fs-5"></p>
                </div>
                <!-- Overlay (optional for readability) -->
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5);"></div>
            </div>


            <!-- Right Container (Login Form) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center ">
                <div class="card shadow-lg p-4 w-100" style="max-width: 400px;">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">LOGIN</h3>
                        <img src="img/PILAR LOGO TRANSPARENT.png" alt="Website Logo" class="img-fluid" style="max-width: 100px;" />
                    </div>

                    <?php if (!empty($login_error)) echo $login_error; ?>

                    <form method="post" autocomplete="off">
                        <input type="text" name="fakeusernameremembered" style="display:none">
                        <input type="password" name="fakepasswordremembered" style="display:none">

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold">Username</label>
                            <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required />
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required />
                                <span class="input-group-text bg-white border-start-0" id="togglePassword" style="cursor: pointer;">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Login Button -->
                        <button type="submit" class="btn btn-primary w-100">Login</button>

                        <!-- Forgot Password -->
                        <div class="mt-3 text-center">
                            <a href="forgot_password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Toggle Password -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.getElementById("eyeIcon");

            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                passwordField.type = "password";
                eyeIcon.classList.replace("bi-eye-slash", "bi-eye");
            }
        });

        const text = "to the Pilar Inventory Management System. Please log in to continue.";
        const typingElement = document.getElementById("typing-text");
        let index = 0;

        function typeWriter() {
            if (index < text.length) {
                typingElement.textContent += text.charAt(index);
                index++;
                setTimeout(typeWriter, 50); // typing speed
            }
        }

        typeWriter();
    </script>
</body>


</html>