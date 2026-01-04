<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../models/JournalEntry.php';

header('Content-Type: application/json');

// Require authentication for all journal operations
$userId = requireAuth();

$database = new Database();
$db = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    // Create new journal entry
    createJournalEntry($db, $userId, $data);
} elseif ($method === 'GET') {
    // Get journal entries
    if (isset($_GET['id'])) {
        getJournalEntryById($db, $userId, $_GET['id']);
    } elseif (isset($_GET['search'])) {
        getJournalEntriesWithSearch($db, $userId, $_GET['search']);
    } elseif (isset($_GET['limit'])) {
        getJournalEntriesWithLimit($db, $userId, $_GET['limit']);
    } else {
        getJournalEntries($db, $userId);
    }
} elseif ($method === 'PUT') {
    // Update journal entry
    updateJournalEntry($db, $userId, $data);
} elseif ($method === 'DELETE') {
    // Delete journal entry
    if (isset($data['id'])) {
        deleteJournalEntry($db, $userId, $data['id']);
    } else {
        jsonResponse(['error' => 'Journal entry ID is required'], 400);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function createJournalEntry($db, $userId, $data) {
    $required = ['content'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    $journalEntry = new JournalEntry($db);
    $journalEntry->user_id = $userId;
    $journalEntry->title = isset($data['title']) ? sanitizeInput($data['title']) : null;
    $journalEntry->content = $data['content']; // Don't sanitize to preserve formatting

    $id = $journalEntry->create();
    if ($id) {
        jsonResponse([
            'message' => 'Journal entry created successfully',
            'id' => $id,
            'journal_entry' => [
                'id' => $id,
                'user_id' => $userId,
                'title' => $journalEntry->title,
                'content' => $journalEntry->content,
                'created_at' => getCurrentTimestamp(),
                'updated_at' => getCurrentTimestamp()
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to create journal entry'], 500);
    }
}

function getJournalEntries($db, $userId) {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20; // Max 100 per page
    $offset = ($page - 1) * $limit;

    $journalEntry = new JournalEntry($db);
    $entries = $journalEntry->getByUser($userId, $limit, $offset);
    $total = $journalEntry->getCountByUser($userId);
    $totalPages = ceil($total / $limit);

    jsonResponse([
        'journal_entries' => $entries,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $total,
            'total_pages' => $totalPages
        ]
    ]);
}

function getJournalEntriesWithLimit($db, $userId, $limit) {
    $limit = min((int)$limit, 100); // Max 100 per request
    $journalEntry = new JournalEntry($db);
    $entries = $journalEntry->getByUser($userId, $limit, 0);

    jsonResponse([
        'journal_entries' => $entries,
        'count' => count($entries)
    ]);
}

function getJournalEntriesWithSearch($db, $userId, $search) {
    $search = sanitizeInput($search);
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20; // Max 100 per page
    $offset = ($page - 1) * $limit;

    $journalEntry = new JournalEntry($db);
    $entries = $journalEntry->getByUserWithSearch($userId, $search, $limit, $offset);

    jsonResponse([
        'journal_entries' => $entries,
        'search_query' => $search,
        'count' => count($entries)
    ]);
}

function getJournalEntryById($db, $userId, $id) {
    $journalEntry = new JournalEntry($db);
    if ($journalEntry->getById($id)) {
        // Verify that the entry belongs to the current user
        if ($journalEntry->user_id != $userId) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        jsonResponse([
            'journal_entry' => [
                'id' => $journalEntry->id,
                'user_id' => $journalEntry->user_id,
                'title' => $journalEntry->title,
                'content' => $journalEntry->content,
                'created_at' => $journalEntry->created_at,
                'updated_at' => $journalEntry->updated_at
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Journal entry not found'], 404);
    }
}

function updateJournalEntry($db, $userId, $data) {
    $required = ['id', 'content'];
    $errors = validateUserInput($data, $required);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
    }

    $journalEntry = new JournalEntry($db);
    if (!$journalEntry->getById($data['id'])) {
        jsonResponse(['error' => 'Journal entry not found'], 404);
    }

    // Verify that the entry belongs to the current user
    if ($journalEntry->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    $journalEntry->title = isset($data['title']) ? sanitizeInput($data['title']) : $journalEntry->title;
    $journalEntry->content = $data['content']; // Don't sanitize to preserve formatting

    if ($journalEntry->update()) {
        jsonResponse([
            'message' => 'Journal entry updated successfully',
            'journal_entry' => [
                'id' => $journalEntry->id,
                'user_id' => $journalEntry->user_id,
                'title' => $journalEntry->title,
                'content' => $journalEntry->content,
                'created_at' => $journalEntry->created_at,
                'updated_at' => getCurrentTimestamp()
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Failed to update journal entry'], 500);
    }
}

function deleteJournalEntry($db, $userId, $id) {
    $journalEntry = new JournalEntry($db);
    if (!$journalEntry->getById($id)) {
        jsonResponse(['error' => 'Journal entry not found'], 404);
    }

    // Verify that the entry belongs to the current user
    if ($journalEntry->user_id != $userId) {
        jsonResponse(['error' => 'Unauthorized'], 401);
    }

    if ($journalEntry->delete()) {
        jsonResponse([
            'message' => 'Journal entry deleted successfully'
        ]);
    } else {
        jsonResponse(['error' => 'Failed to delete journal entry'], 500);
    }
}
?>