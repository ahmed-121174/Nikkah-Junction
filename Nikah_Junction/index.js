document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeAnimations();
    initializeNavigation();
    initializeFeatureCards();
    initializeHeroSection();
    initializeNotifications();
    initializeLocalStorage();
    
    // Add page-specific functionality
    if (document.querySelector('.features-section')) {
        initializeFeatureShowcase();
    }
});

/**
 * Smooth scrolling and page transition animations
 */
function initializeAnimations() {
    // Add slide-in animation to all major sections
    const sections = document.querySelectorAll('section');
    
    // Create Intersection Observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeIn');
                setTimeout(() => {
                    entry.target.classList.add('animate-slideIn');
                }, 200);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });
    
    // Observe each section for scroll animation
    sections.forEach(section => {
        observer.observe(section);
    });
    
    // Add smooth scroll to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
}

/**
 * Enhanced navigation functionality
 */
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.main-nav a');
    
    // Add active state to current page link
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage()) {
            link.classList.add('active');
        }
        
        // Add hover effect
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        // Add click animation
        link.addEventListener('click', function(e) {
            // Only animate internal links (not external)
            if (this.getAttribute('href').startsWith('#')) return;
            
            e.preventDefault();
            const originalBackground = this.style.backgroundColor;
            const originalColor = this.style.color;
            
            this.style.backgroundColor = 'var(--accent-color)';
            this.style.color = 'white';
            
            // Create a ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('nav-ripple');
            this.appendChild(ripple);
            
            // Navigate after animation completes
            setTimeout(() => {
                window.location.href = this.getAttribute('href');
            }, 300);
        });
    });
    
    // Sticky navigation on scroll
    let lastScrollTop = 0;
    const header = document.querySelector('.header');
    const nav = document.querySelector('.main-nav');
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > header.offsetHeight) {
            nav.classList.add('sticky-nav');
            nav.style.position = 'fixed';
            nav.style.top = '0';
            nav.style.left = '0';
            nav.style.right = '0';
            nav.style.zIndex = '1000';
            nav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
            document.body.style.paddingTop = nav.offsetHeight + 'px';
        } else {
            nav.classList.remove('sticky-nav');
            nav.style.position = 'static';
            nav.style.boxShadow = 'none';
            document.body.style.paddingTop = '0';
        }
        
        lastScrollTop = scrollTop;
    });
    
    // Helper function to get current page
    function currentPage() {
        const path = window.location.pathname;
        const page = path.split("/").pop();
        return page || 'home.html';
    }
}

/**
 * Interactive feature cards with hover effects and animations
 */
function initializeFeatureCards() {
    const featureCards = document.querySelectorAll('.feature-card');
    
    featureCards.forEach(card => {
        // Add hover effect
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
            this.style.transition = 'transform 0.3s ease, box-shadow 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--box-shadow)';
        });
        
        // Add click interaction
        card.addEventListener('click', function() {
            // Pulse animation on click
            this.classList.add('pulse-animation');
            setTimeout(() => {
                this.classList.remove('pulse-animation');
            }, 500);
            
            // Toggle expanded state for more information
            const content = this.querySelector('.feature-card-content');
            const expandedContent = this.querySelector('.feature-expanded-content');
            
            if (expandedContent) {
                if (expandedContent.classList.contains('hidden')) {
                    expandedContent.classList.remove('hidden');
                    expandedContent.classList.add('animate-fadeIn');
                } else {
                    expandedContent.classList.add('hidden');
                }
            }
        });
    });
    
    // Add CSS for pulse animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulseEffect {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .pulse-animation {
            animation: pulseEffect 0.5s ease;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Interactive hero section with dynamic content
 */
