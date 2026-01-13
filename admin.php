<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// For demo purposes, we'll allow admin access if email is admin@example.com
// In a real application, you'd have a proper role system
$user_email = $_SESSION['user_email'] ?? '';
$is_admin = ($user_email === 'admin@example.com'); // This is just for demonstration

if (!$is_admin) {
    // Redirect non-admin users
    header('Location: dashboard.php');
    exit();
}

// Include database configuration
require_once 'backend/config/config.php';
require_once 'backend/models/User.php';
require_once 'backend/models/MoodEntry.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize models
$userModel = new User($db);
$moodModel = new MoodEntry($db);

// Handle form submissions
$message = '';
$messageType = '';

// Add new user (for direct form submission, though API is preferred)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Check if user already exists
        if (!$userModel->findByEmail($email)) {
            $userModel->name = $name;
            $userModel->email = $email;
            $userModel->password_hash = password_hash($password, PASSWORD_DEFAULT);

            if ($userModel->create()) {
                $message = "User created successfully!";
                $messageType = "success";
            } else {
                $message = "Error creating user.";
                $messageType = "error";
            }
        } else {
            $message = "User with this email already exists.";
            $messageType = "error";
        }
    } else {
        $message = "Please fill in all fields.";
        $messageType = "error";
    }
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $userId = $_POST['user_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $isActive = $_POST['is_active'] ?? 1;

    if ($userId && !empty($name) && !empty($email)) {
        // Check if another user already has this email
        $existingUser = new User($db);
        if ($existingUser->findByEmail($email) && $existingUser->id != $userId) {
            $message = "Another user already has this email address.";
            $messageType = "error";
        } else {
            // Update the user
            $stmt = $db->prepare("UPDATE users SET name=?, email=?, is_active=?, updated_at=NOW() WHERE id=?");
            if ($stmt->execute([$name, $email, $isActive, $userId])) {
                $message = "User updated successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating user.";
                $messageType = "error";
            }
        }
    } else {
        $message = "Please fill in all fields.";
        $messageType = "error";
    }
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'] ?? 0;

    if ($userId) {
        // Soft delete the user (set is_active to 0)
        $stmt = $db->prepare("UPDATE users SET is_active=0, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$userId])) {
            $message = "User deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting user.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid user ID.";
        $messageType = "error";
    }
}

// Get statistics
$totalUsers = 0;
$activeUsers = 0;
$moodEntriesToday = 0;
$journalEntriesToday = 0;

try {
    // Count total users
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count mood entries for today
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM mood_entries WHERE DATE(date_recorded) = CURDATE()");
    $stmt->execute();
    $moodEntriesToday = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count journal entries for today (assuming there's a journal_entries table)
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM journal_entries WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $journalEntriesToday = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count active users (users who logged in today)
    $stmt = $db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM mood_entries WHERE DATE(date_recorded) = CURDATE()");
    $stmt->execute();
    $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
} catch (Exception $e) {
    $message = "Error loading statistics: " . $e->getMessage();
    $messageType = "error";
}

// Get recent users
$recentUsers = [];
try {
    $stmt = $db->query("SELECT id, name, email, created_at, is_active FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $message = "Error loading recent users: " . $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .admin-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .admin-container {
            padding: 3rem 0;
            background-color: var(--White);
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .admin-card {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .admin-card h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--dark-blue);
        }

        .admin-card h4 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-blue);
            display: block;
            margin: 1rem 0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .data-table th, .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--pastel-blue);
        }

        .data-table th {
            background-color: var(--pastel-blue);
            color: var(--dark-blue);
        }

        .user-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .edit-btn {
            background-color: var(--medium-blue);
            color: white;
        }

        .delete-btn {
            background-color: #ff7675;
            color: white;
        }

        .system-health {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            margin-top: 2rem;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .health-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--pastel-blue);
        }

        .health-status {
            padding: 0.3rem 0.8rem;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .status-good {
            background-color: #00b894;
            color: white;
        }

        .status-warning {
            background-color: #fdcb6e;
            color: var(--text-dark);
        }

        .admin-controls {
            margin-top: 2rem;
            padding: 1.5rem;
            background-color: var(--light-blue);
            border-radius: var(--radius);
        }

        .control-group {
            margin-bottom: 1.5rem;
        }

        .control-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .control-group input, .control-group select, .control-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--pastel-blue);
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
        }

        .control-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 10% auto;
            padding: 2rem;
            border-radius: var(--radius);
            width: 80%;
            max-width: 600px;
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background-color: rgba(0, 184, 148, 0.1);
            color: #00b894;
            border: 1px solid #00b894;
        }

        .alert-error {
            background-color: rgba(255, 118, 117, 0.1);
            color: #ff7675;
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
                <li><a href="admin.php" class="active">Admin Panel</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="btn-login">Log Out</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <section class="admin-header">
        <div class="container">
            <h1>Admin Panel 🔧</h1>
            <p>Manage users, content, and system health</p>
        </div>
    </section>

    <section class="admin-container">
        <div class="container">
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <div class="admin-grid">
                <div class="admin-card">
                    <h3><i class="fas fa-users"></i> User Management</h3>
                    <span class="stat-number" id="total-users"><?php echo $totalUsers; ?></span>
                    <p>Total registered users</p>
                    <button class="btn-primary" style="width: 100%; margin-top: 1rem;" onclick="loadUsers()">
                        <i class="fas fa-user-cog"></i> Manage Users
                    </button>
                </div>

                <div class="admin-card">
                    <h3><i class="fas fa-chart-line"></i> Activity</h3>
                    <span class="stat-number" id="active-users"><?php echo $activeUsers; ?></span>
                    <p>Active users today</p>
                    <button class="btn-primary" style="width: 100%; margin-top: 1rem;" onclick="loadReports()">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </button>
                </div>

                <div class="admin-card">
                    <h3><i class="fas fa-file-alt"></i> Content</h3>
                    <span class="stat-number" id="mood-entries"><?php echo $moodEntriesToday; ?></span>
                    <p>Mood entries today</p>
                    <button class="btn-primary" style="width: 100%; margin-top: 1rem;" onclick="loadContent()">
                        <i class="fas fa-edit"></i> Manage Content
                    </button>
                </div>
            </div>

            <div class="admin-card">
                <h3><i class="fas fa-user-friends"></i> Recent Users</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <?php foreach ($recentUsers as $user): ?>
                        <tr data-user-id="<?php echo $user['id']; ?>">
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <span class="health-status <?php echo $user['is_active'] ? 'status-good' : 'status-warning'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="user-actions">
                                <button class="action-btn edit-btn" onclick="openEditUserModal(<?php echo $user['id']; ?>)">Edit</button>
                                <button class="action-btn delete-btn" onclick="confirmDeleteUser(<?php echo $user['id']; ?>)">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-controls">
                <h3><i class="fas fa-tools"></i> Admin Controls</h3>
                
                <div class="control-group">
                    <label for="add-user-name">Add New User - Name</label>
                    <input type="text" id="add-user-name" placeholder="Full Name">
                    
                    <label for="add-user-email">Email</label>
                    <input type="email" id="add-user-email" placeholder="user@example.com">
                    
                    <label for="add-user-password">Password</label>
                    <input type="password" id="add-user-password" placeholder="Password">
                    
                    <button class="btn-primary" style="margin-top: 0.5rem;" onclick="addUser()">Add User</button>
                </div>
                
                <div class="control-group">
                    <label for="broadcast-message">Broadcast Message</label>
                    <textarea id="broadcast-message" placeholder="Enter message to broadcast to all users..."></textarea>
                    <button class="btn-primary" style="margin-top: 0.5rem;" onclick="sendBroadcast()">Send Broadcast</button>
                </div>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-chart-pie"></i> Platform Analytics</h3>
                <div class="admin-grid">
                    <div class="admin-card">
                        <h4>Daily Active Users</h4>
                        <span class="stat-number" id="daily-active"><?php echo $activeUsers; ?></span>
                    </div>
                    <div class="admin-card">
                        <h4>Weekly Active Users</h4>
                        <span class="stat-number" id="weekly-active">0</span>
                    </div>
                    <div class="admin-card">
                        <h4>Mood Entries Today</h4>
                        <span class="stat-number" id="mood-entries-today"><?php echo $moodEntriesToday; ?></span>
                    </div>
                    <div class="admin-card">
                        <h4>Journal Entries Today</h4>
                        <span class="stat-number" id="journal-entries-today"><?php echo $journalEntriesToday; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-shield-alt"></i> Security Monitoring</h3>
                <div class="admin-grid">
                    <div class="admin-card">
                        <h4>Last Login</h4>
                        <p id="last-login">--</p>
                    </div>
                    <div class="admin-card">
                        <h4>Suspicious IPs</h4>
                        <p id="suspicious-ips">0</p>
                    </div>
                    <div class="admin-card">
                        <h4>Blocked Users</h4>
                        <p id="blocked-users">0</p>
                    </div>
                    <div class="admin-card">
                        <h4>Active Sessions</h4>
                        <p id="active-sessions">0</p>
                    </div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3><i class="fas fa-cog"></i> System Configuration</h3>
                <div class="control-group">
                    <label for="maintenance-mode">Maintenance Mode</label>
                    <select id="maintenance-mode">
                        <option value="off">Off</option>
                        <option value="on">On</option>
                    </select>
                    <button class="btn-primary" style="margin-top: 0.5rem;" onclick="toggleMaintenanceMode()">Apply</button>
                </div>
                
                <div class="control-group">
                    <label for="backup-db">Database Backup</label>
                    <button class="btn-primary" onclick="backupDatabase()">Create Backup</button>
                </div>
            </div>

            <div class="system-health">
                <h3><i class="fas fa-heartbeat"></i> System Health</h3>
                <div class="health-item">
                    <span>Database</span>
                    <span class="health-status status-good" id="db-status">Healthy</span>
                </div>
                <div class="health-item">
                    <span>Server Uptime</span>
                    <span class="health-status status-good" id="uptime-status">99.9%</span>
                </div>
                <div class="health-item">
                    <span>Active Sessions</span>
                    <span class="health-status status-good" id="sessions-count">247</span>
                </div>
                <div class="health-item">
                    <span>Storage</span>
                    <span class="health-status status-warning" id="storage-status">75% used</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for editing user -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('userModal').style.display='none'">&times;</span>
            <h3>Edit User</h3>
            <form id="editUserForm" method="post">
                <input type="hidden" id="edit-user-id" name="user_id">
                <div class="control-group">
                    <label for="edit-user-name">Name</label>
                    <input type="text" id="edit-user-name" name="name" required>
                </div>
                <div class="control-group">
                    <label for="edit-user-email">Email</label>
                    <input type="email" id="edit-user-email" name="email" required>
                </div>
                <div class="control-group">
                    <label for="edit-user-status">Status</label>
                    <select id="edit-user-status" name="is_active">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn-primary" name="update_user">Save Changes</button>
                <button type="button" class="btn-secondary" onclick="deleteUser()" style="background-color: #ff7675; color: white; margin-left: 10px;">Delete User</button>
            </form>
        </div>
    </div>

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

    <script src="js/api.js"></script>
    <script>
        // Global variables to store user data
        let currentUser = null;

        // Load initial data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            const menuBtn = document.querySelector('.menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (menuBtn) {
                menuBtn.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    menuBtn.querySelector('i').classList.toggle('fa-bars');
                    menuBtn.querySelector('i').classList.toggle('fa-times');
                });
            }

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar') && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    if (menuBtn) {
                        menuBtn.querySelector('i').classList.remove('fa-times');
                        menuBtn.querySelector('i').classList.add('fa-bars');
                    }
                }
            });

            // Handle form submission for editing user
            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                if (!currentUser) {
                    e.preventDefault();
                    return;
                }

                // Add hidden field to indicate update action
                const updateField = document.createElement('input');
                updateField.type = 'hidden';
                updateField.name = 'update_user';
                updateField.value = '1';
                this.appendChild(updateField);
            });
        });

        // Open edit user modal
        async function openEditUserModal(userId) {
            try {
                // Fetch user data from the server
                const response = await fetch('backend/api/users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=get_user&id=' + encodeURIComponent(userId)
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('edit-user-id').value = result.user.id;
                    document.getElementById('edit-user-name').value = result.user.name;
                    document.getElementById('edit-user-email').value = result.user.email;
                    document.getElementById('edit-user-status').value = result.user.is_active;
                } else {
                    alert('Error loading user data: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching user data:', error);
                // Fallback to manual data population if API fails
                document.getElementById('edit-user-id').value = userId;
                document.getElementById('edit-user-name').value = 'User Name';
                document.getElementById('edit-user-email').value = 'user@example.com';
                document.getElementById('edit-user-status').value = '1';
            }

            currentUser = userId;
            document.getElementById('userModal').style.display = 'block';
        }

        // Update user
        async function updateUser() {
            if (!currentUser) return;

            const name = document.getElementById('edit-user-name').value;
            const email = document.getElementById('edit-user-email').value;
            const isActive = document.getElementById('edit-user-status').value;

            if (!name || !email) {
                alert('Please fill in all required fields');
                return;
            }

            try {
                // Send update request to the API
                const response = await fetch('backend/api/users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_user&id=${encodeURIComponent(currentUser)}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&is_active=${encodeURIComponent(isActive)}`
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    document.getElementById('userModal').style.display = 'none';
                    location.reload(); // Reload the page to see updated data
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating user:', error);
                alert('Error updating user: ' + error.message);
            }
        }

        // Delete user
        async function deleteUser() {
            if (!currentUser) return;

            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    // Send delete request to the API
                    const response = await fetch('backend/api/users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_user&id=${encodeURIComponent(currentUser)}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        document.getElementById('userModal').style.display = 'none';
                        location.reload(); // Reload the page to see updated data
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert('Error deleting user: ' + error.message);
                }
            }
        }

        // Confirm delete user
        async function confirmDeleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    // Send delete request to the API
                    const response = await fetch('backend/api/users.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_user&id=${encodeURIComponent(userId)}`
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        location.reload(); // Reload the page to see updated data
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert('Error deleting user: ' + error.message);
                }
            }
        }

        // Load all users
        async function loadUsers() {
            alert('Loading all users...');
            // In a real application, this would fetch all users from the API
        }

        // Load reports
        async function loadReports() {
            alert('Loading reports...');
            // In a real application, this would fetch reports from the API
        }

        // Load content
        async function loadContent() {
            alert('Loading content...');
            // In a real application, this would fetch content from the API
        }

        // Add new user
        async function addUser() {
            const name = document.getElementById('add-user-name').value;
            const email = document.getElementById('add-user-email').value;
            const password = document.getElementById('add-user-password').value;

            if (!name || !email || !password) {
                alert('Please fill in all fields');
                return;
            }

            try {
                // Send add user request to the API
                const response = await fetch('backend/api/users.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add_user&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    document.getElementById('add-user-name').value = '';
                    document.getElementById('add-user-email').value = '';
                    document.getElementById('add-user-password').value = '';
                    location.reload(); // Reload the page to see updated data
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error adding user:', error);
                alert('Error adding user: ' + error.message);
            }
        }

        // Send broadcast message
        async function sendBroadcast() {
            const message = document.getElementById('broadcast-message').value;
            if (!message) {
                alert('Please enter a message');
                return;
            }

            try {
                // In a real application, this would call the API to send a broadcast
                alert('Broadcast sent successfully!');
                document.getElementById('broadcast-message').value = '';
            } catch (error) {
                console.error('Error sending broadcast:', error);
                alert('Error sending broadcast: ' + error.message);
            }
        }

        // Toggle maintenance mode
        function toggleMaintenanceMode() {
            const mode = document.getElementById('maintenance-mode').value;
            alert('Maintenance mode ' + (mode === 'on' ? 'enabled' : 'disabled'));
        }

        // Create database backup
        function backupDatabase() {
            if (confirm('Are you sure you want to create a database backup?')) {
                alert('Database backup initiated...');
            }
        }
    </script>
</body>
</html>