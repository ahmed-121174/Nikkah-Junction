// About_Us.js - Enhanced JavaScript for Nikah Junction About Us page
// Created April 2025

// Wait for DOM to be fully loaded before executing scripts
document.addEventListener('DOMContentLoaded', () => {
    // ==================== INITIALIZATION ====================
    console.log('About Us page initialized');
    
    // Show loading overlay first
    showLoadingOverlay();
    
    // ==================== LOADING ANIMATION ====================
    function showLoadingOverlay() {
        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.classList.add('loading-overlay');
        
        // Create spinner element
        const spinner = document.createElement('div');
        spinner.classList.add('spinner');
        
        // Create loading text
        const loadingText = document.createElement('p');
        loadingText.textContent = 'Loading Nikah Junction...';
        loadingText.classList.add('loading-text');
        
        // Append elements
        overlay.appendChild(spinner);
        overlay.appendChild(loadingText);
        document.body.appendChild(overlay);
        
        // Hide overlay after content loads
        window.addEventListener('load', () => {
            setTimeout(() => {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.remove();
                    initializePageAnimations();
                }, 500);
            }, 800);
        });
        
        // Fallback to remove overlay if load event doesn't fire
        setTimeout(() => {
            if (document.body.contains(overlay)) {
                overlay.style.opacity = '0';
                setTimeout(() => {
                    overlay.remove();
                    initializePageAnimations();
                }, 500);
            }
        }, 2500);
    }
    
    // ==================== PAGE ANIMATIONS ====================
    function initializePageAnimations() {
        // Animate header elements
        animateElement('.site-title', 'fadeInDown', 300);
        animateElement('.tagline', 'fadeIn', 600);
        animateElement('.slogan', 'fadeIn', 900);
        
        // Animate navigation links
        const navLinks = document.querySelectorAll('.main-nav a');
        navLinks.forEach((link, index) => {
            animateElement(link, 'fadeInRight', 300 + (index * 100));
        });
        
        // Animate hero section elements
        animateElement('.lead-text', 'fadeIn', 800);
        animateElement('.action-buttons', 'fadeIn', 1000);
        animateElement('.hero-image', 'fadeInUp', 1200);
        
        // Animate features section with scroll-triggered animations
        setupScrollAnimations();
        
        // Add hover effects on buttons and feature cards
        setupHoverEffects();
        
        // Add navigation active state
        setActiveNavLink();
    }
    
    // ==================== ANIMATION UTILTIES ====================
    function animateElement(selector, animationClass, delay = 0) {
        const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!element) return;
        
        // Add animation classes from our custom animations
        setTimeout(() => {
            element.classList.add('animated', animationClass);
            
            // Clean up animation classes after animation completes
            element.addEventListener('animationend', () => {
                element.classList.remove('animated', animationClass);
            }, { once: true });
        }, delay);
    }
    
    function setupScrollAnimations() {
        // Get all sections and elements that should animate on scroll
        const featuresHeadings = document.querySelectorAll('.features-section h2, .features-section h5');
        const featureImages = document.querySelectorAll('.features-section img');
        const featureCards = document.querySelectorAll('.feature-card');
        
        // Set up intersection observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Animate the element when it becomes visible
                    entry.target.classList.add('animated', 'fadeInUp');
                    observer.unobserve(entry.target); // Stop observing once animated
                }
            });
        }, { threshold: 0.2 }); // Trigger when at least 20% of the element is visible
        
        // Observe all elements
        featuresHeadings.forEach(heading => observer.observe(heading));
        featureImages.forEach(img => observer.observe(img));
        featureCards.forEach(card => observer.observe(card));
    }
    
    function setupHoverEffects() {
        // Add pulse effect to buttons on hover
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseover', () => {
                button.classList.add('pulse');
            });
            
            button.addEventListener('mouseout', () => {
                button.classList.remove('pulse');
            });
        });
        
        // Add hover effect to feature cards
        const featureCards = document.querySelectorAll('.feature-card');
        featureCards.forEach(card => {
            card.addEventListener('mouseover', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseout', () => {
                card.style.transform = 'translateY(0)';
                card.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
            });
        });
    }
    
    function setActiveNavLink() {
        // Get the current page filename
        const currentPage = window.location.pathname.split('/').pop();
        
        // Highlight the current page in the navigation
        const navLinks = document.querySelectorAll('.main-nav a');
        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage) {
                link.classList.add('active-nav-link');
            }
        });
    }
    
    // ==================== INTERACTIVE FEATURES ====================
    
    // Add click effects to al clickable elements
    document.querySelectorAll('a, button').forEach(elem => {
        elem.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('ripple-effect');
            
            // Set position based on click location
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            
            this.appendChild(ripple);
            
            // Remove ripple after animation
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // Add typing effect to tagline
    const tagline = document.querySelector('.tagline');
    if (tagline) {
        const originalText = tagline.textContent;
        tagline.textContent = '';
        
        setTimeout(() => {
            let i = 0;
            const typingInterval = setInterval(() => {
                if (i < originalText.length) {
                    tagline.textContent += originalText.charAt(i);
                    i++;
                } else {
                    clearInterval(typingInterval);
                }
            }, 50);
        }, 1500);
    }
    
    // Make hero image interactive
    const heroImage = document.querySelector('.hero-image');
    if (heroImage) {
        heroImage.addEventListener('mouseover', () => {
            heroImage.style.transform = 'scale(1.05)';
            heroImage.style.transition = 'transform 0.3s ease';
        });
        
        heroImage.addEventListener('mouseout', () => {
            heroImage.style.transform = 'scale(1)';
        });
    }
    
    // Create a photo gallery effect for feature images
    const featureImages = document.querySelectorAll('.features-section img');
    featureImages.forEach(img => {
        img.addEventListener('click', () => {
            // Create lightbox overlay
            const lightbox = document.createElement('div');
            lightbox.classList.add('lightbox');
            
            // Create enlarged image
            const enlargedImg = document.createElement('img');
            enlargedImg.src = img.src;
            enlargedImg.alt = img.alt;
            
            // Create close button
            const closeBtn = document.createElement('span');
            closeBtn.innerHTML = '&times;';
            closeBtn.classList.add('lightbox-close');
            
            lightbox.appendChild(enlargedImg);
            lightbox.appendChild(closeBtn);
            document.body.appendChild(lightbox);
            
            // Fade in the lightbox
            setTimeout(() => {
                lightbox.style.opacity = '1';
            }, 10);
            
            // Close lightbox on click
            lightbox.addEventListener('click', () => {
                lightbox.style.opacity = '0';
                setTimeout(() => {
                    lightbox.remove();
                }, 300);
            });
        });
    });
    
    // Add scroll to top button
    createScrollToTopButton();
    
    function createScrollToTopButton() {
        const scrollBtn = document.createElement('button');
        scrollBtn.innerHTML = '&uarr;';
        scrollBtn.classList.add('scroll-to-top');
        document.body.appendChild(scrollBtn);
        
        // Show/hide button based on scroll position
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollBtn.classList.add('visible');
            } else {
                scrollBtn.classList.remove('visible');
            }
        });
        
        // Scroll to top on click
        scrollBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add dynamic CSS for animations
    addAnimationStyles();
    
    function addAnimationStyles() {
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            /* Loading Animation */
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 245, 225, 0.9);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                transition: opacity 0.5s ease;
            }
            
            .spinner {
                width: 60px;
                height: 60px;
                border: 6px solid rgba(139, 69, 19, 0.3);
                border-radius: 50%;
                border-top-color: #8B4513;
                animation: spin 1s ease-in-out infinite;
                margin-bottom: 20px;
            }
            
            .loading-text {
                color: #8B4513;
                font-size: 18px;
                font-weight: bold;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            /* Animation Classes */
            .animated {
                animation-duration: 0.8s;
                animation-fill-mode: both;
            }
            
            .fadeIn {
                animation-name: fadeIn;
            }
            
            .fadeInDown {
                animation-name: fadeInDown;
            }
            
            .fadeInUp {
                animation-name: fadeInUp;
            }
            
            .fadeInRight {
                animation-name: fadeInRight;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translate3d(0, -30px, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translate3d(0, 30px, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }
            
            @keyframes fadeInRight {
                from {
                    opacity: 0;
                    transform: translate3d(30px, 0, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }
            
            /* Button Animation */
            .pulse {
                animation: pulse 0.5s;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            /* Ripple Effect */
            .ripple-effect {
                position: absolute;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.7);
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            /* Feature Card Transitions */
            .feature-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            
            /* Active Navigation Link */
            .active-nav-link {
                background-color: var(--secondary-color);
                color: var(--dark-color) !important;
            }
            
            /* Lightbox */
            .lightbox {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .lightbox img {
                max-width: 90%;
                max-height: 90%;
                border: 3px solid white;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            }
            
            .lightbox-close {
                position: absolute;
                top: 20px;
                right: 20px;
                color: white;
                font-size: 40px;
                cursor: pointer;
            }
            
            /* Scroll to Top Button */
            .scroll-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 40px;
                height: 40px;
                background-color: var(--primary-color);
                color: white;
                border-radius: 50%;
                display: flex;
                justify-content: center;
                align-items: center;
                cursor: pointer;
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.3s ease, transform 0.3s ease;
                z-index: 99;
            }
            
            .scroll-to-top.visible {
                opacity: 1;
                transform: translateY(0);
            }
        `;
        document.head.appendChild(styleElement);
    }
});