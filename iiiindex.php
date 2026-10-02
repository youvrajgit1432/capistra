<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capistra | Financial Management Software</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="protect/css/index.css">
</head>
<body>
    <!-- Animated background elements -->
    <div class="bg-circle circle-1"></div>
    <div class="bg-circle circle-2"></div>
    <div class="bg-circle circle-3"></div>
    
    <!-- Header -->
    <header>
        <div class="logo">
            <i class="fas fa-chart-line"></i>
            <h1>Capistra</h1>
        </div>
        <nav>
            <ul>
                <li><a href="#features">Features</a></li>
                <li><a href="#solutions">Solutions</a></li>
                <li><a href="#testimonials">Testimonials</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
        </nav>
        <a href="index.php" class="cta-button" id="login-button">Login</a>
        <div class="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h2>Transform Your <span>Financial Management</span> With Our Powerful Software</h2>
            <p>Our comprehensive platform integrates employee management, financial tracking, investment analysis, and user administration into one seamless solution. Experience the future of financial management today.</p>
            <div class="hero-buttons">
                <button class="cta-button" id="demo-button">Request Demo</button>
                <button class="secondary-button">Learn More</button>
            </div>
            <div class="trust-badges">
                <p><i class="fas fa-shield-alt"></i> Secure & Compliant</p>
                <p><i class="fas fa-users"></i> Trusted by 500+ companies</p>
            </div>
        </div>
        <div class="hero-image">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Finance Dashboard" class="dashboard-image main-dash">
            <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1511&q=80" alt="Analytics Dashboard" class="dashboard-image secondary-dash">
            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1415&q=80" alt="Management Dashboard" class="dashboard-image tertiary-dash">
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-header">
            <h3>Powerful Features for Complete Financial Control</h3>
            <p>Our platform offers everything you need to manage your company's finances, employees, and investments in one place.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h4>Employee Management</h4>
                <p>Streamline HR processes with our comprehensive employee management system that handles payroll, benefits, and performance tracking.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h4>Financial Analytics</h4>
                <p>Get real-time insights into your company's financial health with customizable dashboards and detailed reporting tools.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h4>Investment Tracking</h4>
                <p>Monitor all your investments in one place with portfolio analysis, performance metrics, and automated reporting.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h4>User Administration</h4>
                <p>Control access with granular permissions and role-based authentication to keep your data secure.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <h4>Automated Invoicing</h4>
                <p>Generate and send professional invoices automatically, with payment tracking and reminders.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h4>Bank-Level Security</h4>
                <p>Your data is protected with enterprise-grade encryption and multi-factor authentication.</p>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-grid">
            <div class="stat-item">
                <h4>500+</h4>
                <p>Happy Clients</p>
            </div>
            <div class="stat-item">
                <h4>$2B+</h4>
                <p>Assets Managed</p>
            </div>
            <div class="stat-item">
                <h4>99.9%</h4>
                <p>Uptime</p>
            </div>
            <div class="stat-item">
                <h4>24/7</h4>
                <p>Support</p>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
        <div class="section-header">
            <h3>What Our Clients Say</h3>
            <p>Don't just take our word for it. Here's what our clients have to say about our platform.</p>
        </div>
        <div class="testimonial-slider">
            <div class="testimonial">
                <div class="testimonial-content">
                    "Capistra's financial management software has transformed how we handle our company finances. The investment tracking features alone have saved us countless hours each month."
                </div>
                <div class="testimonial-author">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="John Doe" class="author-avatar">
                    <div class="author-info">
                        <h5>John Doe</h5>
                        <p>CFO, TechCorp Inc.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta" id="contact">
        <div class="cta-content">
            <h3>Ready to Transform Your Financial Management?</h3>
            <p>Join hundreds of companies who trust Capistra with their financial operations. Get started today with a free demo.</p>
            <a href="index.php" class="cta-button" id="cta-login-button">Get Started Now</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Capistra</h4>
                <p>Empowering businesses with comprehensive financial management solutions since 2010.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Product</h4>
                <ul>
                    <li><a href="#">Features</a></li>
                    <li><a href="#">Pricing</a></li>
                    <li><a href="#">Integrations</a></li>
                    <li><a href="#">Updates</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Guides</a></li>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">API Docs</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">Partners</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2023 Capistra Pvt. Ltd. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        // Animate elements when they come into view
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.feature-card, .stat-item, .testimonial');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Set initial state for animated elements
        document.querySelectorAll('.feature-card, .stat-item, .testimonial').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'all 0.6s ease';
        });
        
        // Add scroll event listener
        window.addEventListener('scroll', animateOnScroll);
        
        // Trigger once on page load
        animateOnScroll();
        
        // Mobile menu toggle
        const mobileMenuButton = document.querySelector('.mobile-menu');
        const navMenu = document.querySelector('nav ul');
        
        mobileMenuButton.addEventListener('click', () => {
            navMenu.style.display = navMenu.style.display === 'flex' ? 'none' : 'flex';
        });
    </script>
</body>
</html>