function initializeHeroSection() {
    const heroSection = document.querySelector('.hero-section');
    if (!heroSection) return;
    
    const heroImage = heroSection.querySelector('.hero-image');
    const actionButtons = heroSection.querySelector('.action-buttons');
    
    
    // Add floating animation to action buttons
    if (actionButtons) {
        const buttons = actionButtons.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
                this.style.transition = 'all 0.3s ease';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'var(--box-shadow)';
            });
        });
    }
    
    // Add typing effect to tagline
    const tagline = heroSection.querySelector('.tagline');
    if (tagline) {
        const originalText = tagline.textContent;
        tagline.textContent = '';
        
        // Type out the text character by character
        let charIndex = 0;
        function typeText() {
            if (charIndex < originalText.length) {
                tagline.textContent += originalText.charAt(charIndex);
                charIndex++;
                setTimeout(typeText, 50);
            }
        }
        
        // Start typing effect after a short delay
        setTimeout(typeText, 1000);
    }
}

/**
 * Feature showcase with interactive preview
 */
function initializeFeatureShowcase() {
    const features = document.querySelectorAll('.features-section .feature-card');
    
    features.forEach(feature => {
        // Create an "Learn more" button
        const learnMoreBtn = document.createElement('button');
        learnMoreBtn.textContent = 'Learn More';
        learnMoreBtn.classList.add('btn', 'btn-small', 'mt-1');
        
        // Create expanded content section
        const expandedContent = document.createElement('div');
        expandedContent.classList.add('feature-expanded-content', 'hidden');
        
        // Populate expanded content based on feature title
        const featureTitle = feature.querySelector('h2').textContent;
        let additionalContent = '';
        
        if (featureTitle.includes('perfect match')) {
            additionalContent = `
                <p>Our advanced matching algorithm considers over 100 compatibility factors including:</p>
                <ul>
                    <li>Religious values and practices</li>
                    <li>Educational background</li>
                    <li>Family values</li>
                    <li>Personality traits</li>
                    <li>Life goals</li>
                </ul>
                <p>We pride ourselves on creating meaningful connections that last.</p>
            `;
        } else if (featureTitle.includes('100% Verified Rishta')) {
            additionalContent = `
                <p>Our verification process includes:</p>
                <ul>
                    <li>Phone number verification</li>
                    <li>ID document checks</li>
                    <li>Personal interviews</li>
                    <li>Reference checks</li>
                </ul>
                <p>Your safety is our top priority.</p>
            `;
        } else if (featureTitle.includes('Get Verified')) {
            additionalContent = `
                <p>Becoming verified is quick and easy:</p>
                <ol>
                    <li>Create your profile</li>
                    <li>Upload your identification</li>
                    <li>Schedule a brief verification call</li>
                    <li>Receive your verification badge within 24 hours</li>
                </ol>
                <p>Verified profiles receive 3x more connections!</p>
            `;
        }
        expandedContent.innerHTML = additionalContent;
        
        // Add elements to the DOM
        feature.querySelector('.feature-card-content').appendChild(learnMoreBtn);
        feature.querySelector('.feature-card-content').appendChild(expandedContent);
        
        // Add event listener to toggle expanded content
        learnMoreBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent the card click event
            
            if (expandedContent.classList.contains('hidden')) {
                expandedContent.classList.remove('hidden');
                expandedContent.classList.add('animate-fadeIn');
                learnMoreBtn.textContent = 'Show Less';
            } else {
                expandedContent.classList.add('hidden');
                learnMoreBtn.textContent = 'Learn More';
            }
        });
    });
}


/**
 * Smart notifications system
 */
