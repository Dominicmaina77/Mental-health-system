<?php
session_start();

// Include necessary files
require_once 'backend/config/config.php';
require_once 'backend/includes/functions.php';
require_once 'backend/includes/auth.php';
require_once 'backend/models/User.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            $error = 'Database connection failed.';
        } else {
            // Attempt login
            $user = new User($db);

            if ($user->findByEmail($email)) {
                if (verifyPassword($password, $user->password_hash)) {
                    if (!$user->is_active) {
                        $error = 'Account is deactivated.';
                    } else {
                        // Login successful
                        $token = login($user->id, $user->email, $user->name);

                        // Set remember me if checked
                        if ($remember) {
                            setcookie('user_token', $token, time() + (86400 * 30), "/"); // 30 days
                        }

                        // Redirect to dashboard
                        header('Location: dashboard.php');
                        exit();
                    }
                } else {
                    $error = 'Invalid credentials.';
                }
            } else {
                $error = 'Invalid credentials.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SootheSpace 🌸</title>
    <link rel="stylesheet" href="forms.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="features.php">Features</a></li>
                <li><a href="resources.php">Resources</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="login.php" class="btn-login active">Log In</a></li>
                <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Login Container -->
    <section class="login-container">
        <div class="container">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back! 🌸</h2>
                    <p>Sign in to continue your wellness journey</p>
                </div>

                <form id="login-form" method="POST" action="login.html">
                    <?php if (isset($error)): ?>
                        <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-toggle">
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                            <button type="button" class="toggle-password" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="remember-forgot">
                        <div class="remember-me">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="divider">
                    <span>Or continue with</span>
                </div>

                <div class="social-login">
                    <button class="social-btn google">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button class="social-btn apple">
                        <i class="fab fa-apple"></i> Apple
                    </button>
                </div>

                <div class="privacy-notice">
                    <i class="fas fa-lock"></i> Your data stays private — always.
                </div>

                <div class="register-link">
                    <p>New to SootheSpace? <a href="signup.html">Create an account</a></p>
                </div>

                <div class="login-quote">
                    <p>"Taking care of your mental health is an act of self-love. Every login is a step toward healing."</p>
                    <div class="author">- SootheSpace Team</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SootheSpace 🌸</h3>
                    <p>A safe haven for mental wellness, designed with care for young minds.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="features.php">Features</a></li>
                        <li><a href="resources.php">Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Data Safety</a></li>
                        <li><a href="#">Crisis Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Get Help</h4>
                    <p>If you're in crisis, please contact:</p>
                    <p class="crisis-line"><i class="fas fa-phone"></i> National Crisis Line: 988</p>
                    <p class="crisis-line"><i class="fas fa-comment"></i> Crisis Text Line: Text HOME to 741741</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 SootheSpace. All rights reserved. Made with <i class="fas fa-heart"></i> for youth mental wellness.</p>
            </div>
        </div>
    </footer>

    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = togglePassword.querySelector('i');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
        });
    </script>
</body>
</html>