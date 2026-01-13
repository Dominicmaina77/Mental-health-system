<?php
// Define the base path for includes
$basePath = dirname(dirname(__FILE__)); // Go up two levels to get to backend/

// Include required files using absolute paths
require_once $basePath . '/config/config.php';
require_once $basePath . '/includes/functions.php';
require_once $basePath . '/includes/auth.php';
require_once $basePath . '/models/MoodEntry.php';

// Require authentication for all mood operations
$userId = requireAuth();

// Set content type header for API responses
if (!headers_sent()) {
    header('Content-Type: application/json');
}

$database = new Database();
$db = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    // Create new mood entry
    createMoodEntry($db, $userId, $data);
} elseif ($method === 'GET') {
    // Get mood entries
    if (isset($_GET['date'])) {
        getMoodEntryByDate($db, $userId, $_GET['date']);
    } elseif (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        getMoodEntriesByDateRange($db, $userId, $_GET['start_date'], $_GET['end_date']);
    } elseif (isset($_GET['streak'])) {
        getMoodStreak($db, $userId);
    } else {
        getRecentMoodEntries($db, $userId);
    }
} elseif ($method === 'PUT') {
    // Update mood entry
    updateMoodEntry($db, $userId, $data);
} elseif ($method === 'DELETE') {
    // Delete mood entry
    if (isset($data['id'])) {
        deleteMoodEntry($db, $userId, $data['id']);
    } else {
        jsonResponse(['error' => 'Mood entry ID is required'], 400);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function createMoodEntry($db, $userId, $data) {
    $required = ['mood_value', 'date_recorded'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    if (!validateMoodValue($data['mood_value'])) {
        jsonResponse(['error' => 'Mood value must be between 1 and 10'], 400);
    }

    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $data['date_recorded']);
    if (!$date || $date->format('Y-m-d') !== $data['date_recorded']) {
        jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
    }

    $moodEntry = new MoodEntry($db);
    $moodEntry->user_id = $userId;
    $moodEntry->mood_value = (int)$data['mood_value'];
    $moodEntry->mood_note = isset($data['mood_note']) ? sanitizeInput($data['mood_note']) : null;
    $moodEntry->date_recorded = $data['date_recorded'];

    $id = $moodEntry->create();
    if ($id) {
        jsonResponse([
            'message' => 'Mood entry created successfully',
            'id' => $id,
            'mood_entry' => [
                'id' => $id,
                'user_id' => $userId,
                'mood_value' => $moodEntry->mood_value,
                'mood_note' => $moodEntry->mood_note,
                'date_recorded' => $moodEntry->date_recorded,
                'created_at' => getCurrentTimestamp()
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to create mood entry'], 500);
    }
}

function getRecentMoodEntries($db, $userId) {
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 30;
    $moodEntry = new MoodEntry($db);
    $entries = $moodEntry->getByUser($userId, $limit);
    
    jsonResponse([
        'mood_entries' => $entries,
        'count' => count($entries)
    ]);
}

function getMoodEntryByDate($db, $userId, $date) {
    // Validate date format
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        jsonResponse(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
    }

    $moodEntry = new MoodEntry($db);
    if ($moodEntry->getByUserAndDate($userId, $date)) {
        jsonResponse([
            'mood_entry' => [
                'id' => $moodEntry->id,
                'user_id' => $moodEntry->user_id,
                'mood_value' => $moodEntry->mood_value,
                'mood_note' => $moodEntry->mood_note,
                'date_recorded' => $moodEntry->date_recorded,
                'created_at' => $moodEntry->created_at
            ]
        ]);
    } else {
        jsonResponse(['mood_entry' => null], 200);
    }
}

function getMoodEntriesByDateRange($db, $userId, $start_date, $end_date) {
    // Validate date formats
    $startDateObj = DateTime::createFromFormat('Y-m-d', $start_date);
    $endDateObj = DateTime::createFromFormat('Y-m-d', $end_date);
    
    if (!$startDateObj || $startDateObj->format('Y-m-d') !== $start_date) {
        jsonResponse(['error' => 'Invalid start date format. Use YYYY-MM-DD'], 400);
    }
    
    if (!$endDateObj || $endDateObj->format('Y-m-d') !== $end_date) {
        jsonResponse(['error' => 'Invalid end date format. Use YYYY-MM-DD'], 400);
    }
    
    if ($startDateObj > $endDateObj) {
        jsonResponse(['error' => 'Start date must be before end date'], 400);
    }

    $moodEntry = new MoodEntry($db);
    $entries = $moodEntry->getByUserAndDateRange($userId, $start_date, $end_date);
    
    jsonResponse([
        'mood_entries' => $entries,
        'count' => count($entries),
        'date_range' => [
            'start_date' => $start_date,
            'end_date' => $end_date
        ]
    ]);
}

function updateMoodEntry($db, $userId, $data) {
    $required = ['id', 'mood_value'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    if (!validateMoodValue($data['mood_value'])) {
        jsonResponse(['error' => 'Mood value must be between 1 and 10'], 400);
    }

    $moodEntry = new MoodEntry($db);
    if (!$moodEntry->getById($data['id'])) {
        jsonResponse(['error' => 'Mood entry not found'], 404);
    }

    // Verify that the entry belongs to the current user
    if ($moodEntry->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    $moodEntry->mood_value = (int)$data['mood_value'];
    $moodEntry->mood_note = isset($data['mood_note']) ? sanitizeInput($data['mood_note']) : $moodEntry->mood_note;

    if ($moodEntry->update()) {
        jsonResponse([
            'message' => 'Mood entry updated successfully',
            'mood_entry' => [
                'id' => $moodEntry->id,
                'user_id' => $moodEntry->user_id,
                'mood_value' => $moodEntry->mood_value,
                'mood_note' => $moodEntry->mood_note,
                'date_recorded' => $moodEntry->date_recorded,
                'created_at' => $moodEntry->created_at
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to update mood entry'], 500);
    }
}

function deleteMoodEntry($db, $userId, $id) {
    $moodEntry = new MoodEntry($db);
    if (!$moodEntry->getById($id)) {
        jsonResponse(['error' => 'Mood entry not found'], 404);
    }

    // Verify that the entry belongs to the current user
    if ($moodEntry->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    if ($moodEntry->delete()) {
        jsonResponse([
            'message' => 'Mood entry deleted successfully'
        ]);
    } else {
        jsonResponse(['error' => 'Failed to delete mood entry'], 500);
    }
}

function getMoodStreak($db, $userId) {
    $moodEntry = new MoodEntry($db);
    $streak = $moodEntry->getMoodStreak($userId);
    
    jsonResponse([
        'streak' => $streak
    ]);
}
?>