function initializeNotifications() {
    // Check if user is returning visitor
    if (localStorage.getItem('returningUser')) {
        // Don't show welcome notification for returning users
    } else {
        // Show welcome notification for new visitors
        showNotification(
            'Welcome to Nikkah Junction!',
            'Create your profile today and find your perfect match.',
            5000
        );
        
        // Mark as returning user
        localStorage.setItem('returningUser', 'true');
    }
    
    // Check if user came from a specific source
    const urlParams = new URLSearchParams(window.location.search);
    const source = urlParams.get('source');
    
    if (source === 'ad') {
        showNotification(
            'Special Offer!',
            'Use code WELCOME50 for 50% off on your premium membership.',
            8000
        );
    }
    
    // Show time-specific greetings
    const currentHour = new Date().getHours();
    let greeting = '';
    
    if (currentHour >= 5 && currentHour < 12) {
        greeting = 'Good morning!';
    } else if (currentHour >= 12 && currentHour < 18) {
        greeting = 'Good afternoon!';
    } else {
        greeting = 'Good evening!';
    }
    
    // Create notification system
    function showNotification(title, message, duration = 4000) {
        // Create notification element
        const notification = document.createElement('div');
        notification.classList.add('notification');
        notification.innerHTML = `
            <div class="notification-header">
                <strong>${title}</strong>
                <button class="close-notification">×</button>
            </div>
            <div class="notification-body">
                <p>${message}</p>
            </div>
        `;
        
        // Add styles
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.backgroundColor = 'white';
        notification.style.borderRadius = 'var(--border-radius)';
        notification.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
        notification.style.padding = '15px';
        notification.style.zIndex = '1001';
        notification.style.maxWidth = '350px';
        notification.style.transition = 'all 0.5s ease';
        notification.style.transform = 'translateX(400px)';
        notification.style.opacity = '0';
        
        // Add to DOM
        document.body.appendChild(notification);
        
        // Animation
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        }, 100);
        
        // Close button functionality
        notification.querySelector('.close-notification').addEventListener('click', function() {
            notification.style.transform = 'translateX(400px)';
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 500);
        });
        
        // Auto dismiss
        if (duration) {
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, duration);
        }
    }
    
    // Additional styles for notifications
    const style = document.createElement('style');
    style.textContent = `
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .close-notification {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: var(--primary-color);
        }
        
        .notification-body p {
            margin: 0;
        }
    `;
    document.head.appendChild(style);
}

/**
 * User preference and interaction storage
 */
function initializeLocalStorage() {
    // Check if first visit
    if (!localStorage.getItem('firstVisit')) {
        localStorage.setItem('firstVisit', Date.now());
    }
    
    // Track page visits
    let pageVisits = parseInt(localStorage.getItem('pageVisits') || '0');
    localStorage.setItem('pageVisits', pageVisits + 1);
    
    // Track features clicked
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('click', function() {
            const featureTitle = this.querySelector('h2').textContent;
            const featureClicks = JSON.parse(localStorage.getItem('featureClicks') || '{}');
            featureClicks[featureTitle] = (featureClicks[featureTitle] || 0) + 1;
            localStorage.setItem('featureClicks', JSON.stringify(featureClicks));
        });
    });
    
    // Save form inputs as draft
    document.querySelectorAll('input, textarea').forEach(input => {
        // Load saved draft values
        const savedValue = localStorage.getItem(`draft_${input.name}`);
        if (savedValue && input.type !== 'password') {
            input.value = savedValue;
        }
        
        // Save draft values on input
        input.addEventListener('input', function() {
            if (this.type !== 'password') {
                localStorage.setItem(`draft_${this.name}`, this.value);
            }
        });
    });
}

/**
 * Form validation and enhancement
 */
