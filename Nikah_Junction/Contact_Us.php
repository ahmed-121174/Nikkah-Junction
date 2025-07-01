<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "nikah_junction";

// Initialize variables for form data
$email = $subject = $message = "";
$errorMessage = "";
$successMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $message = $_POST["body"];
    
    // Basic validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address";
    } elseif (empty($subject)) {
        $errorMessage = "Subject cannot be empty";
    } elseif (empty($message)) {
        $errorMessage = "Message cannot be empty";
    } else {
        // Connect to database
        $conn = new mysqli($host, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            $errorMessage = "Connection failed: " . $conn->connect_error;
        } else {
            // Prepare SQL statement
            $stmt = $conn->prepare("INSERT INTO contact_us (email, subject, message, date_submitted) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $email, $subject, $message);
            
            // Execute statement
            if ($stmt->execute()) {
                $successMessage = "Message sent successfully! We will get back to you soon.";
                // Clear form fields after successful submission
                $email = $subject = $message = "";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }
            
            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Nikah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for PHP version */
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        .success {
            color: #2ecc71;
            padding: 15px;
            background-color: rgba(46, 204, 113, 0.1);
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .floating-label-wrapper {
            position: relative;
            margin-top: 25px;
            margin-bottom: 25px;
        }
        
        .floating-label-wrapper input,
        .floating-label-wrapper textarea {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        
        .floating-label-wrapper label {
            position: absolute;
            left: 12px;
            top: 12px;
            background: white;
            padding: 0 4px;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #777;
        }
        
        .floating-label-wrapper.active label,
        .floating-label-wrapper input:focus + label,
        .floating-label-wrapper input:not(:placeholder-shown) + label,
        .floating-label-wrapper textarea:focus + label,
        .floating-label-wrapper textarea:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: var(--primary-color);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="logo" class="logo">
        <h1 class="site-title">NIKAH JUNCTION</h1>
    </div>
    
    <nav class="main-nav">
        <a href="index.html">Home</a>
        <a href="About_Us.html">About Us</a>
        <a href="faqs.html">FAQs</a>
        <a href="Contact_Us.php">Contact Us</a>
    </nav>

    <div class="form-container">
        <h2 class="section-title">Send Us a Message</h2>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="contact-form">
            <div class="form-group">
                <div class="floating-label-wrapper <?php echo !empty($email) ? 'active' : ''; ?>">
                    <label for="email">Email Address*</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder=" ">
                </div>
            </div>
            
            <div class="form-group">
                <div class="floating-label-wrapper <?php echo !empty($subject) ? 'active' : ''; ?>">
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" value="<?php echo $subject; ?>" placeholder=" ">
                </div>
            </div>
            
            <div class="form-group">
                <div class="floating-label-wrapper <?php echo !empty($message) ? 'active' : ''; ?>">
                    <label for="message">Message:</label>
                    <textarea id="message" name="body" placeholder=" "><?php echo $message; ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Send Message</button>
            </div>
        </form>
    </div>

    <div class="contact-info dashboard-section">
        <h2 class="section-title">Contact Us</h2>
        <h3>Get in touch with NIKAH JUNCTION via sending Your mail to:</h3>
        <p>Email: <a href="mailto:Nikkahjunction@gmail.com">Nikkahjunction@gmail.com</a></p>
        <p>You can also Contact us at: +923403986167<br>Sir Khushal or Sir Azam May respond to you.</p>
    </div>

    <footer class="footer">
        <h2>Laakhon Verified Rishtey <br> Ab Aik Click Par!</h2>
        <img src="logo.png" alt="logo" class="footer-logo">
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    // Create and show loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s ease-out;
    `;
    
    // Create spinner
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    spinner.style.cssText = `
        width: 50px;
        height: 50px;
        border: 5px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        border-top-color: brown;
        animation: spin 1s ease-in-out infinite;
    `;
    
    // Add keyframe animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // Add spinner to overlay
    loadingOverlay.appendChild(spinner);
    document.body.appendChild(loadingOverlay);
    
    // Hide loading overlay after 2 seconds
    setTimeout(() => {
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            loadingOverlay.remove();
        }, 300);
        
        // Add animation classes
        const formContainer = document.querySelector('.form-container');
        const contactInfo = document.querySelector('.contact-info');
        const siteTitle = document.querySelector('.site-title');
        
        if (formContainer) formContainer.classList.add('animated', 'fadeInLeft');
        if (contactInfo) contactInfo.classList.add('animated', 'fadeInRight');
        if (siteTitle) siteTitle.classList.add('animated', 'fadeInDown');
        
        // Setup floating labels
        const formInputs = document.querySelectorAll('.contact-form input, .contact-form textarea');
        formInputs.forEach(input => {
            const wrapper = input.parentNode;
            
            input.addEventListener('focus', () => {
                wrapper.classList.add('active');
            });
            
            input.addEventListener('blur', () => {
                if (input.value === '') {
                    wrapper.classList.remove('active');
                }
            });
        });
        
        // Set active nav link
        const currentPage = window.location.pathname.split('/').pop();
        const navLinks = document.querySelectorAll('.main-nav a');
        navLinks.forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === currentPage) {
                link.classList.add('active-nav');
            }
        });
    }, 2000);
});
    </script>
</body>
</html>