<?php
// Define the base path for includes
$basePath = dirname(dirname(__FILE__)); // Go up two levels to get to backend/

// Include required files using absolute paths
require_once $basePath . '/config/config.php';
require_once $basePath . '/includes/functions.php';
require_once $basePath . '/includes/auth.php';
require_once $basePath . '/models/User.php';

// Require authentication for all user operations
$userId = requireAuth();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize user model
$userModel = new User($db);

// Check if user has admin role
$currentUser = new User($db);
if (!$currentUser->findById($userId) || !in_array($currentUser->role, ['admin', 'moderator'])) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

// Get request method and data
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? '';

if ($method === 'POST' && $action) {
    switch ($action) {
        case 'get_user':
            getUser($db, $userModel);
            break;
        case 'update_user':
            updateUser($db, $userModel);
            break;
        case 'delete_user':
            deleteUser($db, $userModel);
            break;
        case 'update_role':
            updateRole($db, $userModel);
            break;
        case 'add_user':
            addUser($db, $userModel);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
}

function getUser($db, $userModel) {
    $userId = $_POST['id'] ?? 0;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
    }
    
    if ($userModel->findById($userId)) {
        jsonResponse([
            'success' => true,
            'user' => [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'email' => $userModel->email,
                'role' => $userModel->role,
                'is_active' => $userModel->is_active,
                'created_at' => $userModel->created_at
            ]
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'User not found'], 404);
    }
}

function updateUser($db, $userModel) {
    $userId = $_POST['id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $isActive = $_POST['is_active'] ?? 1;
    
    if (!$userId || !$name || !$email) {
        jsonResponse(['success' => false, 'message' => 'User ID, name, and email are required'], 400);
    }
    
    // Check if another user already has this email
    $existingUser = new User($db);
    if ($existingUser->findByEmail($email) && $existingUser->id != $userId) {
        jsonResponse(['success' => false, 'message' => 'Another user already has this email address'], 400);
    }
    
    try {
        $stmt = $db->prepare("UPDATE users SET name=?, email=?, is_active=?, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$name, $email, $isActive, $userId])) {
            jsonResponse(['success' => true, 'message' => 'User updated successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update user'], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()], 500);
    }
}

function deleteUser($db, $userModel) {
    $userId = $_POST['id'] ?? 0;
    
    if (!$userId) {
        jsonResponse(['success' => false, 'message' => 'User ID is required'], 400);
    }
    
    try {
        // Soft delete the user (set is_active to 0)
        $stmt = $db->prepare("UPDATE users SET is_active=0, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$userId])) {
            jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()], 500);
    }
}

function updateRole($db, $userModel) {
    $userId = $_POST['id'] ?? 0;
    $role = $_POST['role'] ?? 'user';
    
    if (!$userId || !in_array($role, ['user', 'moderator', 'admin'])) {
        jsonResponse(['success' => false, 'message' => 'Valid user ID and role are required'], 400);
    }
    
    try {
        $stmt = $db->prepare("UPDATE users SET role=?, updated_at=NOW() WHERE id=?");
        if ($stmt->execute([$role, $userId])) {
            jsonResponse(['success' => true, 'message' => 'User role updated successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update user role'], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error updating user role: ' . $e->getMessage()], 500);
    }
}

function addUser($db, $userModel) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!$name || !$email || !$password) {
        jsonResponse(['success' => false, 'message' => 'Name, email, and password are required'], 400);
    }
    
    // Validate email format
    if (!validateEmail($email)) {
        jsonResponse(['success' => false, 'message' => 'Invalid email format'], 400);
    }
    
    // Check if user already exists
    if ($userModel->findByEmail($email)) {
        jsonResponse(['success' => false, 'message' => 'User with this email already exists'], 400);
    }
    
    // Hash the password
    $hashedPassword = hashPassword($password);
    
    try {
        $userModel->name = $name;
        $userModel->email = $email;
        $userModel->password_hash = $hashedPassword;
        $userModel->role = 'user'; // Default role
        
        $newUserId = $userModel->create();
        
        if ($newUserId) {
            jsonResponse(['success' => true, 'message' => 'User created successfully', 'user_id' => $newUserId]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create user'], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error creating user: ' . $e->getMessage()], 500);
    }
}
?>