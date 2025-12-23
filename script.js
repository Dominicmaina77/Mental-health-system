// Main JavaScript for SootheSpace

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
    
    // Quote Carousel
    const quoteCards = document.querySelectorAll('.quote-card');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');
    
    if (quoteCards.length > 0) {
        let currentQuote = 0;
        
        function showQuote(index) {
            quoteCards.forEach(card => card.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            quoteCards[index].classList.add('active');
            dots[index].classList.add('active');
            currentQuote = index;
        }
        
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showQuote(index));
        });
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                let newIndex = currentQuote - 1;
                if (newIndex < 0) newIndex = quoteCards.length - 1;
                showQuote(newIndex);
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                let newIndex = currentQuote + 1;
                if (newIndex >= quoteCards.length) newIndex = 0;
                showQuote(newIndex);
            });
        }
        
        // Auto-rotate quotes every 5 seconds
        setInterval(() => {
            let newIndex = currentQuote + 1;
            if (newIndex >= quoteCards.length) newIndex = 0;
            showQuote(newIndex);
        }, 5000);
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#') return;
            
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (navLinks && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        if (menuBtn) {
                            menuBtn.querySelector('i').classList.remove('fa-times');
                            menuBtn.querySelector('i').classList.add('fa-bars');
                        }
                    }
                }
            }
        });
    });
    
    // FAQ Accordion
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');
            
            // Toggle answer
            if (answer.classList.contains('active')) {
                answer.classList.remove('active');
                icon.textContent = '+';
            } else {
                // Close other answers
                document.querySelectorAll('.faq-answer.active').forEach(ans => {
                    ans.classList.remove('active');
                    ans.previousElementSibling.querySelector('.faq-icon').textContent = '+';
                });
                
                answer.classList.add('active');
                icon.textContent = '−';
            }
        });
    });
    
    // Open first FAQ by default
    if (faqQuestions.length > 0 && document.querySelector('.faq-answer')) {
        faqQuestions[0].click();
    }
    
    // Resource Filter
    const categoryBtns = document.querySelectorAll('.category-btn');
    const resourceCards = document.querySelectorAll('.resource-card');
    
    if (categoryBtns.length > 0) {
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const category = this.getAttribute('data-category');
                
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
    }
    
    // Contact Form Character Count
    const messageTextarea = document.getElementById('message');
    if (messageTextarea) {
        const charCount = document.getElementById('char-count');
        const wordCount = document.getElementById('word-count');
        
        function updateCounts() {
            const text = messageTextarea.value;
            const chars = text.length;
            const words = text.trim() === '' ? 0 : text.trim().split(/\s+/).length;
            
            if (charCount) charCount.textContent = `${chars} characters`;
            if (wordCount) wordCount.textContent = `${words} words`;
            
            if (chars > 500) {
                charCount.style.color = '#00b894';
            } else if (chars > 250) {
                charCount.style.color = '#fdcb6e';
            } else {
                charCount.style.color = 'var(--text-light)';
            }
        }
        
        messageTextarea.addEventListener('input', updateCounts);
        updateCounts();
    }
    
    // Breathing Animation
    const breathingAnimation = document.querySelector('.breathing-animation');
    if (breathingAnimation) {
        let breathCount = 0;
        const messages = ['Breathe In', 'Hold', 'Breathe Out', 'Hold'];
        
        setInterval(() => {
            breathingAnimation.textContent = messages[breathCount % 4];
            breathCount++;
        }, 2000);
    }
    
    // Form Validation Helper
    window.isValidEmail = function(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    };
    
    // Show Message Helper
    window.showMessage = function(message, type, element) {
        const existingMessage = document.querySelector('.form-message');
        if (existingMessage) existingMessage.remove();
        
        const messageEl = document.createElement('div');
        messageEl.className = `form-message ${type}`;
        messageEl.textContent = message;
        
        if (element) {
            element.appendChild(messageEl);
        } else {
            document.body.appendChild(messageEl);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (messageEl.parentNode) {
                messageEl.parentNode.removeChild(messageEl);
            }
        }, 5000);
    };
    
    // Set current date in dashboard
    const dateDisplay = document.getElementById('current-date');
    if (dateDisplay) {
        const now = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateDisplay.textContent = now.toLocaleDateString('en-US', options);
    }
    
    // Animate stat numbers
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent);
        if (!isNaN(target) && target > 0) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    stat.textContent = target;
                    clearInterval(timer);
                } else {
                    stat.textContent = Math.floor(current);
                }
            }, 30);
        }
    });
    
    // Password toggle functionality (for all pages)
    document.querySelectorAll('.toggle-password').forEach(toggleBtn => {
        toggleBtn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            if (icon) {
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        });
    });
    
    // Initialize Charts if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    }
});

// Chart Initialization Function
function initializeCharts() {
    // Weekly Mood Chart
    const weeklyCtx = document.getElementById('weeklyMoodChart');
    if (weeklyCtx) {
        new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Mood Score',
                    data: [6, 7, 5, 8, 9, 8, 7],
                    borderColor: 'rgba(122, 184, 217, 1)',
                    backgroundColor: 'rgba(122, 184, 217, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(74, 140, 179, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 1,
                        max: 10,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(168, 216, 234, 0.2)' }
                    },
                    x: {
                        grid: { color: 'rgba(168, 216, 234, 0.2)' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const labels = ['Very Low', 'Low', 'Somewhat Low', 'A Bit Low', 'Neutral', 'Okay', 'Good', 'Very Good', 'Great', 'Excellent'];
                                const value = Math.round(context.parsed.y);
                                return `Mood: ${value}/10 - ${labels[value-1] || 'Neutral'}`;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Mood Distribution Chart
    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        new Chart(distCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Low', 'Medium', 'High'],
                datasets: [{
                    data: [3, 5, 7],
                    backgroundColor: ['#ff7675', '#fdcb6e', '#00b894']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
    
    // Journal Entries Chart
    const entriesCtx = document.getElementById('entriesChart');
    if (entriesCtx) {
        new Chart(entriesCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Sep', 'Oct', 'Nov'],
                datasets: [{
                    label: 'Journal Entries',
                    data: [8, 12, 15],
                    backgroundColor: 'rgba(122, 184, 217, 0.7)',
                    borderColor: 'rgba(74, 140, 179, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 2 },
                        grid: { color: 'rgba(168, 216, 234, 0.2)' }
                    },
                    x: {
                        grid: { color: 'rgba(168, 216, 234, 0.2)' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
}