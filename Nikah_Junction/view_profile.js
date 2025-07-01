document.addEventListener('DOMContentLoaded'), function() {
    // Show elements with fade-in animation
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => {
        element.style.opacity = '0';
        setTimeout(() => {
            element.style.transition = 'opacity 1s ease-in-out';
            element.style.opacity = '1';
        }, 100);
    });

    // Profile image hover effect
    const profileImage = document.querySelector('.profile-photo img');
    if (profileImage) {
        profileImage.addEventListener('mouseover', function() {
            this.style.transform = 'scale(1.05)';
            this.style.boxShadow = '0 8px 20px rgba(156, 71, 0, 0.3)';
        });
        
        profileImage.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = '0 5px 15px rgba(156, 71, 0, 0.2)';
        });
    }

    // Button hover animations
    const buttons = document.querySelectorAll('.action-btn');
    buttons.forEach(button => {
        if (!button.classList.contains('disabled')) {
            button.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 8px 20px rgba(156, 71, 0, 0.3)';
            });
            
            button.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 5px 15px rgba(156, 71, 0, 0.2)';
            });
        }
    });
}