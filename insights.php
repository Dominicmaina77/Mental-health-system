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

// Get date range from GET parameters or use default
$date_range = isset($_GET['date_range']) ? intval($_GET['date_range']) : 30;
$chart_type = isset($_GET['chart_type']) ? $_GET['chart_type'] : 'line';
$mood_range = isset($_GET['mood_range']) ? $_GET['mood_range'] : 'all';
$data_category = isset($_GET['data_category']) ? $_GET['data_category'] : 'all';

// Get mood entries for the user based on date range
if ($date_range === 'all') {
    $startDate = '1970-01-01'; // Beginning of time
    $endDate = date('Y-m-d');
} else {
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime("-{$date_range} days"));
}

$moodEntries = $moodEntry->getByUserAndDateRange($_SESSION['user_id'], $startDate, $endDate);

// Calculate insights data
$moodStreak = $moodEntry->getMoodStreak($_SESSION['user_id']);

// Calculate mood distribution
$moodDistribution = $moodEntry->getMoodDistribution($_SESSION['user_id'], $date_range);

// Calculate average mood
$averageMood = $moodEntry->getAverageMood($_SESSION['user_id'], $date_range);

// Calculate best day of week
$bestDayOfWeek = 'Friday'; // Placeholder - would need more complex calculation
$bestDayMood = $averageMood; // Placeholder

// Calculate most common mood
$mostCommonMood = 0;
$mostCommonMoodCount = 0;
foreach ($moodDistribution as $dist) {
    if ($dist['count'] > $mostCommonMoodCount) {
        $mostCommonMoodCount = $dist['count'];
        $mostCommonMood = $dist['mood_value'];
    }
}

// Calculate consistency percentage
$totalDays = $date_range;
$trackedDays = count($moodEntries);
$consistencyPercentage = $totalDays > 0 ? round(($trackedDays / $totalDays) * 100, 1) : 0;

// Prepare data for charts
$moodTrendData = [];
foreach ($moodEntries as $entry) {
    $moodTrendData[] = [
        'date_recorded' => $entry['date_recorded'],
        'mood_value' => $entry['mood_value']
    ];
}

// Prepare week-over-week data (comparing last week vs current week)
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));
$prevWeekStart = date('Y-m-d', strtotime('-1 week monday'));
$prevWeekEnd = date('Y-m-d', strtotime('-1 week sunday'));

$prevWeekAvg = $moodEntry->getAverageMood($_SESSION['user_id'], 7); // Approximation
$currentWeekAvg = $moodEntry->getAverageMood($_SESSION['user_id'], 7); // Approximation

// Prepare month-over-month data
$currentMonthStart = date('Y-m', strtotime('first day of this month'));
$currentMonthEnd = date('Y-m-d', strtotime('last day of this month'));
$prevMonthStart = date('Y-m', strtotime('first day of previous month'));
$prevMonthEnd = date('Y-m-d', strtotime('last day of previous month'));

