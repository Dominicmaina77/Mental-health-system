<?php
// Use absolute path to ensure proper inclusion
$rootDir = dirname(dirname(__FILE__)); // Go up two levels to get to backend/
$dbPath = $rootDir . '/config/database.php';
$functionsPath = $rootDir . '/includes/functions.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    // Fallback: try to include from parent directory
    require_once '../config/database.php';
}

if (file_exists($functionsPath)) {
    require_once $functionsPath;
} else {
    // Fallback: try to include from parent directory
    require_once 'functions.php';
}

class Reminder {
    private $conn;
    private $table = 'reminders';

    // Reminder properties
    public $id;
    public $user_id;
    public $title;
    public $description;
    public $reminder_type;
    public $reminder_time;
    public $is_active;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new reminder
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, title=:title, description=:description, 
                      reminder_type=:reminder_type, reminder_time=:reminder_time";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->reminder_type = htmlspecialchars(strip_tags($this->reminder_type));
        $this->reminder_time = htmlspecialchars(strip_tags($this->reminder_time));

        // Bind values
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':reminder_type', $this->reminder_type);
        $stmt->bindParam(':reminder_time', $this->reminder_time);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Get reminder by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->reminder_type = $row['reminder_type'];
            $this->reminder_time = $row['reminder_time'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    /**
     * Get active reminders by user ID
     */
    public function getByUser($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_active = 1
                  ORDER BY reminder_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all reminders by user ID (including inactive)
     */
    public function getAllByUser($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update reminder
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title=:title, description=:description, 
                      reminder_type=:reminder_type, reminder_time=:reminder_time
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->reminder_type = htmlspecialchars(strip_tags($this->reminder_type));
        $this->reminder_time = htmlspecialchars(strip_tags($this->reminder_time));

        // Bind values
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':reminder_type', $this->reminder_type);
        $stmt->bindParam(':reminder_time', $this->reminder_time);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Activate or deactivate reminder
     */
    public function toggleActive($active) {
        $query = "UPDATE " . $this->table . " 
                  SET is_active=:is_active
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $is_active = $active ? 1 : 0;

        // Bind values
        $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Delete reminder (soft delete by deactivating)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET is_active=0
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Bind values
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get reminders due today
     */
    public function getTodaysReminders($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND is_active = 1
                  AND reminder_time <= :current_time
                  ORDER BY reminder_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':current_time', date('H:i:s'));
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get upcoming reminders (for the next 24 hours)
     */
    public function getUpcomingReminders($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND is_active = 1
                  AND reminder_time BETWEEN :start_time AND :end_time
                  ORDER BY reminder_time ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_time', date('H:i:s'));
        $stmt->bindParam(':end_time', date('H:i:s', strtotime('+24 hours')));
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>