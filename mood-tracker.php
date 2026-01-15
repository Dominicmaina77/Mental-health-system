<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header('Location: login.php');
    exit();
}

// Get user information from session
$user_name = $_SESSION['user_name'] ?? 'User';

// Include necessary files
require_once 'backend/config/config.php';
require_once 'backend/includes/functions.php';
require_once 'backend/models/MoodEntry.php';

$database = new Database();
$db = $database->getConnection();
$moodEntry = new MoodEntry($db);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood_value = intval($_POST['mood_value'] ?? 0);
    $mood_note = trim($_POST['mood_note'] ?? '');
    $date_recorded = $_POST['date_recorded'] ?? date('Y-m-d');
    
    // Validate mood value
    if ($mood_value < 1 || $mood_value > 10) {
        $error = 'Please select a valid mood value (1-10).';
    } else {
        // Create mood entry
        $moodEntry->user_id = $_SESSION['user_id'];
        $moodEntry->mood_value = $mood_value;
        $moodEntry->mood_note = $mood_note;
        $moodEntry->date_recorded = $date_recorded;
        
        if ($moodEntry->create()) {
            $success = 'Mood entry saved successfully!';
        } else {
            $error = 'Failed to save mood entry.';
        }
    }
}

// Get recent mood entries for the user
$recentMoods = $moodEntry->getByUser($_SESSION['user_id'], 10);

