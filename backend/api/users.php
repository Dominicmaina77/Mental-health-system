<?php
// Include required files
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../models/User.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user has admin role
$user_id = $_SESSION['user_id'];
$current_user = new User($db);

if (!$current_user->findById($user_id)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if user has admin role
$is_admin = ($current_user->role === 'admin' || $current_user->role === 'moderator');

if (!$is_admin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden: Admin access required']);
    exit;
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Initialize database and user model
$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

switch ($action) {
    case 'get_user':
        $userId = $_POST['id'] ?? $_GET['id'] ?? 0;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }
        
        // Get user by ID
        if ($userModel->findById($userId)) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email,
                    'is_active' => $userModel->is_active,
                    'created_at' => $userModel->created_at
                ]
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        break;
        
    case 'update_user':
        $userId = $_POST['id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $isActive = $_POST['is_active'] ?? 1;
        
        if (!$userId || !$name || !$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID, name, and email are required']);
            exit;
        }
        
        // Check if another user already has this email
        $existingUser = new User($db);
        if ($existingUser->findByEmail($email) && $existingUser->id != $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Another user already has this email address']);
            exit;
        }
        
        // Update the user
        $stmt = $db->prepare("UPDATE users SET name=?, email=?, is_active=?, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$name, $email, $isActive, $userId])) {
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'is_active' => $isActive
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating user']);
        }
        break;
        
    case 'delete_user':
        $userId = $_POST['id'] ?? 0;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }
        
        // Soft delete the user (set is_active to 0)
        $stmt = $db->prepare("UPDATE users SET is_active=0, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$userId])) {
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error deleting user']);
        }
        break;
        
    case 'get_all_users':
        $limit = intval($_POST['limit'] ?? $_GET['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? $_GET['offset'] ?? 0);
        
        // Get all users
        $stmt = $db->prepare("SELECT id, name, email, created_at, is_active FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $countStmt = $db->query("SELECT COUNT(*) as count FROM users");
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        echo json_encode([
            'success' => true,
            'users' => $users,
            'total_count' => $totalCount,
            'limit' => $limit,
            'offset' => $offset
        ]);
        break;

    case 'add_user':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Name, email, and password are required']);
            exit;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }

        // Check if user already exists
        if ($userModel->findByEmail($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User with this email already exists']);
            exit;
        }

        // Create new user
        $userModel->name = $name;
        $userModel->email = $email;
        $userModel->password_hash = password_hash($password, PASSWORD_DEFAULT);
        $userModel->age_group = null; // Default value

        $userId = $userModel->create();
        if ($userId) {
            echo json_encode([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating user']);
        }
        break;

    case 'update_role':
        $userId = $_POST['id'] ?? 0;
        $newRole = $_POST['role'] ?? 'user';

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'User ID is required']);
            exit;
        }

        if (!in_array($newRole, ['user', 'admin', 'moderator'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid role']);
            exit;
        }

        // Update user role
        $stmt = $db->prepare("UPDATE users SET role=?, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$newRole, $userId])) {
            echo json_encode([
                'success' => true,
                'message' => 'User role updated successfully'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating user role']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>