<?php
// Define the base path for includes
$basePath = dirname(dirname(__FILE__)); // Go up two levels to get to backend/

// Include required files using absolute paths
require_once $basePath . '/config/config.php';
require_once $basePath . '/includes/functions.php';
require_once $basePath . '/includes/auth.php';
require_once $basePath . '/models/Reminder.php';

// Require authentication for all reminder operations
$userId = requireAuth();

$database = new Database();
$db = $database->getConnection();

// Set content type header for API responses
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    // Create new reminder
    createReminder($db, $userId, $data);
} elseif ($method === 'GET') {
    // Get reminders
    if (isset($_GET['id'])) {
        getReminderById($db, $userId, $_GET['id']);
    } elseif (isset($_GET['today'])) {
        getTodaysReminders($db, $userId);
    } elseif (isset($_GET['upcoming'])) {
        getUpcomingReminders($db, $userId);
    } else {
        getReminders($db, $userId);
    }
} elseif ($method === 'PUT') {
    // Update reminder
    updateReminder($db, $userId, $data);
} elseif ($method === 'DELETE') {
    // Delete reminder
    if (isset($data['id'])) {
        deleteReminder($db, $userId, $data['id']);
    } else {
        jsonResponse(['error' => 'Reminder ID is required'], 400);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function createReminder($db, $userId, $data) {
    $required = ['title', 'reminder_time'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    // Validate time format
    $time = DateTime::createFromFormat('H:i', $data['reminder_time']);
    if (!$time || $time->format('H:i') !== $data['reminder_time']) {
        jsonResponse(['error' => 'Invalid time format. Use HH:MM (24-hour format)'], 400);
    }

    $validTypes = ['daily', 'weekly', 'custom'];
    $reminderType = isset($data['reminder_type']) ? $data['reminder_type'] : 'daily';
    
    if (!in_array($reminderType, $validTypes)) {
        jsonResponse(['error' => 'Invalid reminder type. Must be daily, weekly, or custom'], 400);
    }

    $reminder = new Reminder($db);
    $reminder->user_id = $userId;
    $reminder->title = sanitizeInput($data['title']);
    $reminder->description = isset($data['description']) ? sanitizeInput($data['description']) : null;
    $reminder->reminder_type = $reminderType;
    $reminder->reminder_time = $data['reminder_time'];

    $id = $reminder->create();
    if ($id) {
        jsonResponse([
            'message' => 'Reminder created successfully',
            'id' => $id,
            'reminder' => [
                'id' => $id,
                'user_id' => $userId,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'reminder_type' => $reminder->reminder_type,
                'reminder_time' => $reminder->reminder_time,
                'is_active' => true,
                'created_at' => getCurrentTimestamp()
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to create reminder'], 500);
    }
}

function getReminders($db, $userId) {
    $reminder = new Reminder($db);
    $reminders = $reminder->getByUser($userId);
    
    jsonResponse([
        'reminders' => $reminders,
        'count' => count($reminders)
    ]);
}

function getReminderById($db, $userId, $id) {
    $reminder = new Reminder($db);
    if ($reminder->getById($id)) {
        // Verify that the reminder belongs to the current user
        if ($reminder->user_id != $userId) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        jsonResponse([
            'reminder' => [
                'id' => $reminder->id,
                'user_id' => $reminder->user_id,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'reminder_type' => $reminder->reminder_type,
                'reminder_time' => $reminder->reminder_time,
                'is_active' => $reminder->is_active,
                'created_at' => $reminder->created_at
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Reminder not found'], 404);
    }
}

function getTodaysReminders($db, $userId) {
    $reminder = new Reminder($db);
    $reminders = $reminder->getTodaysReminders($userId);
    
    jsonResponse([
        'reminders' => $reminders,
        'count' => count($reminders),
        'date' => getCurrentDate()
    ]);
}

function getUpcomingReminders($db, $userId) {
    $reminder = new Reminder($db);
    $reminders = $reminder->getUpcomingReminders($userId);
    
    jsonResponse([
        'reminders' => $reminders,
        'count' => count($reminders)
    ]);
}

function updateReminder($db, $userId, $data) {
    $required = ['id', 'title', 'reminder_time'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    // Validate time format
    $time = DateTime::createFromFormat('H:i', $data['reminder_time']);
    if (!$time || $time->format('H:i') !== $data['reminder_time']) {
        jsonResponse(['error' => 'Invalid time format. Use HH:MM (24-hour format)'], 400);
    }

    $validTypes = ['daily', 'weekly', 'custom'];
    $reminderType = isset($data['reminder_type']) ? $data['reminder_type'] : 'daily';
    
    if (!in_array($reminderType, $validTypes)) {
        jsonResponse(['error' => 'Invalid reminder type. Must be daily, weekly, or custom'], 400);
    }

    $reminder = new Reminder($db);
    if (!$reminder->getById($data['id'])) {
        jsonResponse(['error' => 'Reminder not found'], 404);
    }

    // Verify that the reminder belongs to the current user
    if ($reminder->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    $reminder->title = sanitizeInput($data['title']);
    $reminder->description = isset($data['description']) ? sanitizeInput($data['description']) : $reminder->description;
    $reminder->reminder_type = $reminderType;
    $reminder->reminder_time = $data['reminder_time'];

    if ($reminder->update()) {
        jsonResponse([
            'message' => 'Reminder updated successfully',
            'reminder' => [
                'id' => $reminder->id,
                'user_id' => $reminder->user_id,
                'title' => $reminder->title,
                'description' => $reminder->description,
                'reminder_type' => $reminder->reminder_type,
                'reminder_time' => $reminder->reminder_time,
                'is_active' => $reminder->is_active,
                'created_at' => $reminder->created_at
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to update reminder'], 500);
    }
}

function deleteReminder($db, $userId, $id) {
    $reminder = new Reminder($db);
    if (!$reminder->getById($id)) {
        jsonResponse(['error' => 'Reminder not found'], 404);
    }

    // Verify that the reminder belongs to the current user
    if ($reminder->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    if ($reminder->delete()) {
        jsonResponse([
            'message' => 'Reminder deactivated successfully'
        ]);
    } else {
        jsonResponse(['error' => 'Failed to deactivate reminder'], 500);
    }
}
?>