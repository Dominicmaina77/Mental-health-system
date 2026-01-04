<?php
require_once '../config/database.php';
require_once 'functions.php'; // For getCurrentTimestamp()

class JournalEntry {
    private $conn;
    private $table = 'journal_entries';

    // Journal entry properties
    public $id;
    public $user_id;
    public $title;
    public $content;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new journal entry
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, title=:title, content=:content";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = $this->content; // Don't sanitize content to preserve formatting

        // Bind values
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Get journal entry by ID
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
            $this->content = $row['content'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Get journal entries by user ID
     */
    public function getByUser($user_id, $limit = 20, $offset = 0) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get journal entries by user with search
     */
    public function getByUserWithSearch($user_id, $search = '', $limit = 20, $offset = 0) {
        if (!empty($search)) {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE user_id = :user_id 
                      AND (title LIKE :search OR content LIKE :search)
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindValue(':search', '%' . $search . '%');
        } else {
            $query = "SELECT * FROM " . $this->table . " 
                      WHERE user_id = :user_id 
                      ORDER BY created_at DESC 
                      LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update journal entry
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET title=:title, content=:content, updated_at=:updated_at 
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = $this->content; // Don't sanitize content to preserve formatting
        $this->updated_at = getCurrentTimestamp();

        // Bind values
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':updated_at', $this->updated_at);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Delete journal entry
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
     * Get count of journal entries by user
     */
    public function getCountByUser($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Get journal entries for the last 30 days by user
     */
    public function getRecentByUser($user_id, $days = 30) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  AND created_at >= DATE_SUB(:current_date, INTERVAL :days DAY)
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':current_date', getCurrentTimestamp());
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>