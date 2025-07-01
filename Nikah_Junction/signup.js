document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.querySelector('form');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const firstnameInput = document.getElementById('firstname');
    const lastnameInput = document.getElementById('lastname');
    
    // Add password strength meter
    const passwordContainer = passwordInput.parentElement;
    const strengthMeter = document.createElement('div');
    strengthMeter.className = 'password-strength';
    strengthMeter.innerHTML = `
        <div class="strength-meter">
            <div class="strength-meter-fill" data-strength="0"></div>
        </div>
        <div class="strength-text">Password strength: <span>Too weak</span></div>
    `;
    passwordContainer.appendChild(strengthMeter);
    
    // Password strength checker
    passwordInput.addEventListener('input', function() {
        const password = passwordInput.value;
        const strengthFill = document.querySelector('.strength-meter-fill');
        const strengthText = document.querySelector('.strength-text span');
        
        let strength = 0;
        
        // Check password length
        if (password.length >= 8) {
            strength += 1;
        }
        
        // Check for mixed case
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) {
            strength += 1;
        }
        
        // Check for numbers
        if (password.match(/\d/)) {
            strength += 1;
        }
        
        // Check for special characters
        if (password.match(/[^a-zA-Z\d]/)) {
            strength += 1;
        }
        
        // Update strength meter UI
        strengthFill.setAttribute('data-strength', strength);
        strengthFill.style.width = (strength * 25) + '%';
        
        // Update text
        const strengthLabels = ['Too weak', 'Weak', 'Medium', 'Good', 'Strong'];
        strengthText.textContent = strengthLabels[strength];
        
        // Add color classes
        const colorClasses = ['strength-weak', 'strength-medium', 'strength-good', 'strength-strong'];
        colorClasses.forEach(cls => strengthFill.classList.remove(cls));
        
        if (strength > 0) {
            const strengthClass = strength === 1 ? 'strength-weak' : 
                                 strength === 2 ? 'strength-medium' : 
                                 strength === 3 ? 'strength-good' : 
                                 'strength-strong';
            strengthFill.classList.add(strengthClass);
        }
    });
    
        phoneInput.addEventListener('input', function (e) {
        // Remove all non-digit characters
        let phoneNumber = e.target.value.replace(/\D/g, '');

        // Remove leading country codes or 0
        if (phoneNumber.startsWith('92')) {
            phoneNumber = phoneNumber.slice(2);
        } else if (phoneNumber.startsWith('0092')) {
            phoneNumber = phoneNumber.slice(4);
        } else if (phoneNumber.startsWith('0')) {
            phoneNumber = phoneNumber.slice(1);
        }

        // Limit to max 10 digits (without country code)
        phoneNumber = phoneNumber.slice(0, 10);

        // Start building formatted value
        let formatted = '+92';

        if (phoneNumber.length > 0) {
            formatted += '-';
        }

        if (phoneNumber.length <= 3) {
            formatted += phoneNumber;
        } else if (phoneNumber.length <= 6) {
            formatted += phoneNumber.slice(0, 3) + '-' + phoneNumber.slice(3);
        } else {
            formatted += phoneNumber.slice(0, 3) + '-' + phoneNumber.slice(3, 10);
        }

        e.target.value = formatted;
    });

    // Extra protection: block alphabetic characters on keypress
    phoneInput.addEventListener('keypress', function (e) {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });

    
    // Form validation
    signupForm.addEventListener('submit', function(e) {
        let isValid = true;
        const errorMessages = [];
        
        // Clear previous error messages
        document.querySelectorAll('.error-message').forEach(el => el.remove());
        
        // Validate first name
        if (firstnameInput.value.trim().length < 2) {
            isValid = false;
            addErrorTo(firstnameInput, 'First name must be at least 2 characters');
            errorMessages.push('First name must be at least 2 characters');
        }
        
        // Validate last name
        if (lastnameInput.value.trim().length < 2) {
            isValid = false;
            addErrorTo(lastnameInput, 'Last name must be at least 2 characters');
            errorMessages.push('Last name must be at least 2 characters');
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            isValid = false;
            addErrorTo(emailInput, 'Please enter a valid email address');
            errorMessages.push('Please enter a valid email address');
        }
        
        // Validate phone (basic validation)
        const phoneDigits = phoneInput.value.replace(/\D/g, '');
        if (phoneDigits.length < 10) {
            isValid = false;
            addErrorTo(phoneInput, 'Please enter a valid phone number');
            errorMessages.push('Please enter a valid phone number');
        }
        
        // Validate password
        if (passwordInput.value.length < 8) {
            isValid = false;
            addErrorTo(passwordInput, 'Password must be at least 8 characters');
            errorMessages.push('Password must be at least 8 characters');
        }
        
        // If the form is not valid, prevent submission
        if (!isValid) {
            e.preventDefault();
            
            // Create a summary error message at the top
            const errorSummary = document.createElement('div');
            errorSummary.className = 'error-summary';
            errorSummary.innerHTML = `
                <p>Please fix the following errors:</p>
                <ul>${errorMessages.map(msg => `<li>${msg}</li>`).join('')}</ul>
            `;
            
            signupForm.insertBefore(errorSummary, signupForm.firstChild);
            
            // Scroll to the top of the form
            errorSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            // Store form data in localStorage for later use
            saveFormData();
            
            // Show loading indicator
            const submitButton = document.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Processing...';
            
            // Simulate server processing (remove in production)
            setTimeout(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }, 1000);
        }
    });
    
    // Helper function to add error message
    function addErrorTo(inputElement, message) {
        const formGroup = inputElement.parentElement;
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        formGroup.appendChild(errorDiv);
        
        // Add error styling
        inputElement.classList.add('input-error');
        
        // Remove error when input changes
        inputElement.addEventListener('input', function() {
            errorDiv.remove();
            inputElement.classList.remove('input-error');
        }, { once: true });
    }
    
    // Save form data to localStorage
    function saveFormData() {
        const formData = {
            firstname: firstnameInput.value,
            lastname: lastnameInput.value,
            email: emailInput.value,
            phone: phoneInput.value
        };
        
        localStorage.setItem('nikkahjunction_signup_data', JSON.stringify(formData));
    }
    
    // // Load any previously saved form data
    // function loadSavedFormData() {
    //     const savedData = localStorage.getItem('nikkahjunction_signup_data');
    //     if (savedData) {
    //         const formData = JSON.parse(savedData);
    //         firstnameInput.value = formData.firstname || '';
    //         lastnameInput.value = formData.lastname || '';
    //         emailInput.value = formData.email || '';
    //         phoneInput.value = formData.phone || '';
    //     }
    // }
    
    // // Check if there's saved data
    // loadSavedFormData();
    
    // Animation enhancements
    const formElements = document.querySelectorAll('.form-group');
    formElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        element.style.transitionDelay = `${index * 0.1}s`;
        
        setTimeout(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    });
});