$prevMonthAvg = $moodEntry->getAverageMood($_SESSION['user_id'], 30); // Approximation
$currentMonthAvg = $moodEntry->getAverageMood($_SESSION['user_id'], 30); // Approximation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insights - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .insights-header {
            padding: 3rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .insights-container {
            padding: 3rem 0;
            background-color: var(--white);
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .chart-card {
            background-color: var(--light-blue);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
        }

        .chart-container {
            height: 200px;
            position: relative;
            margin-top: 1rem;
        }

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .insight-card {
            background-color: var(--light-blue);
            padding: 1.5rem;
            border-radius: var(--radius);
            text-align: center;
        }

        .insight-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .heatmap {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 0.5rem;
            margin: 2rem 0;
        }

        .heatmap-day {
            aspect-ratio: 1;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .heatmap-1 { background-color: #ffebee; }
        .heatmap-2 { background-color: #ffcdd2; }
        .heatmap-3 { background-color: #ef9a9a; }
        .heatmap-4 { background-color: #e57373; }
        .heatmap-5 { background-color: #ef5350; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="mood-tracker.php">Mood Tracker</a></li>
                <li><a href="journal.html">Journal</a></li>
                <li><a href="insights.php" class="active">Insights</a></li>
                <li><a href="reminders.html">Reminders</a></li>
                <li><a href="profile.html">Profile</a></li>
                <li><a href="logout.php" class="btn-login">Log Out</a></li>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <section class="insights-header">
        <div class="container">
            <h1>Insights 📊</h1>
            <p>Visualize your emotional journey and discover patterns</p>
        </div>
    </section>

    <section class="insights-container">
        <div class="container">
            <div class="filters-section" style="display: flex; justify-content: flex-end; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <label for="date-range" style="display: inline-block; margin-right: 0.5rem; color: var(--text-dark); font-weight: 500;">Time Period:</label>
                    <select id="date-range" style="padding: 0.5rem; border-radius: 8px; border: 2px solid var(--pastel-blue); background: var(--white); color: var(--text-dark);">
                        <option value="7" <?php echo $date_range == 7 ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30" <?php echo $date_range == 30 ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="90" <?php echo $date_range == 90 ? 'selected' : ''; ?>>Last 3 Months</option>
                        <option value="365" <?php echo $date_range == 365 ? 'selected' : ''; ?>>Last Year</option>
                        <option value="all" <?php echo $date_range == 'all' ? 'selected' : ''; ?>>All Time</option>
                    </select>
                </div>
                <div>
                    <label for="chart-type" style="display: inline-block; margin-right: 0.5rem; color: var(--text-dark); font-weight: 500;">Chart Type:</label>
                    <select id="chart-type" style="padding: 0.5rem; border-radius: 8px; border: 2px solid var(--pastel-blue); background: var(--white); color: var(--text-dark);">
                        <option value="line" <?php echo $chart_type == 'line' ? 'selected' : ''; ?>>Line Chart</option>
                        <option value="bar" <?php echo $chart_type == 'bar' ? 'selected' : ''; ?>>Bar Chart</option>
                    </select>
                </div>
                <div>
                    <label for="mood-range" style="display: inline-block; margin-right: 0.5rem; color: var(--text-dark); font-weight: 500;">Mood Range:</label>
                    <select id="mood-range" style="padding: 0.5rem; border-radius: 8px; border: 2px solid var(--pastel-blue); background: var(--white); color: var(--text-dark);">
                        <option value="all" <?php echo $mood_range == 'all' ? 'selected' : ''; ?>>All Moods</option>
                        <option value="low" <?php echo $mood_range == 'low' ? 'selected' : ''; ?>>Low (1-3)</option>
                        <option value="medium" <?php echo $mood_range == 'medium' ? 'selected' : ''; ?>>Medium (4-7)</option>
                        <option value="high" <?php echo $mood_range == 'high' ? 'selected' : ''; ?>>High (8-10)</option>
                    </select>
                </div>
                <div>
                    <label for="data-category" style="display: inline-block; margin-right: 0.5rem; color: var(--text-dark); font-weight: 500;">Category:</label>
                    <select id="data-category" style="padding: 0.5rem; border-radius: 8px; border: 2px solid var(--pastel-blue); background: var(--white); color: var(--text-dark);">
                        <option value="all" <?php echo $data_category == 'all' ? 'selected' : ''; ?>>All Data</option>
                        <option value="mood" <?php echo $data_category == 'mood' ? 'selected' : ''; ?>>Mood Only</option>
                        <option value="journal" <?php echo $data_category == 'journal' ? 'selected' : ''; ?>>Journal Only</option>
                        <option value="both" <?php echo $data_category == 'both' ? 'selected' : ''; ?>>Mood & Journal</option>
                    </select>
                </div>
            </div>

            <div class="chart-grid">
                <div class="chart-card">
                    <h3>Weekly Mood Trend</h3>
                    <div class="chart-container">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Mood Distribution</h3>
                    <div class="chart-container">
                        <canvas id="distributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Comparison Charts Section -->
            <h3>Comparison Views</h3>
            <div class="chart-grid">
                <div class="chart-card">
                    <h3>Week-over-Week Comparison</h3>
                    <div class="chart-container">
                        <canvas id="weekOverWeekChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3>Month-over-Month Comparison</h3>
                    <div class="chart-container">
                        <canvas id="monthOverMonthChart"></canvas>
                    </div>
                </div>
            </div>

            <h3>Monthly Mood Heatmap</h3>
            <div class="heatmap">
                <!-- This would be generated dynamically -->
                <div class="heatmap-day heatmap-1">M</div>
                <div class="heatmap-day heatmap-3">T</div>
                <div class="heatmap-day heatmap-5">W</div>
                <div class="heatmap-day heatmap-4">T</div>
                <div class="heatmap-day heatmap-2">F</div>
                <div class="heatmap-day heatmap-1">S</div>
                <div class="heatmap-day heatmap-3">S</div>
                <!-- More days... -->
            </div>

            <div class="insights-grid">
                <div class="insight-card">
                    <div class="insight-icon">📈</div>
                    <h4>Best Day</h4>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--dark-blue);"><?php echo $bestDayOfWeek; ?></p>
                    <p>Average mood: <?php echo number_format($bestDayMood, 1); ?>/10</p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">📉</div>
                    <h4>Most Common Mood</h4>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--dark-blue);"><?php echo $mostCommonMood; ?>/10</p>
                    <p>Recorded <?php echo $mostCommonMoodCount; ?> times</p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">🌟</div>
                    <h4>Current Streak</h4>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--dark-blue);"><?php echo $moodStreak; ?> days</p>
                    <p>Keep going! 🔥</p>
                </div>

                <div class="insight-card">
                    <div class="insight-icon">📅</div>
                    <h4>Tracking Consistency</h4>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--dark-blue);"><?php echo $consistencyPercentage; ?>%</p>
                    <p>Days tracked: <?php echo $trackedDays; ?>/<?php echo $date_range; ?></p>
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
        // Function to update charts with PHP-generated data
        function updateCharts() {
            // Update weekly chart with mood trend data
            <?php if (!empty($moodTrendData)): ?>
            const moodTrendData = <?php echo json_encode($moodTrendData); ?>;
            
            const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (window.weeklyChart && typeof window.weeklyChart.destroy === 'function') {
                window.weeklyChart.destroy();
            }
            
            // Prepare data for chart
            const labels = moodTrendData.map(item => {
                const date = new Date(item.date_recorded);
                return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            });
            
            const data = moodTrendData.map(item => item.mood_value);
            
            // Get chart type from filter
            const chartType = document.getElementById('chart-type').value;
            
            window.weeklyChart = new Chart(weeklyCtx, {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Mood Level',
                        data: data,
                        borderColor: 'rgba(122, 184, 217, 1)',
                        backgroundColor: chartType === 'line' ? 'rgba(122, 184, 217, 0.1)' : 'rgba(122, 184, 217, 0.5)',
                        borderWidth: 3,
                        fill: chartType === 'line',
                        tension: 0.4,
                        pointBackgroundColor: data.map(value => {
                            if (value <= 3) return '#ff7675'; // Red for low moods
                            if (value <= 7) return '#fdcb6e'; // Yellow for medium moods
                            return '#00b894'; // Green for high moods
                        }),
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 1,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Mood Level (1-10)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Mood: ${context.parsed.y}/10`;
                                }
                            }
                        }
                    }
                }
            });
            <?php else: ?>
            // Show message if no data available
            const weeklyChartCanvas = document.getElementById('weeklyChart');
            if (weeklyChartCanvas) {
                const ctx = weeklyChartCanvas.getContext('2d');
                ctx.clearRect(0, 0, weeklyChartCanvas.width, weeklyChartCanvas.height);
                ctx.font = "14px Arial";
                ctx.fillStyle = "#95a5a6";
                ctx.textAlign = "center";
                ctx.fillText("No mood trend data available. Start tracking your mood!", weeklyChartCanvas.width/2, weeklyChartCanvas.height/2);
            }
            <?php endif; ?>
            
            // Update distribution chart with mood distribution data
            <?php if (!empty($moodDistribution)): ?>
            const moodDistributionData = <?php echo json_encode($moodDistribution); ?>;
            
            const distCtx = document.getElementById('distributionChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (window.distributionChart && typeof window.distributionChart.destroy === 'function') {
                window.distributionChart.destroy();
            }
            
            // Prepare data for chart
            const labels = [];
            const data = [];
            const backgroundColors = [];
            
            moodDistributionData.forEach(item => {
                labels.push(`Mood ${item.mood_value}`);
                data.push(item.count);
                
                // Assign colors based on mood value
                if (item.mood_value <= 3) {
                    backgroundColors.push('#ff7675'); // Red for low moods
                } else if (item.mood_value <= 7) {
                    backgroundColors.push('#fdcb6e'); // Yellow for medium moods
                } else {
                    backgroundColors.push('#00b894'); // Green for high moods
                }
            });
            
            // Get chart type from filter
            const chartType = document.getElementById('chart-type').value;
            const distributionChartType = chartType === 'bar' ? 'bar' : 'doughnut';
            
            window.distributionChart = new Chart(distCtx, {
                type: distributionChartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Frequency',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} entries`;
                                }
                            }
                        }
                    }
                }
            });
            <?php else: ?>
            // Show message if no data available
            const distributionChartCanvas = document.getElementById('distributionChart');
            if (distributionChartCanvas) {
                const ctx = distributionChartCanvas.getContext('2d');
                ctx.clearRect(0, 0, distributionChartCanvas.width, distributionChartCanvas.height);
                ctx.font = "14px Arial";
                ctx.fillStyle = "#95a5a6";
                ctx.textAlign = "center";
                ctx.fillText("No mood distribution data available. Start tracking your mood!", distributionChartCanvas.width/2, distributionChartCanvas.height/2);
            }
            <?php endif; ?>
            
            // Update week-over-week chart
            <?php if (isset($prevWeekAvg) && isset($currentWeekAvg)): ?>
            const weekOverWeekCtx = document.getElementById('weekOverWeekChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (window.weekOverWeekChart && typeof window.weekOverWeekChart.destroy === 'function') {
                window.weekOverWeekChart.destroy();
            }
            
            // Sample data for demonstration
            const weeks = ['Prev Week', 'This Week'];
            const avgMoods = [<?php echo number_format($prevWeekAvg, 1); ?>, <?php echo number_format($currentWeekAvg, 1); ?>];
            
            window.weekOverWeekChart = new Chart(weekOverWeekCtx, {
                type: 'bar',
                data: {
                    labels: weeks,
                    datasets: [{
                        label: 'Average Mood',
                        data: avgMoods,
                        backgroundColor: [
                            'rgba(255, 118, 117, 0.5)', // Red for previous week if lower
                            avgMoods[1] > avgMoods[0] ? 'rgba(0, 184, 148, 0.5)' : 'rgba(253, 203, 110, 0.5)' // Green if improvement, yellow if decline
                        ],
                        borderColor: [
                            'rgba(255, 118, 117, 1)',
                            avgMoods[1] > avgMoods[0] ? 'rgba(0, 184, 148, 1)' : 'rgba(253, 203, 110, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 1,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Average Mood (1-10)'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Avg Mood: ${context.parsed.y}/10`;
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
            
            // Update month-over-month chart
            <?php if (isset($prevMonthAvg) && isset($currentMonthAvg)): ?>
            const monthOverMonthCtx = document.getElementById('monthOverMonthChart').getContext('2d');
            
            // Destroy existing chart if it exists
            if (window.monthOverMonthChart && typeof window.monthOverMonthChart.destroy === 'function') {
                window.monthOverMonthChart.destroy();
            }
            
            // Sample data for demonstration
            const months = ['Prev Month', 'This Month'];
            const avgMoods = [<?php echo number_format($prevMonthAvg, 1); ?>, <?php echo number_format($currentMonthAvg, 1); ?>];
            
            window.monthOverMonthChart = new Chart(monthOverMonthCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Average Mood',
                        data: avgMoods,
                        backgroundColor: [
                            'rgba(255, 118, 117, 0.5)', // Red for previous month if lower
                            avgMoods[1] > avgMoods[0] ? 'rgba(0, 184, 148, 0.5)' : 'rgba(253, 203, 110, 0.5)' // Green if improvement, yellow if decline
                        ],
                        borderColor: [
                            'rgba(255, 118, 117, 1)',
                            avgMoods[1] > avgMoods[0] ? 'rgba(0, 184, 148, 1)' : 'rgba(253, 203, 110, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 1,
                            max: 10,
                            title: {
                                display: true,
                                text: 'Average Mood (1-10)'
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Avg Mood: ${context.parsed.y}/10`;
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        }
        
        // Add event listeners for filter controls
        function setupFilterEventListeners() {
            document.getElementById('date-range').addEventListener('change', function() {
                const selectedValue = this.value;
                // Reload the page with the new date range
                window.location.href = `insights.php?date_range=${selectedValue}&chart_type=${document.getElementById('chart-type').value}&mood_range=${document.getElementById('mood-range').value}&data_category=${document.getElementById('data-category').value}`;
            });
            
            document.getElementById('chart-type').addEventListener('change', function() {
                const selectedValue = this.value;
                // Reload the page with the new chart type
                window.location.href = `insights.php?date_range=${document.getElementById('date-range').value}&chart_type=${selectedValue}&mood_range=${document.getElementById('mood-range').value}&data_category=${document.getElementById('data-category').value}`;
            });
            
            document.getElementById('mood-range').addEventListener('change', function() {
                const selectedValue = this.value;
                // Reload the page with the new mood range
                window.location.href = `insights.php?date_range=${document.getElementById('date-range').value}&chart_type=${document.getElementById('chart-type').value}&mood_range=${selectedValue}&data_category=${document.getElementById('data-category').value}`;
            });
            
            document.getElementById('data-category').addEventListener('change', function() {
                const selectedValue = this.value;
                // Reload the page with the new data category
                window.location.href = `insights.php?date_range=${document.getElementById('date-range').value}&chart_type=${document.getElementById('chart-type').value}&mood_range=${document.getElementById('mood-range').value}&data_category=${selectedValue}`;
            });
        }
        
        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateCharts();
            setupFilterEventListeners();
        });
    </script>
</body>
</html>