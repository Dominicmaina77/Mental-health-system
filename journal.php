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
require_once 'backend/models/JournalEntry.php';

$database = new Database();
$db = $database->getConnection();
$journalEntry = new JournalEntry($db);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');

                if (!empty($content)) {
                    $journalEntry->user_id = $_SESSION['user_id'];
                    $journalEntry->title = $title ?: null;
                    $journalEntry->content = $content;

                    if ($journalEntry->create()) {
                        $success = 'Entry saved successfully!';
                    } else {
                        $error = 'Failed to save entry.';
                    }
                } else {
                    $error = 'Content cannot be empty.';
                }
                break;

            case 'update':
                $id = intval($_POST['id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');

                if ($id > 0 && !empty($content)) {
                    $journalEntry->id = $id;
                    $journalEntry->user_id = $_SESSION['user_id'];
                    $journalEntry->title = $title ?: null;
                    $journalEntry->content = $content;

                    if ($journalEntry->update()) {
                        $success = 'Entry updated successfully!';
                    } else {
                        $error = 'Failed to update entry.';
                    }
                } else {
                    $error = 'Invalid entry ID or empty content.';
                }
                break;

            case 'delete':
                $id = intval($_POST['id'] ?? 0);

                if ($id > 0) {
                    $journalEntry->id = $id;
                    $journalEntry->user_id = $_SESSION['user_id'];

                    if ($journalEntry->delete()) {
                        $success = 'Entry deleted successfully!';
                    } else {
                        $error = 'Failed to delete entry.';
                    }
                } else {
                    $error = 'Invalid entry ID.';
                }
                break;
        }
    }
}

// Get recent journal entries for the user
$recentEntries = $journalEntry->getByUser($_SESSION['user_id'], 10);

