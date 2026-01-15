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
    <title>Resources - SootheSpace 🌸</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700&family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Resources-specific styles */
        .page-header {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--soft-blue) 100%);
            text-align: center;
        }
        
        .page-header h1 {
            color: var(--dark-blue);
            margin-bottom: 1rem;
        }
        
        .resources-main {
            padding: 4rem 0;
            background-color: var(--white);
        }
        
        .resource-categories {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .category-btn {
            padding: 0.8rem 1.5rem;
            background-color: var(--light-blue);
            border: 2px solid var(--pastel-blue);
            border-radius: 50px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .category-btn:hover, .category-btn.active {
            background-color: var(--medium-blue);
            border-color: var(--medium-blue);
            color: white;
        }
        
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }
        
        .resource-card {
            background-color: var(--light-blue);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 5px 20px var(--shadow);
            transition: var(--transition);
        }
        
        .resource-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(122, 184, 217, 0.2);
        }
        
        .resource-header {
            padding: 1.5rem;
            background-color: var(--medium-blue);
            color: white;
        }
        
        .resource-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .resource-content {
            padding: 1.5rem;
        }
        
        .resource-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .resource-tag {
            background-color: var(--pastel-blue);
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            color: var(--text-dark);
        }
        
        .crisis-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, var(--soft-blue) 0%, var(--light-blue) 100%);
        }
        
        .crisis-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .crisis-card {
            background-color: var(--white);
            padding: 2rem;
            border-radius: var(--radius);
            text-align: center;
            box-shadow: 0 5px 20px var(--shadow);
            border-top: 5px solid #ff7675;
        }
        
        .crisis-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .crisis-number {
            font-size: 2rem;
            font-weight: 700;
            color: #ff7675;
            display: block;
            margin: 1rem 0;
        }
        
        .breathing-section {
            padding: 4rem 0;
            background-color: var(--white);
        }
        
        .breathing-exercise {
            background: linear-gradient(135deg, var(--accent-mint) 0%, var(--pastel-blue) 100%);
            padding: 3rem;
            border-radius: var(--radius);
            text-align: center;
            margin-top: 2rem;
        }
        
        .breathing-animation {
            width: 150px;
            height: 150px;
            background-color: var(--medium-blue);
            border-radius: 50%;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            animation: breathe 8s infinite ease-in-out;
        }
        
        @keyframes breathe {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.2); }
            50% { transform: scale(1); }
            75% { transform: scale(0.8); }
        }
        
        .steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .step {
            text-align: center;
            padding: 1.5rem;
            background-color: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 5px 15px var(--shadow);
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background-color: var(--medium-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .resource-categories {
                flex-direction: column;
                align-items: center;
            }
            
            .category-btn {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
            
            .breathing-exercise {
                padding: 2rem 1rem;
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
                <li><a href="features.php">Features</a></li>
                <li><a href="resources.php" class="active">Resources</a></li>
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
            <h1>Resources 📚</h1>
            <p class="section-intro">Mental wellness guides, crisis links, and self-care tools</p>
        </div>
    </section>

    <!-- Resources Main -->
    <section class="resources-main">
        <div class="container">
            <h2>Mental Wellness Articles 📖</h2>
            <p class="section-intro">Explore articles on various mental health topics</p>
            
            <div class="resource-categories">
                <button class="category-btn active" data-category="all">All Topics</button>
                <button class="category-btn" data-category="stress">Stress</button>
                <button class="category-btn" data-category="anxiety">Anxiety</button>
                <button class="category-btn" data-category="friendships">Friendships</button>
                <button class="category-btn" data-category="relationships">Relationships</button>
                <button class="category-btn" data-category="self-care">Self-Care</button>
            </div>
            
            <div class="resources-grid">
                <!-- Article 1 -->
                <div class="resource-card" data-category="stress">
                    <div class="resource-header">
                        <div class="resource-icon">📝</div>
                        <h3>Understanding Stress</h3>
                    </div>
                    <div class="resource-content">
                        <p>Learn about stress triggers, physical symptoms, and healthy coping mechanisms for academic and social stress.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">stress</span>
                            <span class="resource-tag">academic</span>
                            <span class="resource-tag">coping</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
                
                <!-- Article 2 -->
                <div class="resource-card" data-category="anxiety">
                    <div class="resource-header">
                        <div class="resource-icon">😰</div>
                        <h3>Managing Anxiety</h3>
                    </div>
                    <div class="resource-content">
                        <p>Practical strategies for dealing with anxiety, including grounding techniques and breathing exercises.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">anxiety</span>
                            <span class="resource-tag">grounding</span>
                            <span class="resource-tag">techniques</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
                
                <!-- Article 3 -->
                <div class="resource-card" data-category="friendships">
                    <div class="resource-header">
                        <div class="resource-icon">👫</div>
                        <h3>Healthy Friendships</h3>
                    </div>
                    <div class="resource-content">
                        <p>Building and maintaining healthy friendships, setting boundaries, and dealing with conflict.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">friendships</span>
                            <span class="resource-tag">boundaries</span>
                            <span class="resource-tag">social</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
                
                <!-- Article 4 -->
                <div class="resource-card" data-category="relationships">
                    <div class="resource-header">
                        <div class="resource-icon">💑</div>
                        <h3>Relationship Wellness</h3>
                    </div>
                    <div class="resource-content">
                        <p>Navigating romantic relationships with emotional intelligence and self-respect.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">relationships</span>
                            <span class="resource-tag">emotional intelligence</span>
                            <span class="resource-tag">boundaries</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
                
                <!-- Article 5 -->
                <div class="resource-card" data-category="self-care">
                    <div class="resource-header">
                        <div class="resource-icon">💆</div>
                        <h3>Self-Care Basics</h3>
                    </div>
                    <div class="resource-content">
                        <p>Creating a sustainable self-care routine that actually works for your lifestyle.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">self-care</span>
                            <span class="resource-tag">routine</span>
                            <span class="resource-tag">wellness</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
                
                <!-- Article 6 -->
                <div class="resource-card" data-category="stress">
                    <div class="resource-header">
                        <div class="resource-icon">🎯</div>
                        <h3>Exam Stress Relief</h3>
                    </div>
                    <div class="resource-content">
                        <p>Study techniques and stress management strategies for exam seasons.</p>
                        <div class="resource-tags">
                            <span class="resource-tag">stress</span>
                            <span class="resource-tag">academic</span>
                            <span class="resource-tag">exams</span>
                        </div>
                        <a href="#" class="btn-secondary" style="margin-top: 1rem; display: inline-block;">Read More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Crisis Section -->
    <section class="crisis-section">
        <div class="container">
            <h2>Crisis Resources 🆘</h2>
            <p class="section-intro">Immediate help is available if you're in crisis</p>
            
            <div class="crisis-cards">
                <div class="crisis-card">
                    <div class="crisis-icon">📞</div>
                    <h3>National Crisis Line</h3>
                    <p>24/7 free and confidential support for people in distress</p>
                    <span class="crisis-number">988</span>
                    <p>Call or text available</p>
                </div>
                
                <div class="crisis-card">
                    <div class="crisis-icon">💬</div>
                    <h3>Crisis Text Line</h3>
                    <p>Free, 24/7 text support with trained crisis counselors</p>
                    <span class="crisis-number">Text HOME to 741741</span>
                    <p>Connect with a crisis counselor</p>
                </div>
                
                <div class="crisis-card">
                    <div class="crisis-icon">🏥</div>
                    <h3>Emergency Services</h3>
                    <p>If you're in immediate danger or concerned for someone's safety</p>
                    <span class="crisis-number">911</span>
                    <p>Or go to the nearest emergency room</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;">
                <p><strong>Remember:</strong> It's okay to ask for help. Reaching out is a sign of strength, not weakness. 💙</p>
            </div>
        </div>
    </section>

    <!-- Breathing Techniques -->
    <section class="breathing-section">
        <div class="container">
            <h2>Breathing Techniques 🌬️</h2>
            <p class="section-intro">Calm your nervous system with these simple exercises</p>
            
            <div class="breathing-exercise">
                <h3 style="color: var(--dark-blue); margin-bottom: 1rem;">5-4-3-2-1 Grounding Technique</h3>
                <div class="breathing-animation">
                    Breathe
                </div>
                <p style="margin-bottom: 2rem;">When feeling anxious or overwhelmed, use this technique to ground yourself in the present moment.</p>
                
                <div class="steps">
                    <div class="step">
                        <div class="step-number">5</div>
                        <h4>Things You Can See</h4>
                        <p>Notice 5 things around you</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">4</div>
                        <h4>Things You Can Touch</h4>
                        <p>Feel 4 things you can touch</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <h4>Things You Can Hear</h4>
                        <p>Listen for 3 sounds</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <h4>Things You Can Smell</h4>
                        <p>Identify 2 scents</p>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">1</div>
                        <h4>Thing You Can Taste</h4>
                        <p>Notice 1 thing you can taste</p>
                    </div>
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
                        <li><a href="features.php">Features</a></li>
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

    <script src="js/script.js"></script>
    <script>
        // Filter resources by category
        document.addEventListener('DOMContentLoaded', function() {
            const categoryBtns = document.querySelectorAll('.category-btn');
            const resourceCards = document.querySelectorAll('.resource-card');
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    categoryBtns.forEach(b => b.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const category = this.getAttribute('data-category');
                    
                    // Show/hide resource cards based on category
                    resourceCards.forEach(card => {
                        if (category === 'all' || card.getAttribute('data-category') === category) {
                            card.style.display = 'block';
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0)';
                            }, 10);
                        } else {
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(20px)';
                            setTimeout(() => {
                                card.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
            
            // Start breathing animation
            const breathingAnimation = document.querySelector('.breathing-animation');
            let breathCount = 0;
            const messages = ['Breathe In', 'Hold', 'Breathe Out', 'Hold'];
            
            setInterval(() => {
                breathingAnimation.textContent = messages[breathCount % 4];
                breathCount++;
            }, 2000);
        });
    </script>
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