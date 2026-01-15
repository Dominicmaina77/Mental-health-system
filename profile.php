<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php');
    exit();
}

// Get user information from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// Include necessary files
require_once 'backend/config/config.php';
require_once 'backend/includes/functions.php';
require_once 'backend/models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

// Get user details
$userDetails = [];
$userModel->findById($user_id);
$userDetails = [
    'id' => $userModel->id,
    'name' => $userModel->name,
    'email' => $userModel->email,
    'created_at' => $userModel->created_at
];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if (!empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $userModel->id = $user_id;
                    $userModel->name = $name;
                    $userModel->email = $email;

                    if ($userModel->update()) {
                        // Update session variables
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $success = 'Profile updated successfully!';
                    } else {
                        $error = 'Failed to update profile.';
                    }
                } else {
                    $error = 'Please enter a valid name and email address.';
                }
                break;

            case 'update_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_new_password = $_POST['confirm_new_password'] ?? '';

                if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                    $error = 'Please fill in all password fields.';
                } elseif ($new_password !== $confirm_new_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long.';
                } else {
                    // Verify current password
                    $userModel->findById($user_id);

                    if (password_verify($current_password, $userModel->password_hash)) {
                        $userModel->id = $user_id;

                        if ($userModel->updatePassword(password_hash($new_password, PASSWORD_DEFAULT))) {
                            $success = 'Password changed successfully!';
                        } else {
                            $error = 'Failed to change password.';
                        }
                    } else {
                        $error = 'Current password is incorrect.';
                    }
                }
                break;

            case 'delete_account':
                $password = $_POST['password'] ?? '';

                // Verify password
                $userModel->id = $user_id;
                $userModel->loadFromDB();
                
                if (password_verify($password, $userModel->password)) {
                    if ($userModel->delete()) {
                        // Clear session and redirect
                        session_destroy();
                        header('Location: index.php');
                        exit();
                    } else {
                        $error = 'Failed to delete account.';
                    }
                } else {
                    $error = 'Password is incorrect.';
                }
                break;
        }
    }
}

// Calculate user stats
$memberSince = $userDetails['created_at'] ?? date('Y-m2-d');
$memberSinceFormatted = date('F Y', strtotime($memberSince));

// Get mood streak (assuming there's a method to get this)
require_once 'backend/models/MoodEntry.php';
$moodDatabase = new Database();
$moodDb = $moodDatabase->getConnection();
$moodEntry = new MoodEntry($moodDb);
$moodStreak = $moodEntry->getMoodStreak($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .profile-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .profile-container {
            padding: 3rem 0;
            background-color: var(--white);
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }

        @media (max-width: 992px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }

        .profile-sidebar {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--medium-blue), var(--accent-lavender));
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }

        .profile-main {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--pastel-blue);
            border-radius: 8px;
            margin-bottom: 1rem;
            background-color: var(--white);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
            }
        }

        .data-options {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-top: 2rem;
        }

        .theme-options {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .theme-option {
            padding: 1rem;
            border-radius: 8px;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }

        .theme-light {
            background-color: var(--light-blue);
            border: 2px solid var(--pastel-blue);
        }

        .theme-dark {
            background-color: #2c3e50;
            color: white;
            border: 2px solid #2c3e50;
        }

        .form-message {
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 500;
        }

        .form-message.success {
            background-color: rgba(122, 184, 217, 0.1);
            color: var(--dark-blue);
            border: 1px solid var(--medium-blue);
        }

        .form-message.error {
            background-color: rgba(255, 100, 100, 0.1);
            color: #d63031;
            border: 1px solid #ff7675;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="mood-tracker.php">Mood Tracker</a></li>
                <li><a href="journal.php">Journal</a></li>
                <li><a href="insights.php">Insights</a></li>
                <li><a href="reminders.php">Reminders</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="logout.php" class="btn-login">Log Out</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <section class="profile-header">
        <div class="container">
            <h1>Profile ⚙️</h1>
            <p>Manage your account and preferences</p>
        </div>
    </section>

    <section class="profile-container">
        <div class="container">
            <div class="profile-layout">
                <div class="profile-sidebar">
                    <div class="profile-avatar"><?php echo strtoupper(substr($user_name, 0, 1)); ?></div>
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <p style="color: var(--text-light);">Member since <?php echo $memberSinceFormatted; ?></p>

                    <div style="margin-top: 2rem;">
                        <div style="font-size: 2rem; font-weight: 700; color: var(--dark-blue);"><?php echo $moodStreak; ?></div>
                        <div>Day Streak 🔥</div>
                    </div>
                </div>

                <div class="profile-main">
                    <?php if (isset($error)): ?>
                        <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="form-message success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <div class="form-section">
                        <h3>Personal Information</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">
                            <input type="text" class="form-control" placeholder="Name" name="name" value="<?php echo htmlspecialchars($user_name); ?>">
                            <input type="email" class="form-control" placeholder="Email" name="email" value="<?php echo htmlspecialchars($user_email); ?>">

                            <div class="btn-group">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                                <button type="reset" class="btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3>Password</h3>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_password">
                            <input type="password" class="form-control" placeholder="Current Password" name="current_password">
                            <input type="password" class="form-control" placeholder="New Password" name="new_password">
                            <input type="password" class="form-control" placeholder="Confirm New Password" name="confirm_new_password">

                            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-key"></i> Update Password
                            </button>
                        </form>
                    </div>

                    <div class="data-options">
                        <h3>Data Management</h3>
                        <p style="color: var(--text-light); margin-bottom: 1rem;">
                            <i class="fas fa-lock"></i> Your data is private and secure
                        </p>

                        <div class="btn-group">
                            <button class="btn-secondary">
                                <i class="fas fa-download"></i> Export My Data
                            </button>
                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone and all your data will be permanently deleted.');">
                                <input type="hidden" name="action" value="delete_account">
                                <input type="password" class="form-control" placeholder="Enter password to confirm" name="password" style="display:inline-block; width:auto; margin-right: 10px;">
                                <button type="submit" class="btn-secondary" style="background-color: #ff7675; border-color: #ff7675; color: white;">
                                    <i class="fas fa-trash"></i> Delete Account
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="theme-options">
                        <h3 style="width: 100%;">Theme</h3>
                        <div class="theme-option theme-light active">
                            <div style="font-size: 1.5rem;">🌞</div>
                            <div>Light</div>
                        </div>
                        <div class="theme-option theme-dark">
                            <div style="font-size: 1.5rem;">🌙</div>
                            <div>Dark</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SootheSpace 🌸</h3>
                    <p>A safe haven for mental wellness</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="resources.php">Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Crisis Help</h4>
                    <p>National Crisis Line: 988</p>
                    <p>Text HOME to 741741</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const themeOptions = document.querySelectorAll('.theme-option');

        themeOptions.forEach(option => {
            option.addEventListener('click', function() {
                themeOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');

                const theme = this.querySelector('div:last-child').textContent.toLowerCase();
                alert(`Theme changed to ${theme} mode`);
            });
        });
    </script>
</body>
</html>