document.addEventListener('DOMContentLoaded', function() {
    // Initialize essential components
    initProfilePictureUpload();
    initAgeCalculation();
    initDynamicFormBehavior();
    initFormValidation();
});

/**
 * Profile Picture Upload & Preview
 */
function initProfilePictureUpload() {
    const profileInput = document.getElementById('profile-picture-input');
    const preview = document.getElementById('profile-preview');
    const removeButton = document.getElementById('remove-photo');
    
    // Handle file selection
    profileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.addEventListener('load', function() {
                preview.src = reader.result;
                preview.style.display = 'block';
                removeButton.style.display = 'inline-block';
            });
            
            reader.readAsDataURL(file);
        }
    });
    
    // Remove photo functionality
    removeButton.addEventListener('click', function() {
        profileInput.value = '';
        preview.src = '';
        preview.style.display = 'none';
        this.style.display = 'none';
    });
}

/**
 * Age Calculation
 */
function initAgeCalculation() {
    const dobInput = document.getElementById('dob');
    const ageContainer = document.getElementById('age-container');
    const ageDisplay = document.getElementById('age-display');
    
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age >= 18 && age <= 80) {
                ageDisplay.textContent = age;
                ageContainer.style.display = 'inline-block';
            } else {
                ageContainer.style.display = 'none';
            }
        } else {
            ageContainer.style.display = 'none';
        }
    });
}

/**
 * Dynamic Form Behavior
 */
function initDynamicFormBehavior() {
    // Employment status and occupation relationship
    const employmentSelect = document.getElementById('employment');
    const occupationField = document.getElementById('occupation');
    
    employmentSelect.addEventListener('change', function() {
        if (this.value === 'Unemployed') {
            occupationField.value = 'N/A';
            occupationField.disabled = true;
        } else {
            occupationField.value = '';
            occupationField.disabled = false;
            occupationField.placeholder = 'Enter Occupation';
        }
    });
    
    // Marital status dynamic fields
    const maritalStatusSelect = document.getElementById('marital-status');
    const maritalStatusDetails = document.getElementById('marital-status-details');
    const divorcedDetails = document.getElementById('divorced-details');
    const widowedDetails = document.getElementById('widowed-details');
    
    maritalStatusSelect.addEventListener('change', function() {
        if (this.value === 'Divorced') {
            maritalStatusDetails.style.display = 'block';
            divorcedDetails.style.display = 'block';
            widowedDetails.style.display = 'none';
        } else if (this.value === 'Widowed') {
            maritalStatusDetails.style.display = 'block';
            divorcedDetails.style.display = 'none';
            widowedDetails.style.display = 'block';
        } else {
            maritalStatusDetails.style.display = 'none';
        }
    });
    
    // Add smooth scrolling for section navigation
    const sectionLinks = document.querySelectorAll('.form-nav-list a');
    
    sectionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                window.scrollTo({
                    top: targetSection.offsetTop - 50,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate age
        const dobInput = document.getElementById('dob');
        if (dobInput.value) {
            const birthDate = new Date(dobInput.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age < 18) {
                showNotification('You must be at least 18 years old', 'error');
                isValid = false;
            }
        }
        
        // Validate height range
        const minHeight = document.getElementById('min-height').value;
        const maxHeight = document.getElementById('max-height').value;
        if (minHeight && maxHeight) {
            const minHeightValue = parseFloat(minHeight.replace("'", "."));
            const maxHeightValue = parseFloat(maxHeight.replace("'", "."));
            
            if (minHeightValue > maxHeightValue) {
                showNotification('Maximum height must be greater than minimum height', 'error');
                isValid = false;
            }
        }
        
        // Validate age range
        const minAge = document.getElementById('min-age').value;
        const maxAge = document.getElementById('max-age').value;
        if (minAge && maxAge) {
            if (parseInt(minAge) > parseInt(maxAge)) {
                showNotification('Maximum age must be greater than minimum age', 'error');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add close button
    const closeBtn = document.createElement('span');
    closeBtn.className = 'notification-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.style.position = 'absolute';
    closeBtn.style.top = '5px';
    closeBtn.style.right = '10px';
    closeBtn.style.cursor = 'pointer';
    closeBtn.style.fontSize = '18px';
    
    closeBtn.addEventListener('click', function() {
        notification.remove();
    });
    
    notification.appendChild(closeBtn);
    container.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}