function initializeForms() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Basic validation
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('input-error');
                    
                    // Add error message
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('error-message');
                    errorMsg.textContent = 'This field is required';
                    
                    // Check if error message already exists
                    const existingError = field.parentNode.querySelector('.error-message');
                    if (!existingError) {
                        field.parentNode.appendChild(errorMsg);
                    }
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !emailRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('input-error');
                    
                    // Add error message
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('error-message');
                    errorMsg.textContent = 'Please enter a valid email address';
                    
                    // Check if error message already exists
                    const existingError = field.parentNode.querySelector('.error-message');
                    if (!existingError) {
                        field.parentNode.appendChild(errorMsg);
                    }
                }
            });
            
            // Phone validation
            const phoneFields = form.querySelectorAll('input[type="tel"]');
            phoneFields.forEach(field => {
                const phoneRegex = /^\+?[\d\s()-]{10,15}$/;
                if (field.value && !phoneRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('input-error');
                    
                    // Add error message
                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('error-message');
                    errorMsg.textContent = 'Please enter a valid phone number';
                    
                    // Check if error message already exists
                    const existingError = field.parentNode.querySelector('.error-message');
                    if (!existingError) {
                        field.parentNode.appendChild(errorMsg);
                    }
                }
            });
            
            // Password strength validation
            const passwordFields = form.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                if (field.value) {
                    const hasMinLength = field.value.length >= 8;
                    const hasUpperCase = /[A-Z]/.test(field.value);
                    const hasLowerCase = /[a-z]/.test(field.value);
                    const hasNumbers = /\d/.test(field.value);
                    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(field.value);
                    
                    if (!(hasMinLength && (hasUpperCase || hasLowerCase) && (hasNumbers || hasSpecialChar))) {
                        isValid = false;
                        field.classList.add('input-error');
                        
                        // Add error message
                        const errorMsg = document.createElement('p');
                        errorMsg.classList.add('error-message');
                        errorMsg.textContent = 'Password must be at least 8 characters and include a mix of letters, numbers, or special characters';
                        
                        // Check if error message already exists
                        const existingError = field.parentNode.querySelector('.error-message');
                        if (!existingError) {
                            field.parentNode.appendChild(errorMsg);
                        }
                    }
                }
            });
            
            // Prevent form submission if invalid
            if (!isValid) {
                e.preventDefault();
                
                // Show form error message
                const formError = document.createElement('div');
                formError.classList.add('form-error');
                formError.textContent = 'Please correct the errors and try again.';
                formError.style.color = 'var(--error-color)';
                formError.style.textAlign = 'center';
                formError.style.padding = '10px';
                formError.style.marginTop = '10px';
                
                // Check if error message already exists
                const existingFormError = form.querySelector('.form-error');
                if (existingFormError) {
                    existingFormError.remove();
                }
                
                form.appendChild(formError);
                
                // Scroll to first error
                const firstError = form.querySelector('.input-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            } else {
                // Success handling - could add AJAX form submission here
                const formSuccess = document.createElement('div');
                formSuccess.classList.add('form-success');
                formSuccess.textContent = 'Form submitted successfully!';
                formSuccess.style.color = 'var(--success-color)';
                formSuccess.style.textAlign = 'center';
                formSuccess.style.padding = '10px';
                
                // Check if success message already exists
                const existingFormSuccess = form.querySelector('.form-success');
                if (existingFormSuccess) {
                    existingFormSuccess.remove();
                }
                
                form.appendChild(formSuccess);
            }
        });
        
        // Real-time validation and feedback
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('input', function() {
                // Remove error styling when user starts typing
                this.classList.remove('input-error');
                
                // Remove error message
                const existingError = this.parentNode.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
            });
            
            // Add feedback for password strength
            if (field.type === 'password') {
                field.addEventListener('input', function() {
                    // Create or find strength meter
                    let strengthMeter = this.parentNode.querySelector('.password-strength');
                    if (!strengthMeter) {
                        strengthMeter = document.createElement('div');
                        strengthMeter.classList.add('password-strength');
                        strengthMeter.style.height = '5px';
                        strengthMeter.style.marginTop = '5px';
                        strengthMeter.style.marginBottom = '10px';
                        strengthMeter.style.transition = 'all 0.3s ease';
                        this.parentNode.appendChild(strengthMeter);
                    }
                    
                    // Calculate password strength
                    const value = this.value;
                    let strength = 0;
                    
                    if (value.length >= 8) strength += 20;
                    if (value.length >= 12) strength += 10;
                    if (/[A-Z]/.test(value)) strength += 20;
                    if (/[a-z]/.test(value)) strength += 15;
                    if (/\d/.test(value)) strength += 15;
                    if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) strength += 20;
                    
                    // Update strength meter
                    let backgroundColor;
                    let strengthText;
                    
                    if (strength < 30) {
                        backgroundColor = '#ff4d4d'; // Red
                        strengthText = 'Weak';
                    } else if (strength < 60) {
                        backgroundColor = '#ffb84d'; // Orange
                        strengthText = 'Moderate';
                    } else if (strength < 80) {
                        backgroundColor = '#ffff4d'; // Yellow
                        strengthText = 'Good';
                    } else {
                        backgroundColor = '#4CAF50'; // Green
                        strengthText = 'Strong';
                    }
                    
                    strengthMeter.style.width = strength + '%';
                    strengthMeter.style.backgroundColor = backgroundColor;
                    
                    // Add strength text
                    let strengthTextElem = this.parentNode.querySelector('.password-strength-text');
                    if (!strengthTextElem) {
                        strengthTextElem = document.createElement('small');
                        strengthTextElem.classList.add('password-strength-text');
                        this.parentNode.appendChild(strengthTextElem);
                    }
                    
                    strengthTextElem.textContent = strengthText + ' Password';
                    strengthTextElem.style.color = backgroundColor;
                });
            }
        });
        
        // Add CSS for form validation
        const style = document.createElement('style');
        style.textContent = `
            .input-error {
                border-color: var(--error-color) !important;
                box-shadow: 0 0 0 2px rgba(244, 67, 54, 0.25);
            }
            
            .error-message {
                color: var(--error-color);
                font-size: 0.8em;
                margin-top: 5px;
                margin-bottom: 10px;
            }
        `;
        document.head.appendChild(style);
    });
}

