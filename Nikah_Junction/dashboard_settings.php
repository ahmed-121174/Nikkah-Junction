

<?php
// Start session
session_start();

// Database connection
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "nikah_junction";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
           die("Connection failed: " . $conn->connect_error);
        }

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: index.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = [];
$profile = [];
$preferences = [];

try {
    // Get user information
    $stmt = $conn->prepare("SELECT firstname, lastname, email, phone FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        // User not found
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Get user profile
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $profile = $result->fetch_assoc();
    }
    
    // Get notification preferences if table exists
    $stmt = $conn->prepare("
        SELECT 1 FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = 'user_preferences'
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $preferences = $result->fetch_assoc();
        }
    }
} catch (Exception $e) {
    // Handle error
    die("Error fetching user data: " . $e->getMessage());
}

// Flash message handling
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for flash messages */
        .flash-message {
            padding: 12px 20px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: bold;
            animation: fadeOut 5s forwards;
        }
        
        .flash-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .flash-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .flash-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .profile-preview-container {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .profile-preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>NIKKAH JUNCTION</h1>
        <p class="tagline">Connecting Hearts Through Faith</p>
    </div>

    <nav class="dashboard-nav">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="dashboard_user_profiles.php">User Profiles</a></li>
            <li><a href="dashboard_matches.php">Matches</a></li>
            <li><a href="dashboard_msgs.php">Messages</a></li>
            <li><a href="dashboard_settings.php" class="active">Settings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="dashboard-header animate-fadeIn">
        <h1>Settings</h1>
        <hr>
        <p>Manage your account settings.</p>
        
        <?php if ($flash_message): ?>
            <div class="flash-message flash-<?php echo $flash_message['type']; ?>">
                <?php echo $flash_message['message']; ?>
            </div>
        <?php endif; ?>
    </div>

    <section class="dashboard-section animate-slideIn">
        <h2>Profile Management</h2>
        <form class="form-container" action="settings_handler.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="profile_update" value="1">
            
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>" placeholder="Enter First Name" required>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname'] ?? ''); ?>" placeholder="Enter Last Name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter Email Address" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? '+92'); ?>" placeholder="Enter Phone Number">
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                
                <?php if (!empty($profile['profile_picture'])): ?>
                <div class="profile-preview-container">
                    <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture" class="profile-preview-image">
                    <span>Current profile picture</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Privacy & Security</h2>
        <form class="form-container" action="settings_handler.php" method="POST">
            <input type="hidden" name="security_update" value="1">
            
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter New Password">
                <small>Leave blank if you don't want to change your password</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password">
            </div>

            <div class="form-group">
                <label for="visibility">Profile Visibility:</label>
                <select id="visibility" name="visibility" required>
                    <option value="">Select an option</option>
                    <option value="public" <?php echo (isset($profile['visibility']) && $profile['visibility'] == 'public') ? 'selected' : ''; ?>>Public</option>
                    <option value="private" <?php echo (isset($profile['visibility']) && $profile['visibility'] == 'private') ? 'selected' : ''; ?>>Private</option>
                    <option value="matches_only" <?php echo (isset($profile['visibility']) && $profile['visibility'] == 'matches_only') ? 'selected' : ''; ?>>Matches Only</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Security Settings</button>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Notification Preferences</h2>
        <form class="form-container" action="settings_handler.php" method="POST">
            <input type="hidden" name="notification_update" value="1">
            
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="email_notifications" <?php echo (isset($preferences['email_notifications']) && $preferences['email_notifications'] == 1) ? 'checked' : ''; ?>> 
                    Receive Email Notifications
                </label>
            </div>

            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="sms_notifications" <?php echo (isset($preferences['sms_notifications']) && $preferences['sms_notifications'] == 1) ? 'checked' : ''; ?>> 
                    Receive SMS Notifications
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Preferences</button>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Account Management</h2>
        <form class="form-container" action="settings_handler.php" method="POST" id="deactivateForm">
            <input type="hidden" name="deactivate_account" value="1">
            
            <div class="form-group">
                <label>Deactivate Account (You can reactivate later):</label>
                <div class="form-actions mt-1">
                    <button type="button" class="btn" onclick="confirmDeactivate()">Deactivate Account</button>
                </div>
            </div>
        </form>

        <form class="form-container mt-3" action="settings_handler.php" method="POST" id="deleteForm">
            <input type="hidden" name="delete_account" value="1">
            <input type="hidden" name="delete_confirmation" id="delete_confirmation_input">
            
            <div class="form-group">
                <label>Delete Account (This action is permanent):</label>
                <div class="form-actions mt-1">
                    <button type="button" class="btn" style="background-color: var(--error-color); color: white;" onclick="confirmDelete()">Delete Account</button>
                </div>
            </div>
        </form>
    </section>

    <footer class="footer">
        <h2>Nikkah Junction</h2>
        <p>Connecting Hearts Through Faith</p>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>

    <script>
        // Profile picture preview
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF)');
                e.target.value = '';
                return;
            }
            
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size should be less than 5MB');
                e.target.value = '';
                return;
            }
            
            // Create or update preview
            let previewContainer = document.querySelector('.profile-preview-container');
            if (!previewContainer) {
                previewContainer = document.createElement('div');
                previewContainer.classList.add('profile-preview-container');
                e.target.parentNode.appendChild(previewContainer);
            }
            
            let preview = previewContainer.querySelector('.profile-preview-image');
            if (!preview) {
                preview = document.createElement('img');
                preview.classList.add('profile-preview-image');
                previewContainer.appendChild(preview);
                
                const span = document.createElement('span');
                span.textContent = 'New profile picture';
                previewContainer.appendChild(span);
            }
            
            // Set preview image
            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
        
        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('focus', function() {
                if (!this.value.startsWith('+92')) {
                    this.value = '+92';
                }
            });

            phoneInput.addEventListener('input', function(e) {
                let phoneNumber = e.target.value.replace(/\D/g, '');

                // Remove country code if accidentally typed again
                if (phoneNumber.startsWith('92')) {
                    phoneNumber = phoneNumber.slice(2);
                } else if (phoneNumber.startsWith('0092')) {
                    phoneNumber = phoneNumber.slice(4);
                } else if (phoneNumber.startsWith('0')) {
                    phoneNumber = phoneNumber.slice(1);
                }

                // Limit max 10 digits
                phoneNumber = phoneNumber.slice(0, 10);

                // Build formatted value
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

            // Block alphabetic characters immediately
            phoneInput.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        }
        
        // Password strength check
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        
        if (passwordInput && confirmInput) {
            // Create password strength meter if it doesn't exist
            if (!document.querySelector('.password-strength-meter')) {
                const meterContainer = document.createElement('div');
                meterContainer.innerHTML = `
                    <div class="password-strength-meter">
                        <div class="strength-meter-bar"></div>
                        <p class="strength-meter-label">Password strength</p>
                    </div>
                `;
                passwordInput.parentNode.appendChild(meterContainer);
            }
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const meter = document.querySelector('.strength-meter-bar');
                const label = document.querySelector('.strength-meter-label');
                
                if (!meter || !label || !password) {
                    if (meter) meter.style.width = '0%';
                    if (label) label.textContent = 'Password strength';
                    return;
                }
                
                // Calculate password strength
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 25;
                
                // Character variety checks
                if (/[A-Z]/.test(password)) strength += 25;
                if (/[a-z]/.test(password)) strength += 15;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[^A-Za-z0-9]/.test(password)) strength += 20;
                
                // Update the meter
                meter.style.width = `${strength}%`;
                
                // Update the class and label
                meter.className = 'strength-meter-bar';
                if (strength < 30) {
                    meter.classList.add('weak');
                    label.textContent = 'Weak password';
                } else if (strength < 60) {
                    meter.classList.add('medium');
                    label.textContent = 'Medium strength';
                } else {
                    meter.classList.add('strong');
                    label.textContent = 'Strong password';
                }
            });
            
            // Password confirmation check
            confirmInput.addEventListener('input', function() {
                if (this.value === passwordInput.value) {
                    this.setCustomValidity('');
                } else {
                    this.setCustomValidity('Passwords do not match');
                }
            });
        }
        
        // Account deactivation confirmation
        function confirmDeactivate() {
            if (confirm('Are you sure you want to deactivate your account? You can reactivate it later by logging in.')) {
                document.getElementById('deactivateForm').submit();
            }
        }
        
        // Account deletion confirmation
        function confirmDelete() {
            if (confirm('WARNING: This action is permanent and cannot be undone. All your data will be deleted.')) {
                const confirmationCode = prompt('Please type "DELETE" to confirm account deletion:');
                
                if (confirmationCode === 'DELETE') {
                    document.getElementById('delete_confirmation_input').value = confirmationCode;
                    document.getElementById('deleteForm').submit();
                } else {
                    alert('Account deletion cancelled. Confirmation text did not match.');
                }
            }
        }
    </script>
</body>
</html>