// Get mood streak
$moodStreak = $moodEntry->getMoodStreak($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mood Tracker - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Mood Tracker specific styles */
        .page-header {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .page-header h1 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }

        .mood-tracker {
            padding: 4rem 0;
            background-color: var(--White);
        }

        .tracker-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .mood-selector {
            background-color: var(--light-blue);
            padding: 2.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px var(--shadow);
        }

        .mood-emojis {
            display: flex;
            justify-content: space-between;
            margin: 2rem 0;
        }

        .mood-emoji {
            font-size: 3rem;
            cursor: pointer;
            padding: 1rem;
            border-radius: 50%;
            transition: var(--transition);
            background-color: var(--White);
        }

        .mood-emoji:hover {
            transform: scale(1.2);
            background-color: var(--pastel-blue);
        }

        .mood-emoji.active {
            transform: scale(1.3);
            background-color: var(--medium-blue);
            color: white;
            box-shadow: 0 5px 15px rgba(122, 184, 217, 0.3);
        }

        .mood-slider-container {
            margin: 2rem 0;
        }

        .mood-slider {
            width: 100%;
            height: 20px;
            -webkit-appearance: none;
            appearance: none;
            background: linear-gradient(90deg, #ff7675, #fdcb6e, #00b894);
            border-radius: 10px;
            outline: none;
        }

        .mood-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 30px;
            height: 30px;
            background-color: var(--White);
            border: 3px solid var(--medium-blue);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .tags-section {
            margin: 2rem 0;
        }

        .tags-title {
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .tag-checkbox {
            display: none;
        }

        .tag-label {
            padding: 0.5rem 1rem;
            background-color: var(--pastel-blue);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .tag-checkbox:checked + .tag-label {
            background-color: var(--medium-blue);
            color: white;
        }

        .note-section {
            margin: 2rem 0;
        }

        .note-textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--pastel-blue);
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 1rem;
            min-height: 120px;
            resize: vertical;
            background-color: var(--light-blue);
        }

        .note-textarea:focus {
            outline: none;
            border-color: var(--medium-blue);
            box-shadow: 0 0 0 3px rgba(122, 184, 217, 0.2);
        }

        .save-button {
            width: 100%;
            padding: 1rem;
            background-color: var(--medium-blue);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .save-button:hover {
            background-color: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(122, 184, 217, 0.3);
        }

        .recent-moods {
            margin-top: 4rem;
        }

        .mood-history {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .mood-day {
            background-color: var(--light-blue);
            padding: 1.5rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 15px var(--shadow);
        }

        .mood-day-emoji {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .mood-day-date {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .mood-day-value {
            font-weight: 600;
            color: var(--dark-blue);
        }

        .streak-section {
            text-align: center;
            margin-top: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, var(--accent-pink) 0%, var(--accent-lavender) 100%);
            border-radius: var(--radius);
            color: white;
        }

        .streak-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }

        .streak-label {
            font-size: 1.1rem;
        }

        .form-message {
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 500;
        }

        .form-message.success {
            background-color: rgba(122, 184, 217, 0.1);
            color: var(--dark-blue);
            border: 1px solid var(--medium-blue);
        }

        .form-message.error {
            background-color: rgba(255, 100, 100, 0.1);
            color: #d63031;
            border: 1px solid #ff7675;
        }

        @media (max-width: 768px) {
            .mood-emojis {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .mood-emoji {
                font-size: 2.5rem;
            }

            .tags-container {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="mood-tracker.php" class="active">Mood Tracker</a></li>
                <li><a href="journal.php">Journal</a></li>
                <li><a href="insights.php">Insights</a></li>
                <li><a href="reminders.php">Reminders</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="btn-login">Log Out</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Mood Tracker 😊</h1>
            <p class="section-intro">Track your emotions daily and discover patterns over time</p>
        </div>
    </section>

    <!-- Mood Tracker Main -->
    <section class="mood-tracker">
        <div class="container">
            <div class="tracker-container">
                <!-- Mood Selector -->
                <div class="mood-selector">
                    <h2>How are you feeling today?</h2>
                    <p>Select your current mood from the options below</p>

                    <?php if (isset($error)): ?>
                        <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="form-message success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mood-emojis">
                            <div class="mood-emoji" data-value="1" data-emoji="😢" onclick="selectMood(1, '😢')">😢</div>
                            <div class="mood-emoji" data-value="3" data-emoji="😞" onclick="selectMood(3, '😞')">😞</div>
                            <div class="mood-emoji" data-value="5" data-emoji="😐" onclick="selectMood(5, '😐')">😐</div>
                            <div class="mood-emoji active" data-value="7" data-emoji="🙂" onclick="selectMood(7, '🙂')">🙂</div>
                            <div class="mood-emoji" data-value="9" data-emoji="😊" onclick="selectMood(9, '😊')">😊</div>
                        </div>

                        <div class="mood-slider-container">
                            <input type="range" min="1" max="10" value="7" class="mood-slider" id="mood-slider" name="mood_value" onchange="updateEmojiSelection(this.value)">
                            <div class="slider-labels">
                                <span>Very Low</span>
                                <span>Neutral</span>
                                <span>Excellent</span>
                            </div>
                        </div>

                        <div class="tags-section">
                            <h3 class="tags-title">What's affecting your mood today? (Optional)</h3>
                            <div class="tags-container">
                                <input type="checkbox" id="tag-school" class="tag-checkbox" name="tags[]" value="school">
                                <label for="tag-school" class="tag-label">School</label>

                                <input type="checkbox" id="tag-relationship" class="tag-checkbox" name="tags[]" value="relationship">
                                <label for="tag-relationship" class="tag-label">Relationship</label>

                                <input type="checkbox" id="tag-stress" class="tag-checkbox" name="tags[]" value="stress">
                                <label for="tag-stress" class="tag-label">Stress</label>

                                <input type="checkbox" id="tag-family" class="tag-checkbox" name="tags[]" value="family">
                                <label for="tag-family" class="tag-label">Family</label>

                                <input type="checkbox" id="tag-friends" class="tag-checkbox" name="tags[]" value="friends">
                                <label for="tag-friends" class="tag-label">Friends</label>

                                <input type="checkbox" id="tag-health" class="tag-checkbox" name="tags[]" value="health">
                                <label for="tag-health" class="tag-label">Health</label>

                                <input type="checkbox" id="tag-work" class="tag-checkbox" name="tags[]" value="work">
                                <label for="tag-work" class="tag-label">Work</label>

                                <input type="checkbox" id="tag-weather" class="tag-checkbox" name="tags[]" value="weather">
                                <label for="tag-weather" class="tag-label">Weather</label>
                            </div>
                        </div>

                        <div class="note-section">
                            <h3>Add a note (Optional)</h3>
                            <textarea class="note-textarea" name="mood_note" placeholder="Write about what's on your mind..."></textarea>
                        </div>

                        <input type="hidden" name="date_recorded" value="<?php echo date('Y-m-d'); ?>">

                        <button type="submit" class="save-button">
                            <i class="fas fa-save"></i> Save Today's Mood
                        </button>
                    </form>
                </div>

                <!-- Recent Moods -->
                <div class="recent-moods">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h2>Your Recent Moods 📅</h2>
                    </div>
                    <p>Track your emotional journey over time</p>

                    <div class="mood-history">
                        <?php if (!empty($recentMoods)): ?>
                            <?php foreach ($recentMoods as $mood): ?>
                                <div class="mood-day">
                                    <div class="mood-day-emoji">
                                        <?php 
                                        // Map mood value to emoji
                                        if ($mood['mood_value'] <= 3) echo '😢';
                                        elseif ($mood['mood_value'] <= 5) echo '😞';
                                        elseif ($mood['mood_value'] <= 7) echo '🙂';
                                        elseif ($mood['mood_value'] <= 9) echo '😊';
                                        else echo '😄';
                                        ?>
                                    </div>
                                    <div class="mood-day-date">
                                        <?php echo date('M j', strtotime($mood['date_recorded'])); ?>
                                    </div>
                                    <div class="mood-day-value">
                                        <?php echo $mood['mood_value']; ?>/10
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="text-align: center; padding: 2rem; color: var(--text-light);">No mood entries yet. Start tracking today!</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Streak Section -->
                <div class="streak-section">
                    <span class="streak-number"><?php echo $moodStreak; ?></span>
                    <span class="streak-label">Day Mood Tracking Streak! 🔥</span>
                    <p style="margin-top: 1rem; opacity: 0.9;">Keep going! Consistency helps you understand yourself better.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SootheSpace 🌸</h3>
                    <p>A safe haven for mental wellness, designed with care for young minds.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                        <a href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="resources.php">Resources</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Data Safety</a></li>
                        <li><a href="#">Crisis Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Get Help</h4>
                    <p>If you're in crisis, please contact:</p>
                    <p class="crisis-line"><i class="fas fa-phone"></i> National Crisis Line: 988</p>
                    <p class="crisis-line"><i class="fas fa-comment"></i> Crisis Text Line: Text HOME to 741741</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 SootheSpace. All rights reserved. Made with <i class="fas fa-heart"></i> for youth mental wellness.</p>
            </div>
        </div>
    </footer>

    <script>
        // Update slider color based on value
        function updateSliderColor(value) {
            const percent = ((value - 1) / 9) * 100;
            // Adjust gradient based on value
            const hue = 120 - (value - 1) * 12; // From red (hue ~0) to green (hue ~120)
            document.getElementById('mood-slider').style.background = `linear-gradient(to right, hsl(${hue}, 100%, 50%), hsl(${hue + 20}, 100%, 50%))`;
        }

        // Function to select mood and update UI
        function selectMood(value, emoji) {
            // Remove active class from all emojis
            document.querySelectorAll('.mood-emoji').forEach(el => {
                el.classList.remove('active');
            });
            
            // Add active class to clicked emoji
            event.target.classList.add('active');
            
            // Update slider value
            document.getElementById('mood-slider').value = value;
            
            // Update slider color
            updateSliderColor(value);
        }

        // Function to update emoji selection when slider changes
        function updateEmojiSelection(value) {
            // Remove active class from all emojis
            document.querySelectorAll('.mood-emoji').forEach(el => {
                el.classList.remove('active');
            });
            
            // Find closest emoji to the slider value
            let closestEmoji = null;
            let minDiff = Infinity;
            
            document.querySelectorAll('.mood-emoji').forEach(emoji => {
                const emojiValue = parseInt(emoji.getAttribute('data-value'));
                const diff = Math.abs(emojiValue - value);
                
                if (diff < minDiff) {
                    minDiff = diff;
                    closestEmoji = emoji;
                }
            });
            
            if (closestEmoji) {
                closestEmoji.classList.add('active');
            }
            
            // Update slider color
            updateSliderColor(value);
        }

        // Initialize slider color
        document.addEventListener('DOMContentLoaded', function() {
            updateSliderColor(document.getElementById('mood-slider').value);
        });
    </script>
</body>
</html>