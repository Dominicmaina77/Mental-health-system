<?php
// Define the base path for includes
$basePath = dirname(dirname(__FILE__)); // Go up two levels to get to backend/

// Include required files using absolute paths
require_once $basePath . '/config/config.php';
require_once $basePath . '/includes/functions.php';
require_once $basePath . '/includes/auth.php';
require_once $basePath . '/models/User.php';

// Require authentication for all broadcast operations
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
        case 'send_broadcast':
            sendBroadcast($db, $userId);
            break;
        case 'get_broadcasts':
            getBroadcasts($db);
            break;
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} else {
    jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
}

function sendBroadcast($db, $currentUserId) {
    $message = trim($_POST['message'] ?? '');

    if (!$message) {
        jsonResponse(['success' => false, 'message' => 'Message is required'], 400);
    }

    try {
        // Insert broadcast message into database
        $stmt = $db->prepare("INSERT INTO broadcasts (message, sender_id, created_at) VALUES (?, ?, NOW())");
        if ($stmt->execute([$message, $currentUserId])) {
            $broadcastId = $db->lastInsertId();

            // In a real application, you would send this message to all users
            // For now, we'll just record it in the database

            jsonResponse([
                'success' => true,
                'message' => 'Broadcast sent successfully',
                'broadcast_id' => $broadcastId
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to send broadcast'], 500);
        }
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error sending broadcast: ' . $e->getMessage()], 500);
    }
}

function getBroadcasts($db) {
    $limit = $_POST['limit'] ?? 10;
    $offset = $_POST['offset'] ?? 0;

    try {
        $stmt = $db->prepare("SELECT b.*, u.name as sender_name FROM broadcasts b LEFT JOIN users u ON b.sender_id = u.id ORDER BY b.created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        $broadcasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        jsonResponse([
            'success' => true,
            'broadcasts' => $broadcasts,
            'count' => count($broadcasts)
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error fetching broadcasts: ' . $e->getMessage()], 500);
    }
}
?>