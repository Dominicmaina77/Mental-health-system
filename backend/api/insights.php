<?php
// Define the base path for includes
$basePath = dirname(dirname(__FILE__)); // Go up two levels to get to backend/

// Include required files using absolute paths
require_once $basePath . '/config/config.php';
require_once $basePath . '/includes/functions.php';
require_once $basePath . '/includes/auth.php';
require_once $basePath . '/models/MoodEntry.php';
require_once $basePath . '/models/JournalEntry.php';

header('Content-Type: application/json');

// Require authentication for all insights operations
$userId = requireAuth();

$database = new Database();
$db = $database->getConnection();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get insights
    getInsights($db, $userId);
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function getInsights($db, $userId) {
    $moodEntry = new MoodEntry($db);
    $journalEntry = new JournalEntry($db);

    // Get mood data for insights
    $moodData = $moodEntry->getByUser($userId, 30);
    
    // Calculate average mood for different periods
    $avgMood7Days = $moodEntry->getAverageMood($userId, 7);
    $avgMood30Days = $moodEntry->getAverageMood($userId, 30);
    $avgMoodAllTime = $moodEntry->getAverageMood($userId, 365); // Approximate all-time
    
    // Get mood distribution
    $moodDistribution = $moodEntry->getMoodDistribution($userId, 30);
    
    // Get mood streak
    $moodStreak = $moodEntry->getMoodStreak($userId);
    
    // Get journal stats
    $journalCount = $journalEntry->getCountByUser($userId);
    $recentJournalEntries = $journalEntry->getRecentByUser($userId, 5);
    
    // Get mood trend (last 7 days)
    $trendQuery = "SELECT date_recorded, mood_value FROM mood_entries 
                   WHERE user_id = :user_id 
                   AND date_recorded >= DATE_SUB(:current_date, INTERVAL 7 DAY)
                   ORDER BY date_recorded ASC";
    $trendStmt = $db->prepare($trendQuery);
    $trendStmt->bindParam(':user_id', $userId);
    $trendStmt->bindParam(':current_date', getCurrentDate());
    $trendStmt->execute();
    $moodTrend = $trendStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate mood trend direction
    $trendDirection = 'neutral';
    if (count($moodTrend) >= 2) {
        $firstMood = $moodTrend[0]['mood_value'];
        $lastMood = $moodTrend[count($moodTrend) - 1]['mood_value'];
        
        if ($lastMood > $firstMood) {
            $trendDirection = 'improving';
        } elseif ($lastMood < $firstMood) {
            $trendDirection = 'declining';
        }
    }
    
    // Get mood consistency (how many days in the last 7 had entries)
    $consistencyQuery = "SELECT COUNT(DISTINCT date_recorded) as days_with_entries 
                         FROM mood_entries 
                         WHERE user_id = :user_id 
                         AND date_recorded >= DATE_SUB(:current_date, INTERVAL 7 DAY)";
    $consistencyStmt = $db->prepare($consistencyQuery);
    $consistencyStmt->bindParam(':user_id', $userId);
    $consistencyStmt->bindParam(':current_date', getCurrentDate());
    $consistencyStmt->execute();
    $daysWithEntries = $consistencyStmt->fetch(PDO::FETCH_ASSOC)['days_with_entries'];
    $consistencyPercentage = round(($daysWithEntries / 7) * 100);
    
    // Get most common mood
    $commonMoodQuery = "SELECT mood_value, COUNT(*) as count FROM mood_entries 
                        WHERE user_id = :user_id 
                        AND date_recorded >= DATE_SUB(:current_date, INTERVAL 30 DAY)
                        GROUP BY mood_value 
                        ORDER BY count DESC 
                        LIMIT 1";
    $commonMoodStmt = $db->prepare($commonMoodQuery);
    $commonMoodStmt->bindParam(':user_id', $userId);
    $commonMoodStmt->bindParam(':current_date', getCurrentDate());
    $commonMoodStmt->execute();
    $commonMood = $commonMoodStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get mood by time of day (if we had timestamps for mood entries)
    // For now, we'll just return the overall stats
    
    jsonResponse([
        'insights' => [
            'average_mood' => [
                '7_days' => $avgMood7Days,
                '30_days' => $avgMood30Days,
                'all_time' => $avgMoodAllTime
            ],
            'mood_distribution' => $moodDistribution,
            'mood_streak' => $moodStreak,
            'mood_trend' => [
                'direction' => $trendDirection,
                'data' => $moodTrend
            ],
            'consistency' => [
                'percentage' => $consistencyPercentage,
                'days_with_entries' => $daysWithEntries
            ],
            'journal_stats' => [
                'total_entries' => $journalCount,
                'recent_entries' => $recentJournalEntries
            ],
            'most_common_mood' => $commonMood ? $commonMood['mood_value'] : null,
            'total_mood_entries' => count($moodData)
        ]
    ]);
}

// Additional function to get mood insights by tag (if we implement mood tags later)
function getMoodByTag($db, $userId) {
    // This would require the mood tags tables to be implemented
    // For now, this is a placeholder for future enhancement
    return [];
}
?>