<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* About-specific styles */
        .page-header {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }
        
        .page-header h1 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }
        
        .about-section {
            padding: 4rem 0;
        }
        
        .about-section:nth-child(even) {
            background-color: var(--white);
        }
        
        .about-section:nth-child(odd) {
            background-color: var(--light-blue);
        }
        
        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .mission-card, .vision-card {
            background-color: var(--white);
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: 0 10px 30px var(--shadow);
            text-align: center;
            border-top: 5px solid var(--accent-pink);
            transition: var(--transition);
        }
        
        .vision-card {
            border-top-color: var(--accent-lavender);
        }
        
        .mission-card:hover, .vision-card:hover {
            transform: translateY(-10px);
        }
        
        .mission-icon, .vision-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .mission-statement, .vision-statement {
            font-style: italic;
            font-size: 1.3rem;
            color: var(--dark-blue);
            margin: 1.5rem 0;
            line-height: 1.6;
            padding: 0 1rem;
        }
        
        .why-we-exist {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .issue-card {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 20px var(--shadow);
            transition: var(--transition);
        }
        
        .issue-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(122, 184, 217, 0.2);
        }
        
        .issue-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin: 4rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 2rem;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 5px 20px var(--shadow);
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-blue);
            display: block;
            line-height: 1;
        }
        
        .stat-text {
            font-size: 0.9rem;
            color: var(--text-light);
            display: block;
            margin-top: 0.5rem;
        }
        
        .team-values {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .value-card {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 20px var(--shadow);
            transition: var(--transition);
        }
        
        .value-card:hover {
            transform: translateY(-5px);
        }
        
        .value-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .cta-section {
            padding: 5rem 0;
            background: linear-gradient(135deg, var(--accent-pink) 0%, var(--accent-lavender) 100%);
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            color: white;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .mission-statement, .vision-statement {
                font-size: 1.1rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
<?php
session_start();
// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'User' : '';
?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">SootheSpace 🌸</a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php" class="active">About</a></li>
                <li><a href="features.php">Features</a></li>
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
            <h1>About SootheSpace 🌸</h1>
            <p class="section-intro">Our mission, vision, and why we exist to support youth mental health</p>
        </div>
    </section>

    <!-- Mission & Vision -->
    <section class="about-section">
        <div class="container">
            <div class="mission-vision">
                <div class="mission-card">
                    <div class="mission-icon">🎯</div>
                    <h3>Our Mission</h3>
                    <p class="mission-statement">"To help young people understand their mental health patterns early."</p>
                    <p>We believe that awareness is the first step toward healing. By providing tools to track and understand emotional patterns, we empower youth to take control of their mental wellbeing.</p>
                </div>
                
                <div class="vision-card">
                    <div class="vision-icon">🌈</div>
                    <h3>Our Vision</h3>
                    <p class="vision-statement">"A world where mental awareness is part of daily life."</p>
                    <p>We envision a future where checking in with your emotions is as routine as checking the weather. Where young people grow up with emotional literacy skills that help them navigate life's challenges.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why We Exist -->
    <section class="about-section">
        <div class="container">
            <h2>Why We Exist 💙</h2>
            <p class="section-intro">Young people today face unique challenges that need dedicated support</p>
            
            <div class="why-we-exist">
                <div class="issue-card">
                    <div class="issue-icon">😔</div>
                    <h4>Academic Pressure</h4>
                    <p>Grades, exams, and future uncertainty create immense stress for students at all levels.</p>
                </div>
                
                <div class="issue-card">
                    <div class="issue-icon">😟</div>
                    <h4>Bullying & Social Stress</h4>
                    <p>Online and offline bullying affects mental health, self-esteem, and social connections.</p>
                </div>
                
                <div class="issue-card">
                    <div class="issue-icon">😰</div>
                    <h4>Anxiety & Depression</h4>
                    <p>Rising rates of anxiety and depression among youth need proactive, accessible support.</p>
                </div>
                
                <div class="issue-card">
                    <div class="issue-icon">😞</div>
                    <h4>Self-Esteem Issues</h4>
                    <p>Social media comparisons and societal pressures impact how young people see themselves.</p>
                </div>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number">1 in 5</span>
                    <span class="stat-text">youth experience mental health challenges</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50%</span>
                    <span class="stat-text">of mental health conditions begin by age 14</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">70%</span>
                    <span class="stat-text">of youth don't receive adequate support</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values -->
    <section class="about-section">
        <div class="container">
            <h2>Our Core Values 🌟</h2>
            <p class="section-intro">The principles that guide everything we do</p>
            
            <div class="team-values">
                <div class="value-card">
                    <div class="value-icon">🔒</div>
                    <h4>Privacy First</h4>
                    <p>Your data belongs to you. We never sell information or show ads.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h4>Youth-Centered</h4>
                    <p>Designed with and for young people. Your feedback shapes our platform.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🌱</div>
                    <h4>Growth Mindset</h4>
                    <p>Mental wellness is a journey, not a destination. We celebrate progress.</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">💙</div>
                    <h4>Compassion</h4>
                    <p>We meet you where you are, without judgment. All feelings are valid.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <h2>Join Our Community of Care 🌸</h2>
            <p>Together, we're building a more emotionally aware generation.</p>
            <a href="signup.php" class="btn-primary">Start Your Journey Today</a>
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
                        <li><a href="features.php">Features</a></li>
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