// Calculate statistics
$totalEntries = $journalEntry->getCountByUser($_SESSION['user_id']);
$entriesThisMonth = $journalEntry->getRecentByUser($_SESSION['user_id'], 30);
$entriesThisMonth = count(array_filter($entriesThisMonth, function($entry) {
    return date('m/Y', strtotime($entry['created_at'])) === date('m/Y');
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        .journal-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .journal-container {
            padding: 3rem 0;
            background-color: var(--white);
        }

        .journal-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 992px) {
            .journal-layout {
                grid-template-columns: 1fr;
            }
        }

        .editor-section {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .entries-section {
            background-color: var(--light-blue);
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
            display: flex;
            flex-direction: column;
        }

        .entries-container {
            max-height: 500px; /* Set a max height for scrolling */
            overflow-y: auto; /* Enable vertical scrolling */
            padding-right: 10px; /* Space for scrollbar */
        }

        /* Custom scrollbar styling */
        .entries-container::-webkit-scrollbar {
            width: 8px;
        }

        .entries-container::-webkit-scrollbar-track {
            background: var(--pastel-blue);
            border-radius: 4px;
        }

        .entries-container::-webkit-scrollbar-thumb {
            background: var(--medium-blue);
            border-radius: 4px;
        }

        .entries-container::-webkit-scrollbar-thumb:hover {
            background: var(--dark-blue);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--pastel-blue);
            border-radius: 8px;
            margin-bottom: 1rem;
            background-color: var(--white);
        }

        textarea.form-control {
            min-height: 200px;
        }

        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .tag-option {
            padding: 0.5rem 1rem;
            background-color: var(--pastel-blue);
            border-radius: 50px;
            cursor: pointer;
        }

        .tag-option.selected {
            background-color: var(--medium-blue);
            color: white;
        }

        .journal-entry {
            background-color: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid var(--medium-blue);
            max-height: 300px; /* Limit height of each entry */
            overflow-y: auto; /* Allow scrolling within each entry */
        }

        /* Custom scrollbar for individual entries */
        .journal-entry::-webkit-scrollbar {
            width: 6px;
        }

        .journal-entry::-webkit-scrollbar-track {
            background: var(--pastel-blue);
            border-radius: 3px;
        }

        .journal-entry::-webkit-scrollbar-thumb {
            background: var(--medium-blue);
            border-radius: 3px;
        }

        .journal-entry::-webkit-scrollbar-thumb:hover {
            background: var(--dark-blue);
        }

        .entry-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .entry-actions {
            display: flex;
            gap: 0.5rem;
        }

        .entry-actions button {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: var(--transition);
        }

        .entry-actions button:hover {
            background-color: var(--pastel-blue);
            color: var(--dark-blue);
        }

        .entry-date {
            margin-bottom: 1rem;
        }

        .stats-card {
            background: linear-gradient(135deg, var(--medium-blue) 0%, var(--dark-blue) 100%);
            color: white;
            padding: 2rem;
            border-radius: var(--radius);
            margin-top: 2rem;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
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
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="mood-tracker.php">Mood Tracker</a></li>
                <li><a href="journal.php" class="active">Journal</a></li>
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

    <section class="journal-header">
        <div class="container">
            <h1>My Journal 📝</h1>
            <p>Write, save, and reflect on your thoughts privately</p>
        </div>
    </section>

    <section class="journal-container">
        <div class="container">
            <div class="journal-layout">
                <div class="editor-section">
                    <h3>New Entry ✨</h3>
                    
                    <?php if (isset($error)): ?>
                        <div class="form-message error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="form-message success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="create">
                        <input type="text" class="form-control" placeholder="Title (optional)" name="title" id="entry-title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                        <textarea class="form-control" placeholder="Write your thoughts here..." name="content" id="entry-content"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>

                        <button type="submit" class="btn-primary" id="save-entry" style="width: 100%;">
                            <i class="fas fa-save"></i> Save Entry
                        </button>
                    </form>

                    <p style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                        <i class="fas fa-lock"></i> Private by default - only you can see this
                    </p>
                </div>

                <div class="entries-section">
                    <h3>Recent Entries 📚</h3>
                    <div class="entries-container">
                        <div class="journal-entries">
                            <?php if (!empty($recentEntries)): ?>
                                <?php foreach ($recentEntries as $entry): ?>
                                    <div class="journal-entry" data-entry-id="<?php echo $entry['id']; ?>">
                                        <div class="entry-header">
                                            <strong><?php echo htmlspecialchars($entry['title'] ?: 'Untitled Entry'); ?></strong>
                                            <div class="entry-actions">
                                                <button class="btn-edit" title="Edit entry" onclick="editEntry(<?php echo $entry['id']; ?>, '<?php echo addslashes(htmlspecialchars($entry['title'] ?: '')); ?>', '<?php echo addslashes(htmlspecialchars($entry['content'])); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this journal entry? This action cannot be undone.');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">
                                                    <button type="submit" class="btn-delete" title="Delete entry">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="entry-date">
                                            <span style="font-size: 0.9rem; color: var(--text-light);"><?php echo date('M j', strtotime($entry['created_at'])); ?></span>
                                        </div>
                                        <p><?php echo substr(htmlspecialchars($entry['content']), 0, 150) . (strlen($entry['content']) > 150 ? '...' : ''); ?></p>
                                        <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                                            <!-- Tags functionality not yet implemented in backend -->
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No entries yet. Start writing your first entry!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-card">
                <h3 style="color: white;">Journaling Statistics 📊</h3>
                <div class="stats-grid">
                    <div>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo $totalEntries; ?></div>
                        <div>Total Entries</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700;"><?php echo $entriesThisMonth; ?></div>
                        <div>This Month</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700;">0</div>
                        <div>Day Streak</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>SootheSpace 🌸</h3>
                    <p>A safe haven for mental wellness</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="resources.php">Resources</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Crisis Help</h4>
                    <p>National Crisis Line: 988</p>
                    <p>Text HOME to 741741</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Function to edit an entry
        function editEntry(id, title, content) {
            document.getElementById('entry-title').value = title;
            document.getElementById('entry-content').value = content;
            
            // Change the save button to update button
            const saveButton = document.getElementById('save-entry');
            saveButton.innerHTML = '<i class="fas fa-sync-alt"></i> Update Entry';
            
            // Create a hidden form for updating
            const form = document.querySelector('.editor-section form');
            form.action = '';
            form.innerHTML = `
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="${id}">
                <input type="text" class="form-control" placeholder="Title (optional)" name="title" id="entry-title" value="${title}">
                <textarea class="form-control" placeholder="Write your thoughts here..." name="content" id="entry-content">${content}</textarea>
                <button type="submit" class="btn-primary" id="save-entry" style="width: 100%;">
                    <i class="fas fa-sync-alt"></i> Update Entry
                </button>
            `;
        }
    </script>
</body>
</html>