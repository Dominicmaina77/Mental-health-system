<?php
require_once '../config/database.php';

class User {
    private $conn;
    private $table = 'users';

    // User properties
    public $id;
    public $name;
    public $email;
    public $password_hash;
    public $age_group;
    public $created_at;
    public $updated_at;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new user
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET name=:name, email=:email, password_hash=:password_hash, age_group=:age_group";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password_hash = htmlspecialchars(strip_tags($this->password_hash));
        $this->age_group = htmlspecialchars(strip_tags($this->age_group));

        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $this->password_hash);
        $stmt->bindParam(':age_group', $this->age_group);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->age_group = $row['age_group'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    /**
     * Find user by ID
     */
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->age_group = $row['age_group'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    /**
     * Update user profile
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name=:name, age_group=:age_group, updated_at=:updated_at 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->age_group = htmlspecialchars(strip_tags($this->age_group));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_at = getCurrentTimestamp();

        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':age_group', $this->age_group);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Update user password
     */
    public function updatePassword($newPasswordHash) {
        $query = "UPDATE " . $this->table . " 
                  SET password_hash=:password_hash, updated_at=:updated_at 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_at = getCurrentTimestamp();

        $stmt->bindParam(':password_hash', $newPasswordHash);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Delete user (soft delete by deactivating)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " 
                  SET is_active=0, updated_at=:updated_at 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->updated_at = getCurrentTimestamp();

        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Get user statistics
     */
    public function getUserStats($userId) {
        $stats = [];
        
        // Count mood entries
        $moodQuery = "SELECT COUNT(*) as count FROM mood_entries WHERE user_id = :user_id";
        $moodStmt = $this->conn->prepare($moodQuery);
        $moodStmt->bindParam(':user_id', $userId);
        $moodStmt->execute();
        $stats['mood_entries_count'] = $moodStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count journal entries
        $journalQuery = "SELECT COUNT(*) as count FROM journal_entries WHERE user_id = :user_id";
        $journalStmt = $this->conn->prepare($journalQuery);
        $journalStmt->bindParam(':user_id', $userId);
        $journalStmt->execute();
        $stats['journal_entries_count'] = $journalStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Count active reminders
        $reminderQuery = "SELECT COUNT(*) as count FROM reminders WHERE user_id = :user_id AND is_active = 1";
        $reminderStmt = $this->conn->prepare($reminderQuery);
        $reminderStmt->bindParam(':user_id', $userId);
        $reminderStmt->execute();
        $stats['active_reminders_count'] = $reminderStmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    }
}
?>