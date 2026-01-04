<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../models/User.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'register':
                register($db, $data);
                break;
            case 'login':
                login($db, $data);
                break;
            case 'logout':
                logout();
                break;
            case 'update_profile':
                updateProfile($db, $data);
                break;
            case 'update_password':
                updatePassword($db, $data);
                break;
            case 'delete_account':
                deleteAccount($db, $data);
                break;
            default:
                jsonResponse(['error' => 'Invalid action'], 400);
        }
    } else {
        jsonResponse(['error' => 'No action specified'], 400);
    }
} elseif ($method === 'GET') {
    // Check authentication status
    if (isAuthenticated()) {
        $userId = getCurrentUser();
        $user = new User($db);
        
        if ($user->findById($userId)) {
            jsonResponse([
                'authenticated' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'age_group' => $user->age_group,
                    'created_at' => $user->created_at
                ],
                'stats' => $user->getUserStats($userId)
            ]);
        } else {
            jsonResponse(['authenticated' => false], 401);
        }
    } else {
        jsonResponse(['authenticated' => false], 200);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function register($db, $data) {
    $errors = validateUserInput($data, ['name', 'email', 'password']);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    if (!validateEmail($data['email'])) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }

    if (strlen($data['password']) < 6) {
        jsonResponse(['error' => 'Password must be at least 6 characters'], 400);
    }

    $user = new User($db);
    
    if ($user->findByEmail($data['email'])) {
        jsonResponse(['error' => 'Email already exists'], 409);
    }

    $user->name = sanitizeInput($data['name']);
    $user->email = sanitizeInput($data['email']);
    $user->password_hash = hashPassword($data['password']);
    $user->age_group = isset($data['age_group']) ? sanitizeInput($data['age_group']) : null;

    $userId = $user->create();
    if ($userId) {
        // Log the user in after registration
        $token = login($userId, $user->email, $user->name);
        
        jsonResponse([
            'message' => 'User registered successfully',
            'user_id' => $userId,
            'email' => $user->email,
            'name' => $user->name,
            'token' => $token
        ]);
    } else {
        jsonResponse(['error' => 'Registration failed'], 500);
    }
}

function login($db, $data) {
    $errors = validateUserInput($data, ['email', 'password']);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    $user = new User($db);
    
    if ($user->findByEmail($data['email'])) {
        if (verifyPassword($data['password'], $user->password_hash)) {
            if (!$user->is_active) {
                jsonResponse(['error' => 'Account is deactivated'], 401);
            }
            
            $token = login($user->id, $user->email, $user->name);
            
            jsonResponse([
                'message' => 'Login successful',
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'token' => $token,
                'stats' => $user->getUserStats($user->id)
            ]);
        } else {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    } else {
        jsonResponse(['error' => 'Invalid credentials'], 401);
    }
}

function updateProfile($db, $data) {
    $userId = requireAuth();
    
    $errors = validateUserInput($data, ['name']);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    $user = new User($db);
    
    if (!$user->findById($userId)) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    $user->name = sanitizeInput($data['name']);
    $user->age_group = isset($data['age_group']) ? sanitizeInput($data['age_group']) : $user->age_group;

    if ($user->update()) {
        jsonResponse([
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age_group' => $user->age_group
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to update profile'], 500);
    }
}

function updatePassword($db, $data) {
    $userId = requireAuth();
    
    $errors = validateUserInput($data, ['current_password', 'new_password']);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    if (strlen($data['new_password']) < 6) {
        jsonResponse(['error' => 'New password must be at least 6 characters'], 400);
    }

    $user = new User($db);
    
    if (!$user->findById($userId)) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Verify current password
    if (!verifyPassword($data['current_password'], $user->password_hash)) {
        jsonResponse(['error' => 'Current password is incorrect'], 400);
    }

    $newPasswordHash = hashPassword($data['new_password']);
    
    if ($user->updatePassword($newPasswordHash)) {
        jsonResponse([
            'message' => 'Password updated successfully'
        ]);
    } else {
        jsonResponse(['error' => 'Failed to update password'], 500);
    }
}

function deleteAccount($db, $data) {
    $userId = requireAuth();
    
    $errors = validateUserInput($data, ['password']);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    $user = new User($db);
    
    if (!$user->findById($userId)) {
        jsonResponse(['error' => 'User not found'], 404);
    }

    // Verify password
    if (!verifyPassword($data['password'], $user->password_hash)) {
        jsonResponse(['error' => 'Password is incorrect'], 400);
    }

    if ($user->delete()) {
        logout();
        jsonResponse([
            'message' => 'Account deleted successfully'
        ]);
    } else {
        jsonResponse(['error' => 'Failed to delete account'], 500);
    }
}
?>