<?php
session_start();
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'User' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Features-specific styles */
        .page-header {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }

        .page-header h1 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }

        .features-main {
            padding: 4rem 0;
            background-color: var(--white);
        }

        .feature-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-bottom: 4rem;
        }

        .feature-detail:nth-child(even) {
            grid-template-columns: 1fr 1fr;
        }

        .feature-detail:nth-child(even) .feature-content {
            order: 2;
        }

        .feature-detail:nth-child(even) .feature-visual {
            order: 1;
        }

        .feature-icon-large {
            font-size: 4rem;
            margin-bottom: 1.5rem;
        }

        .feature-visual {
            background-color: var(--light-blue);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px var(--shadow);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-visual img {
            max-width: 100%;
            border-radius: 8px;
        }

        .emoji-slider {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(90deg, #ff7675, #fdcb6e, #00b894);
            border-radius: 50px;
            margin: 1rem 0;
        }

        .emoji-options {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
        }

        .emoji-option {
            font-size: 2rem;
            cursor: pointer;
            transition: var(--transition);
            padding: 0.5rem;
            border-radius: 50%;
        }

        .emoji-option:hover {
            background-color: var(--pastel-blue);
            transform: scale(1.2);
        }

        .journal-preview {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 8px;
            border: 2px solid var(--pastel-blue);
            font-family: 'Courier New', monospace;
            text-align: left;
            width: 100%;
        }

        .journal-date {
            color: var(--medium-blue);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .privacy-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--soft-blue) 0%, var(--light-blue) 100%);
            text-align: center;
        }

        .privacy-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .privacy-card {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 20px var(--shadow);
        }

        .privacy-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--medium-blue);
        }

        .cta-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--medium-blue) 0%, var(--dark-blue) 100%);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            color: white;
            margin-bottom: 1rem;
        }

        @media (max-width: 992px) {
            .feature-detail {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .feature-detail:nth-child(even) {
                grid-template-columns: 1fr;
            }

            .feature-detail:nth-child(even) .feature-content,
            .feature-detail:nth-child(even) .feature-visual {
                order: 0;
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
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="features.php" class="active">Features</a></li>
                <li><a href="resources.php">Resources</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="dashboard.php" class="btn-login">Dashboard</a></li>
                    <li><a href="logout.php" class="btn-signup">Log Out</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Log In</a></li>
                    <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
            <div class="menu-btn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Features 🌟</h1>
            <p class="section-intro">Discover tools designed to support your mental wellness journey</p>
        </div>
    </section>

    <!-- Features Details -->
    <section class="features-main">
        <div class="container">
            <!-- Mood Log Feature -->
            <div class="feature-detail">
                <div class="feature-content">
                    <div class="feature-icon-large">😊</div>
                    <h2>Mood Log</h2>
                    <p>Track your emotions daily with our intuitive mood tracker. Simply select how you're feeling from our emoji-based scale, add tags for context, and leave optional notes.</p>
                    <ul>
                        <li>🌡️ Color-coded mood slider from 1-10</li>
                        <li>🎯 Emoji-style moods for quick selection</li>
                        <li>🏷️ Tags for school, relationships, stress, etc.</li>
                        <li>📝 Optional notes for context</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="emoji-slider"></div>
                    <div class="emoji-options">
                        <span class="emoji-option">😢</span>
                        <span class="emoji-option">😞</span>
                        <span class="emoji-option">😐</span>
                        <span class="emoji-option">🙂</span>
                        <span class="emoji-option">😊</span>
                    </div>
                    <div style="margin-top: 2rem;">
                        <p><strong>Tags:</strong> <span class="tag">School</span> <span class="tag">Stress</span> <span class="tag">Family</span></p>
                    </div>
                </div>
            </div>

            <!-- Daily Journal Feature -->
            <div class="feature-detail">
                <div class="feature-content">
                    <div class="feature-icon-large">📝</div>
                    <h2>Daily Journal</h2>
                    <p>Express your thoughts freely in your secure digital journal. Write, save, and edit entries with complete privacy. Your words are for your eyes only.</p>
                    <ul>
                        <li>🔒 Private by default - only you can see your entries</li>
                        <li>📅 Date-stamped entries for reflection</li>
                        <li>🏷️ Add tags to organize your thoughts</li>
                        <li>🔍 Search through past entries easily</li>
                        <li>📤 Export entries as PDF if desired</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div class="journal-preview">
                        <div class="journal-date">Today, 3:45 PM</div>
                        <p>Today was challenging but I'm proud of how I handled things. I practiced deep breathing when I felt overwhelmed...</p>
                        <p>Grateful for: My friend checking in on me ☕</p>
                        <div style="margin-top: 1rem; font-size: 0.9rem; color: var(--text-light);">
                            Tags: <span style="background-color: var(--pastel-blue); padding: 0.2rem 0.5rem; border-radius: 4px;">reflection</span>
                            <span style="background-color: var(--pastel-blue); padding: 0.2rem 0.5rem; border-radius: 4px;">gratitude</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Insights Feature -->
            <div class="feature-detail">
                <div class="feature-content">
                    <div class="feature-icon-large">📈</div>
                    <h2>Charts & Insights</h2>
                    <p>Visualize your emotional journey with beautiful charts. Spot trends, identify patterns, and celebrate your progress over time.</p>
                    <ul>
                        <li>📊 Weekly mood charts</li>
                        <li>📅 Monthly trends and patterns</li>
                        <li>🎯 Mood heatmap visualization</li>
                        <li>💡 Personalized insights based on your data</li>
                        <li>📋 "Your most common triggers" analysis</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div style="width: 100%; text-align: center;">
                        <div style="display: inline-block; padding: 2rem; background: linear-gradient(135deg, var(--light-blue) 0%, var(--pastel-blue) 100%); border-radius: var(--radius);">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                            <h3 style="color: var(--dark-blue);">Weekly Insights</h3>
                            <p>Your mood average: 7.2/10 📈</p>
                            <p>Best day: Friday 🌟</p>
                            <p>Common trigger: School deadlines</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reminders Feature -->
            <div class="feature-detail">
                <div class="feature-content">
                    <div class="feature-icon-large">🔔</div>
                    <h2>Gentle Reminders</h2>
                    <p>Set personalized check-ins and self-care reminders. We'll help you build healthy habits with gentle notifications.</p>
                    <ul>
                        <li>⏰ Custom timing (daily, weekly, specific times)</li>
                        <li>🔔 Notification options: email or in-app</li>
                        <li>🌙 Quiet hours to respect your downtime</li>
                        <li>🎯 Goal-based reminders</li>
                        <li>📱 Push notifications for check-ins</li>
                    </ul>
                </div>
                <div class="feature-visual">
                    <div style="width: 100%; max-width: 300px; margin: 0 auto; background-color: var(--white); border-radius: 12px; padding: 1.5rem; box-shadow: 0 5px 15px var(--shadow);">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 1.5rem;">🔔</div>
                            <div>
                                <h4 style="margin: 0; color: var(--dark-blue);">Mood Check-in</h4>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--text-light);">Daily • 6:00 PM</p>
                            </div>
                        </div>
                        <p style="margin-bottom: 1rem;">Time to check in with your mood! How are you feeling right now?</p>
                        <div style="display: flex; gap: 0.5rem;">
                            <button style="flex: 1; padding: 0.5rem; background-color: var(--pastel-blue); border: none; border-radius: 8px; cursor: pointer;">Remind Later</button>
                            <button style="flex: 1; padding: 0.5rem; background-color: var(--medium-blue); color: white; border: none; border-radius: 8px; cursor: pointer;">Check In Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Section -->
    <section class="privacy-section">
        <div class="container">
            <h2>Your Privacy Matters 🔒</h2>
            <p class="section-intro">We built SootheSpace with privacy and security at its core</p>
            
            <div class="privacy-features">
                <div class="privacy-card">
                    <div class="privacy-icon">🔐</div>
                    <h4>End-to-End Encryption</h4>
                    <p>All your data is encrypted and secure. Even we can't read your journal entries.</p>
                </div>
                
                <div class="privacy-card">
                    <div class="privacy-icon">🚫</div>
                    <h4>No Tracking, No Ads</h4>
                    <p>We don't track you across the web. No ads, no data selling, ever.</p>
                </div>
                
                <div class="privacy-card">
                    <div class="privacy-icon">🗑️</div>
                    <h4>Full Data Control</h4>
                    <p>Export or delete your data anytime. You own your information.</p>
                </div>
                
                <div class="privacy-card">
                    <div class="privacy-icon">👤</div>
                    <h4>Anonymous Analytics</h4>
                    <p>We only use anonymous, aggregated data to improve our services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Wellness Journey? 🚀</h2>
            <p>Experience all these features in a safe, supportive environment designed just for you.</p>
            <a href="signup.php" class="btn-primary">Get Started for Free</a>
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
                        <?php if ($isLoggedIn): ?>
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="profile.html">Profile</a></li>
                        <?php endif; ?>
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

    <script src="js/api.js"></script>
    <script>
        // Update navigation based on user status
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            const menuBtn = document.querySelector('.menu-btn');
            const navLinks = document.querySelector('.nav-links');

            if (menuBtn) {
                menuBtn.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    menuBtn.querySelector('i').classList.toggle('fa-bars');
                    menuBtn.querySelector('i').classList.toggle('fa-times');
                });
            }

            // Close mobile menu when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.navbar') && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    if (menuBtn) {
                        menuBtn.querySelector('i').classList.remove('fa-times');
                        menuBtn.querySelector('i').classList.add('fa-bars');
                    }
                }
            });
        });
    </script>
</body>
</html>