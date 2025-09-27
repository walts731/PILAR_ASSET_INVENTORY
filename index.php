<?php
session_start();
require_once "connect.php"; // Include database connection
// Fetch system settings (logo and title)
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];
if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $system = $result->fetch_assoc();
    }
}
// Include login engine AFTER fetching system settings to avoid closing the connection prematurely
require_once "engine/login_engine.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - <?= htmlspecialchars($system['system_title']) ?></title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="bg-light min-vh-100 d-flex align-items-center justify-content-center login_body">

    <div class="container-fluid">
        <div class="row min-vh-100">

            <!-- Left Container (Carousel with Background and Typing Text) -->
            <div class="col-md-6 p-0">
                <div id="welcomeCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-inner h-100">

                        <!-- Slide 1 -->
                        <div class="carousel-item active h-100 position-relative bg-cover bg-center" style="background-image: url('img/pilar.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Welcome!</h1>
                                    <p class="fs-5 typing-text" data-text="to <?= htmlspecialchars($system['system_title']) ?>. Please log in to continue."></p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item h-100 position-relative bg-cover bg-center" style="background-image: url('img/chess.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Efficient Tracking</h1>
                                    <p class="fs-5 typing-text" data-text="Keep your inventory organized and accessible."></p>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item h-100 position-relative bg-cover bg-center" style="background-image: url('img/pilar3.jpg');">
                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background-color: rgba(0, 0, 0, 0.5); z-index: 1;"></div>
                            <div class="d-flex align-items-center justify-content-center h-100 z-2 position-relative text-white text-center px-4">
                                <div>
                                    <h1 class="fw-bold mb-3">Team Collaboration</h1>
                                    <p class="fs-5 typing-text" data-text="Work together with your team seamlessly."></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right Container (Login Form) -->
            <div class="col-md-6 d-flex align-items-center justify-content-center ">
                <div class="card shadow-lg p-4 w-100" style="max-width: 400px;">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold">LOGIN</h3>
                        <img src="img/<?= htmlspecialchars($system['logo']) ?>" alt="Website Logo" class="img-fluid" style="max-width: 100px;" />
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
                        <button type="submit" id="loginBtn" class="btn btn-primary w-100">Login</button>


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
    <script src="js/login.js"></script>
</body>


</html>