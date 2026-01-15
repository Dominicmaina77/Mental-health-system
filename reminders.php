<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['user_name'] ?? 'User';

// Include necessary files
require_once 'backend/config/config.php';
require_once 'backend/includes/functions.php';
require_once 'backend/models/Reminder.php';

$database = new Database();
$db = $database->getConnection();
$reminder = new Reminder($db);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $reminder_time = $_POST['reminder_time'] ?? '';
                $reminder_type = $_POST['reminder_type'] ?? 'daily';

                if (!empty($title) && !empty($reminder_time)) {
                    $reminder->user_id = $_SESSION['user_id'];
                    $reminder->title = $title;
                    $reminder->description = $description ?: null;
                    $reminder->reminder_time = $reminder_time;
                    $reminder->reminder_type = $reminder_type;
                    $reminder->is_active = 1;

                    if ($reminder->create()) {
                        $success = 'Reminder set successfully!';
                    } else {
                        $error = 'Failed to set reminder.';
                    }
                } else {
                    $error = 'Please enter a title and time for the reminder.';
                }
                break;

            case 'toggle':
                $id = intval($_POST['id'] ?? 0);
                $isActive = intval($_POST['is_active'] ?? 0);

                if ($id > 0) {
                    $reminder->id = $id;
                    $reminder->user_id = $_SESSION['user_id'];

                    if ($reminder->toggleActive($isActive)) {
                        $success = $isActive ? 'Reminder activated!' : 'Reminder deactivated!';
                    } else {
                        $error = 'Failed to update reminder status.';
                    }
                } else {
                    $error = 'Invalid reminder ID.';
                }
                break;

            case 'delete':
                $id = intval($_POST['id'] ?? 0);

                if ($id > 0) {
                    $reminder->id = $id;

                    if ($reminder->delete()) {
                        $success = 'Reminder deleted successfully!';
                    } else {
                        $error = 'Failed to delete reminder.';
                    }
                } else {
                    $error = 'Invalid reminder ID.';
                }
                break;
        }
    }
}

// Get active reminders for the user
$activeReminders = $reminder->getByUser($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .reminders-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .reminders-container {
            padding: 3rem 0;
            background-color: var(--white);
        }

        .reminder-form {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--pastel-blue);
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .reminders-list {
            margin-top: 2rem;
        }

        .reminder-item {
            background-color: var(--light-blue);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .reminder-info h4 {
            margin: 0;
            color: var(--dark-blue);
        }

        .reminder-time {
            background-color: var(--medium-blue);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .notification-options {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            margin-top: 2rem;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .checkbox-group {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .checkbox-group {
                flex-direction: column;
                gap: 1rem;
            }
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
                <li><a href="reminders.php" class="active">Reminders</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="btn-login">Log Out</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <section class="reminders-header">
        <div class="container">
            <h1>Reminders 🔔</h1>
            <p>Set gentle reminders for self-care and check-ins</p>
        </div>
    </section>

    <section class="reminders-container">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($success)): ?>
                <div class="form-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="reminder-form">
                <h3>Create New Reminder</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <input type="text" class="form-control" placeholder="Reminder title" name="title" id="reminder-title" required>

                    <div class="form-grid">
                        <select class="form-control" name="reminder_type" id="reminder-type">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="custom">Custom</option>
                        </select>

                        <input type="time" class="form-control" name="reminder_time" id="reminder-time" value="18:00" required>
                    </div>

                    <textarea class="form-control" placeholder="Optional description" rows="3" name="description" id="reminder-desc"></textarea>

                    <button type="submit" class="btn-primary" id="save-reminder" style="width: 100%;">
                        <i class="fas fa-bell"></i> Set Reminder
                    </button>
                </form>
            </div>

            <div class="reminders-list">
                <h3>Your Active Reminders</h3>

                <?php if (!empty($activeReminders)): ?>
                    <?php foreach ($activeReminders as $rem): ?>
                        <div class="reminder-item">
                            <div class="reminder-info">
                                <h4><?php echo htmlspecialchars($rem['title']); ?></h4>
                                <p style="margin: 0.5rem 0 0; color: var(--text-light);"><?php echo htmlspecialchars($rem['description'] ?: 'No description'); ?></p>
                            </div>
                            <div class="reminder-time"><?php echo date('g:i A', strtotime($rem['reminder_time'])); ?></div>
                            <div class="reminder-actions">
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?php echo $rem['id']; ?>">
                                    <input type="hidden" name="is_active" value="<?php echo $rem['is_active'] ? 0 : 1; ?>">
                                    <button type="submit" class="btn-secondary" style="padding: 0.5rem 1rem; border-radius: 50px; margin-right: 0.5rem;">
                                        <i class="fas fa-<?php echo $rem['is_active'] ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this reminder?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $rem['id']; ?>">
                                    <button type="submit" class="btn-secondary" style="background-color: #ff7675; color: white; padding: 0.5rem 1rem; border-radius: 50px;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-light); padding: 2rem;">No reminders yet. Create your first one!</p>
                <?php endif; ?>
            </div>

            <div class="notification-options">
                <h3>Notification Settings</h3>
                <div class="checkbox-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" checked>
                        <span>In-app notifications</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox">
                        <span>Email reminders</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" checked>
                        <span>Quiet hours (10PM-7AM)</span>
                    </label>
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
</body>
</html>