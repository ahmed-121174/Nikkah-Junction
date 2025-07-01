<?php
// Start session
session_start();

// Database connection
require_once 'connection_db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Determine which form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Profile Management form
    if (isset($_POST['profile_update'])) {
        handleProfileUpdate($conn, $user_id);
    }
    
    // Security & Privacy form
    elseif (isset($_POST['security_update'])) {
        handleSecurityUpdate($conn, $user_id);
    }
    
    // Notification Preferences form
    elseif (isset($_POST['notification_update'])) {
        handleNotificationUpdate($conn, $user_id);
    }
    
    // Account Management - Deactivate
    elseif (isset($_POST['deactivate_account'])) {
        handleAccountDeactivation($conn, $user_id);
    }
    
    // Account Management - Delete
    elseif (isset($_POST['delete_account'])) {
        handleAccountDeletion($conn, $user_id);
    }
}

/**
 * Handle profile information update
 */
function handleProfileUpdate($conn, $user_id) {
    try {
        // Get form data
        $firstname = validateInput($_POST['firstname']);
        $lastname = validateInput($_POST['lastname']);
        $email = validateInput($_POST['email']);
        $phone = validateInput($_POST['phone']);
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Invalid email format');
            header('Location: dashboard_settings.php');
            exit();
        }
        
        // Check if email already exists (for another user)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            setFlashMessage('error', 'Email already in use by another account');
            header('Location: dashboard_settings.php');
            exit();
        }
        
        // Validate phone number (Pakistan format)
        if (!empty($phone) && !preg_match('/^\+92-[0-9]{3}-[0-9]{7}$/', $phone)) {
            setFlashMessage('error', 'Please enter a valid Pakistan phone number (+92-XXX-XXXXXXX)');
            header('Location: dashboard_settings.php');
            exit();
        }
        
        // Update user table
        $stmt = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $firstname, $lastname, $email, $phone, $user_id);
        $stmt->execute();
        
        // Handle profile picture upload if present
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $profile_picture = handleFileUpload($_FILES['profile_picture'], $user_id);
            
            if ($profile_picture) {
                // Check if user_profile exists
                $stmt = $conn->prepare("SELECT id FROM user_profiles WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Update existing profile
                    $stmt = $conn->prepare("UPDATE user_profiles SET profile_picture = ? WHERE id = ?");
                    $stmt->bind_param("si", $profile_picture, $user_id);
                } else {
                    // Create new profile
                    $stmt = $conn->prepare("INSERT INTO user_profiles (id, profile_picture) VALUES (?, ?)");
                    $stmt->bind_param("is", $user_id, $profile_picture);
                }
                
                $stmt->execute();
            }
        }
        
        setFlashMessage('success', 'Profile updated successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error updating profile: ' . $e->getMessage());
    }
    
    header('Location: dashboard_settings.php');
    exit();
}

/**
 * Handle security settings update
 */
