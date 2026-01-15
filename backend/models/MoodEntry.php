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

class MoodEntry {
    private $conn;
    private $table = 'mood_entries';

    // Mood entry properties
    public $id;
    public $user_id;
    public $mood_value;
    public $mood_note;
    public $date_recorded;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new mood entry
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, mood_value=:mood_value, mood_note=:mood_note, date_recorded=:date_recorded";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mood_value = htmlspecialchars(strip_tags($this->mood_value));
        $this->mood_note = htmlspecialchars(strip_tags($this->mood_note));
        $this->date_recorded = htmlspecialchars(strip_tags($this->date_recorded));

        // Bind values
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':mood_value', $this->mood_value);
        $stmt->bindParam(':mood_note', $this->mood_note);
        $stmt->bindParam(':date_recorded', $this->date_recorded);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Get mood entry by ID
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
            $this->mood_value = $row['mood_value'];
            $this->mood_note = $row['mood_note'];
            $this->date_recorded = $row['date_recorded'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    /**
     * Get mood entries by user ID
     */
    public function getByUser($user_id, $limit = 30) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY date_recorded DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get mood entries by user and date range
     */
    public function getByUserAndDateRange($user_id, $start_date, $end_date) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND date_recorded BETWEEN :start_date AND :end_date
                  ORDER BY date_recorded ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get mood entry by user and specific date
     */
    public function getByUserAndDate($user_id, $date) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND date_recorded = :date 
                  LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->mood_value = $row['mood_value'];
            $this->mood_note = $row['mood_note'];
            $this->date_recorded = $row['date_recorded'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    /**
     * Update mood entry
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET mood_value=:mood_value, mood_note=:mood_note 
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->mood_value = htmlspecialchars(strip_tags($this->mood_value));
        $this->mood_note = htmlspecialchars(strip_tags($this->mood_note));

        // Bind values
        $stmt->bindParam(':mood_value', $this->mood_value);
        $stmt->bindParam(':mood_note', $this->mood_note);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Delete mood entry
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id AND user_id=:user_id";
        
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
     * Get average mood for a user
     */
    public function getAverageMood($user_id, $days = 30) {
        $query = "SELECT AVG(mood_value) as avg_mood FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND date_recorded >= DATE_SUB(:current_date, INTERVAL :days DAY)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $currentDate = getCurrentDate();
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['avg_mood'] ? round($result['avg_mood'], 2) : 0;
    }

    /**
     * Get mood distribution for a user
     */
    public function getMoodDistribution($user_id, $days = 30) {
        $query = "SELECT mood_value, COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND date_recorded >= DATE_SUB(:current_date, INTERVAL :days DAY)
                  GROUP BY mood_value 
                  ORDER BY mood_value";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $currentDate = getCurrentDate();
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get mood streak for a user (consecutive days with mood entries)
     */
    public function getMoodStreak($user_id) {
        $query = "SELECT date_recorded FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND date_recorded >= DATE_SUB(:current_date, INTERVAL 30 DAY)
                  ORDER BY date_recorded DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $currentDate = getCurrentDate();
        $stmt->bindParam(':current_date', $currentDate);
        $stmt->execute();

        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($dates)) {
            return 0;
        }
        
        // Calculate consecutive days
        $streak = 0;
        $current_date = new DateTime();
        $current_date->setTime(0, 0, 0);
        
        foreach ($dates as $date) {
            $entry_date = new DateTime($date);
            $entry_date->setTime(0, 0, 0);
            
            $diff = $current_date->diff($entry_date)->days;
            
            if ($diff == $streak) {
                $streak++;
                $current_date = $entry_date;
            } else {
                break;
            }
        }
        
        return $streak;
    }
}
?>