/**
 * Testimonials carousel
 */
function initializeTestimonials() {
    // Create testimonials data
    const testimonials = [
        {
            name: "Danish & Aiza",
            image: "couple1.png",
            testimonial: "Alhamdulillah, we found each other on Nikkah Junction and got married within 6 months. The verification process gave us so much confidence that we were talking to genuine people.",
            rating: 5
        },
        {
            name: "Shoaib & Sana",
            image: "couple2.png",
            testimonial: "The compatibility meter was spot on! We were a 92% match and after meeting, we knew why. Nikkah Junction made it easy to find someone who shares the same values and life goals.",
            rating: 5
        },
        {
            name: "Yousaf Raza Gilani & Sahr Khosa",
            image: "couple3.png",
            testimonial: "After trying several matrimonial services, Nikkah Junction was the only one that delivered quality matches. The AI recommendations were surprisingly accurate!",
            rating: 4
        }
    ];

    // Find container element or create one
    let testimonialsSection = document.querySelector('.testimonials-section');
    
    if (!testimonialsSection) {
        testimonialsSection = document.createElement('section');
        testimonialsSection.classList.add('testimonials-section');
        testimonialsSection.innerHTML = `<h2 class="text-center">Success Stories</h2>`;
        
        // Insert before footer
        const footer = document.querySelector('.footer');
        if (footer) {
            document.body.insertBefore(testimonialsSection, footer);
        } else {
            document.body.appendChild(testimonialsSection);
        }
    }

    // Create carousel container
    const carouselContainer = document.createElement('div');
    carouselContainer.classList.add('testimonial-carousel');
    testimonialsSection.appendChild(carouselContainer);
    
    // Add testimonials to carousel
    testimonials.forEach((testimonial, index) => {
        const testimonialCard = document.createElement('div');
        testimonialCard.classList.add('testimonial-card');
        testimonialCard.dataset.index = index;
        
        // Generate stars based on rating
        let stars = '';
        for (let i = 0; i < 5; i++) {
            if (i < testimonial.rating) {
                stars += '<span class="star filled">★</span>';
            } else {
                stars += '<span class="star">☆</span>';
            }
        }
        
        testimonialCard.innerHTML = `
            <div class="testimonial-image">
                <img src="${testimonial.image}" alt="${testimonial.name}" onerror="this.src='placeholder.jpg'">
            </div>
            <div class="testimonial-content">
                <p class="testimonial-text">"${testimonial.testimonial}"</p>
                <div class="testimonial-rating">${stars}</div>
                <p class="testimonial-name">- ${testimonial.name}</p>
            </div>
        `;
        
        carouselContainer.appendChild(testimonialCard);
    });
    
    // Add navigation arrows
    const prevButton = document.createElement('button');
    prevButton.classList.add('carousel-nav', 'prev-button');
    prevButton.innerHTML = '❮';
    
    const nextButton = document.createElement('button');
    nextButton.classList.add('carousel-nav', 'next-button');
    nextButton.innerHTML = '❯';
    
    testimonialsSection.appendChild(prevButton);
    testimonialsSection.appendChild(nextButton);
    
    // Add styles for testimonials
    const style = document.createElement('style');
    style.textContent = `
        .testimonials-section {
            margin: 60px 0;
            position: relative;
            padding: 20px 0;
            overflow: hidden;
        }
        
        .testimonial-carousel {
            display: flex;
            transition: transform 0.5s ease;
        }
        
        .testimonial-card {
            min-width: 100%;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin: 0 10px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .testimonial-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20px;
            flex-shrink: 0;
            border: 3px solid var(--secondary-color);
        }
        
        .testimonial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .testimonial-content {
            flex: 1;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 15px;
        }
        
        .testimonial-rating {
            margin-bottom: 10px;
            color: var(--secondary-color);
            font-size: 1.2em;
        }
        
        .testimonial-name {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .star {
            color: #ccc;
        }
        
        .star.filled {
            color: var(--secondary-color);
        }
        
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: var(--primary-color);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 1.5em;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            z-index: 10;
        }
        
        .carousel-nav:hover {
            opacity: 1;
        }
        
        .prev-button {
            left: 10px;
        }
        
        .next-button {
            right: 10px;
        }
        
        @media screen and (max-width: 768px) {
            .testimonial-card {
                flex-direction: column;
                text-align: center;
            }
            
            .testimonial-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Carousel functionality
    let currentSlide = 0;
    const slides = carouselContainer.querySelectorAll('.testimonial-card');
    
    function showSlide(index) {
        if (index < 0) {
            currentSlide = slides.length - 1;
        } else if (index >= slides.length) {
            currentSlide = 0;
        } else {
            currentSlide = index;
        }
        
        const offset = -currentSlide * 100;
        carouselContainer.style.transform = `translateX(${offset}%)`;
    }
    
    // Initialize carousel
    showSlide(0);
    
    // Add event listeners for navigation
    prevButton.addEventListener('click', () => {
        showSlide(currentSlide - 1);
    });
    
    nextButton.addEventListener('click', () => {
        showSlide(currentSlide + 1);
    });
    
    
    // Enable touch swipe for mobile devices
    let touchStartX = 0;
    let touchEndX = 0;
    
    carouselContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    
    carouselContainer.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, { passive: true });
    
    function handleSwipe() {
        if (touchEndX < touchStartX) {
            // Swipe left
            showSlide(currentSlide + 1);
        } else if (touchEndX > touchStartX) {
            // Swipe right
            showSlide(currentSlide - 1);
        }
    }
}

/**
 * Dynamic content loading and caching
 */
function initializeContentLoading() {
    // Preload common pages to make navigation feel faster
    function preloadPage(url) {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);
    }
    
    // Preload common navigation links
    const commonPages = ['About_Us.html', 'faqs.html', 'Contact_Us.html', 'login.html', 'signup.html'];
    commonPages.forEach(page => {
        preloadPage(page);
    });
    
    // Image lazy loading
    document.addEventListener('DOMContentLoaded', function() {
        const lazyImages = [].slice.call(document.querySelectorAll('img.lazy'));
        
        if ('IntersectionObserver' in window) {
            const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const lazyImage = entry.target;
                        lazyImage.src = lazyImage.dataset.src;
                        lazyImage.classList.remove('lazy');
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            });
            
            lazyImages.forEach(function(lazyImage) {
                lazyImageObserver.observe(lazyImage);
            });
        } else {
            // Fallback for browsers that don't support IntersectionObserver
            let active = false;
            
            const lazyLoad = function() {
                if (active === false) {
                    active = true;
                    
                    setTimeout(function() {
                        lazyImages.forEach(function(lazyImage) {
                            if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== 'none') {
                                lazyImage.src = lazyImage.dataset.src;
                                lazyImage.classList.remove('lazy');
                                
                                lazyImages = lazyImages.filter(function(image) {
                                    return image !== lazyImage;
                                });
                                
                                if (lazyImages.length === 0) {
                                    document.removeEventListener('scroll', lazyLoad);
                                    window.removeEventListener('resize', lazyLoad);
                                    window.removeEventListener('orientationchange', lazyLoad);
                                }
                            }
                        });
                        
                        active = false;
                    }, 200);
                }
            };
            
            document.addEventListener('scroll', lazyLoad);
            window.addEventListener('resize', lazyLoad);
            window.addEventListener('orientationchange', lazyLoad);
        }
    });
}

/**
 * Analytics and event tracking
 */
function initializeAnalytics() {
    // Simple event tracking function
    function trackEvent(category, action, label = null) {
        // In a real implementation, this would send data to an analytics service
        console.log(`Analytics Event: ${category} - ${action}${label ? ' - ' + label : ''}`);
        
        // Store events locally for debugging
        const events = JSON.parse(localStorage.getItem('trackingEvents') || '[]');
        events.push({
            category,
            action,
            label,
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('trackingEvents', JSON.stringify(events));
    }
    
    // Track page view
    trackEvent('Page', 'View', window.location.pathname);
    
    // Track user interactions
    document.addEventListener('click', function(e) {
        // Track button clicks
        if (e.target.tagName === 'BUTTON' || e.target.classList.contains('btn')) {
            trackEvent('Interaction', 'Button Click', e.target.textContent.trim());
        }
        
        // Track navigation
        if (e.target.tagName === 'A') {
            trackEvent('Navigation', 'Link Click', e.target.textContent.trim() || e.target.href);
        }
        
        // Track feature card clicks
        if (e.target.closest('.feature-card')) {
            const card = e.target.closest('.feature-card');
            const title = card.querySelector('h2').textContent;
            trackEvent('Engagement', 'Feature Click', title);
        }
    });
    
    // Track scroll depth
    let scrollDepthMarkers = [25, 50, 75, 100];
    let scrollDepthTracked = [];
    
    window.addEventListener('scroll', function() {
        const scrollPercent = Math.round((window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100);
        
        scrollDepthMarkers.forEach(marker => {
            if (scrollPercent >= marker && !scrollDepthTracked.includes(marker)) {
                trackEvent('Scroll', 'Depth Reached', `${marker}%`);
                scrollDepthTracked.push(marker);
            }
        });
    });
    
    // Track time spent
    let startTime = Date.now();
    let timeIntervals = [30, 60, 120, 300]; // in seconds
    let timeTracked = [];
    
    setInterval(() => {
        const timeSpent = Math.floor((Date.now() - startTime) / 1000);
        
        timeIntervals.forEach(interval => {
            if (timeSpent >= interval && !timeTracked.includes(interval)) {
                trackEvent('Engagement', 'Time Spent', `${interval} seconds`);
                timeTracked.push(interval);
            }
        });
    }, 1000);
    
    // Track form interactions
    document.querySelectorAll('form').forEach(form => {
        // Track form submissions
        form.addEventListener('submit', function(e) {
            trackEvent('Form', 'Submit', form.getAttribute('name') || form.id || 'Unknown Form');
        });
        
        // Track form field focus
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('focus', function() {
                trackEvent('Form', 'Field Focus', field.name || field.id);
            });
        });
    });
}

// Initialize all features when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Call initializations
    initializeTestimonials();
    initializeForms();
    initializeContentLoading();
    initializeAnalytics();
    
    // Add mobile menu for responsive design
    if (window.innerWidth <= 768) {
        initializeMobileMenu();
    }
    
    // Initialize any page-specific functionality
    initializePageSpecific();
});

/**
 * Mobile menu for responsive design
 */
function initializeMobileMenu() {
    const mainNav = document.querySelector('.main-nav');
    if (!mainNav) return;
    
    // Create mobile menu toggle button
    const menuToggle = document.createElement('button');
    menuToggle.classList.add('mobile-menu-toggle');
    menuToggle.innerHTML = `
        <span class="menu-icon">
            <span class="menu-icon-bar"></span>
            <span class="menu-icon-bar"></span>
            <span class="menu-icon-bar"></span>
        </span>
    `;
    
    // Insert toggle button before nav
    mainNav.parentNode.insertBefore(menuToggle, mainNav);
    
    // Add styles for mobile menu
    const style = document.createElement('style');
    style.textContent = `
        @media screen and (max-width: 768px) {
            .main-nav {
                display: none;
                width: 100%;
                padding: 0;
                transition: all 0.3s ease;
            }
            
            .main-nav.open {
                display: flex;
                flex-direction: column;
            }
            
            .mobile-menu-toggle {
                display: block;
                background: none;
                border: none;
                cursor: pointer;
                padding: 10px;
                align-self: flex-end;
                margin-bottom: 15px;
            }
            
            .menu-icon {
                display: inline-block;
                position: relative;
                width: 30px;
                height: 24px;
            }
            
            .menu-icon-bar {
                display: block;
                position: absolute;
                height: 3px;
                width: 100%;
                background: var(--primary-color);
                opacity: 1;
                left: 0;
                transform: rotate(0deg);
                transition: all 0.3s ease;
            }
            
            .menu-icon-bar:nth-child(1) {
                top: 0;
            }
            
            .menu-icon-bar:nth-child(2) {
                top: 10px;
            }
            
            .menu-icon-bar:nth-child(3) {
                top: 20px;
            }
            
            .menu-icon.open .menu-icon-bar:nth-child(1) {
                top: 10px;
                transform: rotate(135deg);
            }
            
            .menu-icon.open .menu-icon-bar:nth-child(2) {
                opacity: 0;
                left: -60px;
            }
            
            .menu-icon.open .menu-icon-bar:nth-child(3) {
                top: 10px;
                transform: rotate(-135deg);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Toggle menu on button click
    menuToggle.addEventListener('click', function() {
        mainNav.classList.toggle('open');
        this.querySelector('.menu-icon').classList.toggle('open');
    });
}

/**
 * Page-specific functionality
 */
function initializePageSpecific() {
    // Get current page
    const path = window.location.pathname;
    const currentPage = path.split("/").pop() || 'home.html';
    
    // Home page-specific functionality
    if (currentPage === 'home.html' || currentPage === '') {
        // Add success counter animation
        const counterSection = document.createElement('section');
        counterSection.classList.add('counter-section');
        counterSection.innerHTML = `
            <div class="counter-container">
                <div class="counter-item">
                    <h3>50,000+</h3>
                    <p>Verified Users</p>
                </div>
                <div class="counter-item">
                    <h3>5,000+</h3>
                    <p>Successful Matches</p>
                </div>
                <div class="counter-item">
                    <h3>98%</h3>
                    <p>Satisfaction Rate</p>
                </div>
            </div>
        `;
        
        // Insert before testimonials
        const testimonials = document.querySelector('.testimonials-section');
        if (testimonials) {
            document.body.insertBefore(counterSection, testimonials);
        } else {
            const footer = document.querySelector('.footer');
            document.body.insertBefore(counterSection, footer);
        }
        
        // Add styles for counter section
        const style = document.createElement('style');
        style.textContent = `
            .counter-section {
                background-color: var(--primary-color);
                padding: 40px 20px;
                margin: 40px 0;
                border-radius: var(--border-radius);
                color: white;
            }
            
            .counter-container {
                display: flex;
                justify-content: space-around;
                text-align: center;
                flex-wrap: wrap;
            }
            
            .counter-item {
                padding: 20px;
            }
            
            .counter-item h3 {
                font-size: 2.5em;
                color: var(--secondary-color);
                margin-bottom: 10px;
            }
            
            .counter-item p {
                font-size: 1.2em;
                margin: 0;
            }
            
            @media screen and (max-width: 768px) {
                .counter-container {
                    flex-direction: column;
                }
                
                .counter-item {
                    margin-bottom: 20px;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Animate counter numbers
        const counterItems = document.querySelectorAll('.counter-item h3');
        
        function animateValue(element, start, end, duration) {
            const range = end - start;
            const startTime = performance.now();
            const suffix = element.textContent.replace(/[0-9,+%]/g, '');
            
            function updateValue(timestamp) {
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                let value = Math.floor(progress * range + start);
                
                // Format the number with commas
                value = value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                
                element.textContent = value + suffix;
                
                if (progress < 1) {
                    requestAnimationFrame(updateValue);
                }
            }
            
            requestAnimationFrame(updateValue);
        }
        
        // Create intersection observer for counter animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    counterItems.forEach(counter => {
                        const value = parseInt(counter.textContent.replace(/[^0-9]/g, ''));
                        animateValue(counter, 0, value, 2000);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        // Observe counter section
        observer.observe(counterSection);
    }
}