function handleSecurityUpdate($conn, $user_id) {
    try {
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        $visibility = validateInput($_POST['visibility']);
        
        // Handle password update if provided
        if (!empty($new_password)) {
            // Validate password
            if (strlen($new_password) < 8) {
                setFlashMessage('error', 'Password must be at least 8 characters long');
                header('Location: dashboard_settings.php');
                exit();
            }
            
            // Check if passwords match
            if ($new_password !== $confirm_password) {
                setFlashMessage('error', 'Passwords do not match');
                header('Location: dashboard_settings.php');
                exit();
            }
            
            // Hash password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $password_hash, $user_id);
            $stmt->execute();
        }
        
        // Update visibility setting in user_profiles
        $stmt = $conn->prepare("SELECT id FROM user_profiles WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing profile
            $stmt = $conn->prepare("UPDATE user_profiles SET visibility = ? WHERE id = ?");
            $stmt->bind_param("si", $visibility, $user_id);
        } else {
            // Create new profile
            $stmt = $conn->prepare("INSERT INTO user_profiles (id, visibility) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $visibility);
        }
        
        $stmt->execute();
        
        setFlashMessage('success', 'Security settings updated successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error updating security settings: ' . $e->getMessage());
    }
    
    header('Location: dashboard_settings.php');
    exit();
}

/**
 * Handle notification preferences update
 */
function handleNotificationUpdate($conn, $user_id) {
    try {
        // Get notification preferences
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        
        // Check if user_preferences table exists and create if not
        $stmt = $conn->prepare("
            CREATE TABLE IF NOT EXISTS user_preferences (
                user_id INT PRIMARY KEY,
                email_notifications TINYINT(1) DEFAULT 0,
                sms_notifications TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        $stmt->execute();
        
        // Check if user preferences exist
        $stmt = $conn->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing preferences
            $stmt = $conn->prepare("UPDATE user_preferences SET email_notifications = ?, sms_notifications = ? WHERE user_id = ?");
            $stmt->bind_param("iii", $email_notifications, $sms_notifications, $user_id);
        } else {
            // Create new preferences
            $stmt = $conn->prepare("INSERT INTO user_preferences (user_id, email_notifications, sms_notifications) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $email_notifications, $sms_notifications);
        }
        
        $stmt->execute();
        
        setFlashMessage('success', 'Notification preferences updated successfully');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error updating notification preferences: ' . $e->getMessage());
    }
    
    header('Location: dashboard_settings.php');
    exit();
}

/**
 * Handle account deactivation
 */
function handleAccountDeactivation($conn, $user_id) {
    try {
        // Add is_active column to users table if it doesn't exist
        $stmt = $conn->prepare("
            SHOW COLUMNS FROM users LIKE 'is_active'
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            $stmt = $conn->prepare("
                ALTER TABLE users ADD COLUMN is_active TINYINT(1) DEFAULT 1
            ");
            $stmt->execute();
        }
        
        // Set account to inactive
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Log the deactivation
        $stmt = $conn->prepare("
            CREATE TABLE IF NOT EXISTS user_activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                action VARCHAR(50),
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $stmt->execute();
        
        $action = "account_deactivated";
        $stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, action) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $action);
        $stmt->execute();
        
        // Destroy session
        session_destroy();
        
        setFlashMessage('info', 'Your account has been deactivated. You can reactivate it by logging in again.');
    } catch (Exception $e) {
        setFlashMessage('error', 'Error deactivating account: ' . $e->getMessage());
    }
    
    header('Location: login.php');
    exit();
}

/**
 * Handle account deletion
 */
function handleAccountDeletion($conn, $user_id) {
    try {
        // Verify deletion confirmation
        $confirmation = isset($_POST['delete_confirmation']) ? $_POST['delete_confirmation'] : '';
        
        if ($confirmation !== 'DELETE') {
            setFlashMessage('error', 'Account deletion cancelled. Confirmation text did not match.');
            header('Location: dashboard_settings.php');
            exit();
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Delete from user_profiles
        $stmt = $conn->prepare("DELETE FROM user_profiles WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete from user_preferences if exists
        $stmt = $conn->prepare("
            DELETE FROM user_preferences WHERE user_id = ? AND EXISTS 
            (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'user_preferences')
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Delete any other related records
        // Add more delete statements for other tables that reference user_id
        
        // Log the deletion in a separate table
        $stmt = $conn->prepare("
            CREATE TABLE IF NOT EXISTS deleted_users_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                deletion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $stmt->execute();
        
        $stmt = $conn->prepare("INSERT INTO deleted_users_log (user_id) VALUES (?)");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Finally delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Destroy session
        session_destroy();
        
        setFlashMessage('info', 'Your account has been permanently deleted.');
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        setFlashMessage('error', 'Error deleting account: ' . $e->getMessage());
        header('Location: dashboard_settings.php');
        exit();
    }
    
    header('Location: dashboard.php');
    exit();
}

/**
 * Handle file upload
 */
function handleFileUpload($file, $user_id) {
    // Define allowed file types and max size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        setFlashMessage('error', 'Invalid file type. Please upload JPEG, PNG or GIF files only.');
        return false;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        setFlashMessage('error', 'File is too large. Maximum size is 5MB.');
        return false;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = 'uploads/profile_pictures/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = $user_id . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    // Move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    } else {
        setFlashMessage('error', 'Error uploading file. Please try again.');
        return false;
    }
}

/**
 * Validate and sanitize input
 */
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}
?>