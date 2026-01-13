<?php
session_start();

// Include necessary files
require_once 'backend/config/config.php';
require_once 'backend/includes/functions.php';
require_once 'backend/includes/auth.php';
require_once 'backend/models/User.php';

// Initialize variables
$error = '';
$success = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Account Information
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Step 2: Profile Information (if submitted)
    $ageGroup = $_POST['age_group'] ?? '';
    $goals = $_POST['goals'] ?? [];

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($ageGroup)) {
        $error = 'Please select your age group.';
    } else {
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();

        if (!$db) {
            $error = 'Database connection failed.';
        } else {
            // Check if user already exists
            $user = new User($db);

            if ($user->findByEmail($email)) {
                $error = 'Email already exists.';
            } else {
                // Create new user
                $user->name = sanitizeInput($name);
                $user->email = sanitizeInput($email);
                $user->password_hash = hashPassword($password);
                $user->age_group = sanitizeInput($ageGroup);

                // Set role - regular user by default
                $user->role = 'user';

                // Check if admin creation is requested with proper secret
                $adminSecret = $_POST['admin_secret'] ?? '';
                if ($adminSecret === 'make_me_admin') { // This should be a strong secret in production
                    $user->role = 'admin';
                }

                $userId = $user->create();
                
                if ($userId) {
                    // Login the user after registration
                    $token = login($userId, $user->email, $user->name);
                    
                    $success = 'Account created successfully! Welcome to SootheSpace.';
                    
                    // Redirect to dashboard after a short delay
                    header('Refresh: 2; URL=dashboard.php');
                } else {
                    $error = 'Registration failed. Please try again.';
                }
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
    <title>Sign Up - SootheSpace 🌸</title>
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
                <li><a href="login.php" class="btn-login">Log In</a></li>
                <li><a href="signup.php" class="btn-signup active">Sign Up</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Signup Container -->
    <section class="signup-container">
        <div class="container">
            <div class="signup-card">
                <div class="signup-header">
                    <h2>Join SootheSpace 🌸</h2>
                    <p>Start your mental wellness journey today</p>
                </div>

                <?php if ($error): ?>
                    <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="form-message success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <!-- Signup Form -->
                <form id="signup-form" method="POST" action="signup.php">
                    <!-- Step 1: Account Information -->
                    <div id="step-1" class="form-pages active">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Your name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <div class="password-toggle">
                                <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                                <button type="button" class="toggle-password" id="togglePassword1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm-password"><i class="fas fa-lock"></i> Confirm Password</label>
                            <div class="password-toggle">
                                <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
                                <button type="button" class="toggle-password" id="togglePassword2">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="terms">
                            <input type="checkbox" id="terms" name="terms" required>
                            <label for="terms">
                                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Hidden admin creation field for special access -->
                        <div id="admin-secret-field" style="display: none;">
                            <div class="form-group">
                                <label for="admin_secret"><i class="fas fa-key"></i> Admin Secret Key</label>
                                <input type="password" id="admin_secret" name="admin_secret" class="form-control" placeholder="Enter admin creation key">
                            </div>
                        </div>

                        <div class="form-navigation">
                            <div></div>
                            <button type="submit" class="btn-next">
                                Create Account <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>

                        <!-- Admin creation link for authorized personnel -->
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="#" onclick="toggleAdminField(); return false;" style="color: var(--medium-blue); text-decoration: none; font-size: 0.9rem;">
                                Create Admin Account?
                            </a>
                        </div>
                    </div>

                    <!-- Step 2: Profile Information (Hidden in this version since we're simplifying) -->
                    <div id="step-2" class="form-pages" style="display:none;">
                        <div class="form-group">
                            <label><i class="fas fa-user-friends"></i> Age Group</label>
                            <div class="age-group">
                                <div class="age-option" data-age="13-17">
                                    <i class="fas fa-user-graduate"></i>
                                    <div>13-17</div>
                                </div>
                                <div class="age-option" data-age="18-24">
                                    <i class="fas fa-university"></i>
                                    <div>18-24</div>
                                </div>
                                <div class="age-option" data-age="25+">
                                    <i class="fas fa-user-tie"></i>
                                    <div>25+</div>
                                </div>
                            </div>
                            <input type="hidden" id="age-group" name="age_group" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-bullseye"></i> What are your main wellness goals?</label>
                            <div class="checkbox-group">
                                <div class="checkbox-item">
                                    <input type="checkbox" id="goal-mood" name="goals[]" value="track-mood">
                                    <label for="goal-mood">Track my mood patterns</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="goal-journal" name="goals[]" value="journal">
                                    <label for="goal-journal">Keep a private journal</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="goal-stress" name="goals[]" value="manage-stress">
                                    <label for="goal-stress">Manage stress & anxiety</label>
                                </div>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="goal-sleep" name="goals[]" value="improve-sleep">
                                    <label for="goal-sleep">Improve sleep habits</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="login.php">Sign in here</a></p>
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
            function setupPasswordToggle(toggleBtn, inputId) {
                const passwordInput = document.getElementById(inputId);
                const eyeIcon = toggleBtn.querySelector('i');

                if (toggleBtn && passwordInput) {
                    toggleBtn.addEventListener('click', function() {
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
            }

            setupPasswordToggle(document.getElementById('togglePassword1'), 'password');
            setupPasswordToggle(document.getElementById('togglePassword2'), 'confirm-password');
        });

        function toggleAdminField() {
            const adminField = document.getElementById('admin-secret-field');
            if (adminField.style.display === 'none') {
                adminField.style.display = 'block';
            } else {
                adminField.style.display = 'none';
            }
        }
    </script>
</body>
</html>