// Add necessary CSS
const styleElement = document.createElement('style');
styleElement.textContent = `
    .password-strength {
        margin-top: 8px;
    }
    
    .strength-meter {
        height: 4px;
        background-color: #e0e0e0;
        border-radius: 2px;
        position: relative;
        margin-bottom: 8px;
    }
    
    .strength-meter-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }
    
    .strength-meter-fill[data-strength="0"] {
        width: 0%;
        background-color: transparent;
    }
    
    .strength-weak {
        background-color: #f44336;
    }
    
    .strength-medium {
        background-color: #ffa726;
    }
    
    .strength-good {
        background-color: #66bb6a;
    }
    
    .strength-strong {
        background-color: #43a047;
    }
    
    .strength-text {
        font-size: 12px;
        color: #777;
    }
    
    .error-message {
        color: var(--error-color);
        font-size: 0.85em;
        margin-top: 5px;
    }
    
    .input-error {
        border-color: var(--error-color) !important;
    }
    
    .error-summary {
        background-color: #ffebee;
        border: 1px solid var(--error-color);
        border-radius: var(--border-radius);
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .error-summary ul {
        margin-top: 10px;
        margin-left: 20px;
    }
    
    .spinner {
        display: inline-block;
        width: 15px;
        height: 15px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
        margin-right: 5px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(styleElement);