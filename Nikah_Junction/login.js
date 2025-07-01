document.addEventListener('DOMContentLoaded', function() {
    // References to key elements
    const formContainer = document.querySelector('.form-container');
    const loginForm = document.querySelector('.login-form');
    const formGroups = document.querySelectorAll('.form-group');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const submitButton = document.querySelector('button[type="submit"]');
    const registerButton = document.querySelector('.btn-secondary');
    const forgotPasswordLink = document.querySelector('a[href="forgot_password.php"]');

    // Add staggered animation to form fields
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        group.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        
        // Stagger the animations
        setTimeout(() => {
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, 200 + (index * 150));
    });

    // Add focus/blur effects for form inputs
    const addInputEffects = (inputElement) => {
        inputElement.addEventListener('focus', function() {
            this.parentElement.style.transition = 'transform 0.3s ease';
            this.parentElement.style.transform = 'scale(1.02)';
            this.style.transition = 'box-shadow 0.3s ease';
            this.style.boxShadow = '0 0 10px rgba(139, 69, 19, 0.3)';
        });

        inputElement.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    };

    addInputEffects(emailInput);
    addInputEffects(passwordInput);

    // Animate submit button on hover
    submitButton.addEventListener('mouseenter', function() {
        this.style.transition = 'transform 0.3s ease, background-color 0.3s ease, color 0.3s ease';
        this.style.transform = 'translateY(-2px)';
    });

    submitButton.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });

    // Add pulse animation to register button
    registerButton.classList.add('register-btn-pulse');
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .register-btn-pulse {
            animation: pulse 2s infinite;
        }
        .register-btn-pulse:hover {
            animation: none;
        }
    `;
    document.head.appendChild(style);

    // Add subtle transition to forgot password link
    forgotPasswordLink.style.transition = 'color 0.3s ease, text-decoration 0.3s ease';
    forgotPasswordLink.addEventListener('mouseenter', function() {
        this.style.color = '#6b340e';
    });
    forgotPasswordLink.addEventListener('mouseleave', function() {
        this.style.color = '';
    });

    // Form submission animation
    loginForm.addEventListener('submit', function(e) {
        // Don't prevent default as we want the form to submit normally
        // Just add a visual effect
        formContainer.style.transition = 'transform 0.4s ease, opacity 0.4s ease';
        formContainer.style.transform = 'scale(0.95)';
        formContainer.style.opacity = '0.8';
    });

    // Page load complete animation
    window.addEventListener('load', function() {
        document.body.classList.add('page-loaded');
        const loadStyle = document.createElement('style');
        loadStyle.textContent = `
            .page-loaded {
                animation: fadeInPage 1s ease forwards;
            }
            @keyframes fadeInPage {
                from { opacity: 0.7; }
                to { opacity: 1; }
            }
        `;
        document.head.appendChild(loadStyle);
    });

    // Add subtle background effect
    const backgroundEffect = document.createElement('style');
    backgroundEffect.textContent = `
        body {
            background-image: linear-gradient(45deg, var(--light-color) 25%, transparent 25%, 
                transparent 50%, var(--light-color) 50%, var(--light-color) 75%, 
                transparent 75%, transparent);
            background-size: 100px 100px;
            animation: backgroundMove 60s linear infinite;
        }
        @keyframes backgroundMove {
            from { background-position: 0 0; }
            to { background-position: 100px 100px; }
        }
    `;
    document.head.appendChild(backgroundEffect);
});