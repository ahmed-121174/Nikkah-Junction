<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "nikah_junction";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the profile ID from URL parameter
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID is provided, redirect to profiles page
if ($profile_id === 0) {
    header("Location: user_profiles.php");
    exit();
}

// Get current user ID
$current_user_id = $_SESSION['user_id'];

// Fetch user data and profile data
$sql = "SELECT u.*, up.* 
        FROM users u 
        LEFT JOIN user_profiles up ON u.id = up.id 
        WHERE u.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Profile not found
    header("Location: user_profiles.php");
    exit();
}

$profile_data = $result->fetch_assoc();
$stmt->close();

// Function to calculate age from date of birth
function calculateAge($dob) {
    $birthdate = new DateTime($dob);
    $today = new DateTime();
    $age = $birthdate->diff($today)->y;
    return $age;
}

// Process send message form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $recipient_id = $profile_id;
    $message_text = trim($_POST['message_text']);
    
    // Check if there's an existing conversation
    $sql = "SELECT conversation_id FROM messages 
            WHERE (sender_id = ? AND recipient_id = ?) 
            OR (sender_id = ? AND recipient_id = ?) 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $current_user_id, $recipient_id, $recipient_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conversation_id = $row['conversation_id'];
    } else {
        // Create a new conversation_id
        $sql = "INSERT INTO conversations (created_at) VALUES (NOW())";
        $conn->query($sql);
        $conversation_id = $conn->insert_id;
    }
    
    // Insert the message
    $sql = "INSERT INTO messages (conversation_id, sender_id, recipient_id, message_text, is_read, created_at) 
            VALUES (?, ?, ?, ?, 0, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $conversation_id, $current_user_id, $recipient_id, $message_text);
    
    if ($stmt->execute()) {
        // Redirect to messages page
        header("Location: dashboard_msgs.php?conversation_id=" . $conversation_id);
        exit();
    }
    
    $stmt->close();
}

// Process friend request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_friend_request'])) {
    $sql = "INSERT INTO friend_requests (sender_id, receiver_id, status, created_at) 
            VALUES (?, ?, 'pending', NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $current_user_id, $profile_id);
    
    if ($stmt->execute()) {
        $success_message = "Friend request sent successfully!";
    } else {
        $error_message = "Error sending friend request.";
    }
    
    $stmt->close();
}

// Check if friend request already sent
$friend_request_status = null;
$sql = "SELECT status FROM friend_requests 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $profile_id, $profile_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $friend_request_status = $row['status'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile - Nikkah Junction</title>
    <link rel="stylesheet" href="view.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <img src="logo.png" alt="Nikkah Junction Logo">
                <div class="logo-text">
                    <h1>NIKKAH JUNCTION</h1>
                    <p>Connecting Hearts Through Faith</p>
                </div>
            </div>
        </header>

        <nav class="dashboard-nav">
            <h2>Dashboard</h2>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="dashboard_user_profiles.php">User Profiles</a></li>
                <li><a href="dashboard_matches.php">Matches</a></li>
                <li><a href="dashboard_msgs.php">Messages</a></li>
                <li><a href="dashboard_settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="profile-view fade-in">
            <h2>View Profile</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-photo">
                        <?php if (!empty($profile_data['profile_picture'])): ?>
                            <img src="<?php echo $profile_data['profile_picture']; ?>" alt="Profile Picture">
                        <?php else: ?>
                            <img src="default-profile.png" alt="Default Profile Picture">
                        <?php endif; ?>
                    </div>
                    <div class="profile-basic-info">
                        <h3><?php echo htmlspecialchars($profile_data['firstname'] . ' ' . $profile_data['lastname']); ?></h3>
                        <p><i class="fas fa-birthday-cake"></i> Age: <?php echo !empty($profile_data['dob']) ? calculateAge($profile_data['dob']) : 'Not specified'; ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> Location: <?php echo !empty($profile_data['country']) ? htmlspecialchars($profile_data['country']) : 'Not specified'; ?></p>
                        <p><i class="fas fa-graduation-cap"></i> Education: <?php echo !empty($profile_data['education']) ? htmlspecialchars($profile_data['education']) : 'Not specified'; ?></p>
                        <p><i class="fas fa-briefcase"></i> Occupation: <?php echo !empty($profile_data['occupation']) ? htmlspecialchars($profile_data['occupation']) : 'Not specified'; ?></p>
                    </div>
                    <div class="profile-actions">
                        <?php if ($current_user_id != $profile_id): ?>
                            <button class="action-btn message-btn" id="openMessageModal"><a href="dashboard_msgs.php"><i class="fas fa-envelope"></i> Send Message</a></button>

                            
                            <?php if ($friend_request_status === null): ?>
                                <form method="post">
                                    <button type="submit" name="send_friend_request" class="action-btn friend-btn"><i class="fas fa-user-plus"></i> Send Friend Request</button>
                                </form>
                            <?php elseif ($friend_request_status === 'pending'): ?>
                                <button class="action-btn friend-btn disabled"><i class="fas fa-clock"></i> Friend Request Pending</button>
                            <?php elseif ($friend_request_status === 'accepted'): ?>
                                <button class="action-btn friend-btn disabled"><i class="fas fa-check"></i> Friends</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="dashboard_settings.php" class="action-btn edit-btn"><i class="fas fa-edit"></i> Edit Profile</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-details">
                    <div class="detail-section">
                        <h4>Personal Information</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Gender:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['gender']) ? htmlspecialchars($profile_data['gender']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Religion:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['religion']) ? htmlspecialchars($profile_data['religion']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Caste:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['caste']) ? htmlspecialchars($profile_data['caste']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Mother Tongue:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['mother_tongue']) ? htmlspecialchars($profile_data['mother_tongue']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Height:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['height']) ? htmlspecialchars($profile_data['height']) . ' cm' : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Marital Status:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['marital_status']) ? htmlspecialchars($profile_data['marital_status']) : 'Not specified'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Lifestyle</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Diet:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['diet']) ? htmlspecialchars($profile_data['diet']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Drinking:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['drinking']) ? htmlspecialchars($profile_data['drinking']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Smoking:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['smoking']) ? htmlspecialchars($profile_data['smoking']) : 'Not specified'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Family Background</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Father's Occupation:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['father_occupation']) ? htmlspecialchars($profile_data['father_occupation']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Mother's Occupation:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['mother_occupation']) ? htmlspecialchars($profile_data['mother_occupation']) : 'Not specified'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Siblings:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['siblings']) ? htmlspecialchars($profile_data['siblings']) : 'Not specified'; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Partner Preferences</h4>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Age Range:</span>
                                <span class="detail-value">
                                    <?php 
                                    if (!empty($profile_data['min_age_preferences']) && !empty($profile_data['max_age_preferences'])) {
                                        echo htmlspecialchars($profile_data['min_age_preferences']) . ' to ' . htmlspecialchars($profile_data['max_age_preferences']) . ' years';
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Height Range:</span>
                                <span class="detail-value">
                                    <?php 
                                    if (!empty($profile_data['min_height_preferences']) && !empty($profile_data['max_height_preferences'])) {
                                        echo htmlspecialchars($profile_data['min_height_preferences']) . ' to ' . htmlspecialchars($profile_data['max_height_preferences']) . ' cm';
                                    } else {
                                        echo 'Not specified';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="detail-item full-width">
                                <span class="detail-label">Additional Preferences:</span>
                                <span class="detail-value"><?php echo !empty($profile_data['additional_preferences']) ? htmlspecialchars($profile_data['additional_preferences']) : 'Not specified'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Nikkah Junction. All rights reserved.</p>
        </footer>
    </div>

    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3>Send Message to <?php echo htmlspecialchars($profile_data['firstname']); ?></h3>
            <form method="post" action="">
                <div class="form-group">
                    <label for="message_text">Your Message:</label>
                    <textarea name="message_text" id="message_text" rows="5" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn">Send Message</button>
            </form>
        </div>
    </div>

    <script src="view_profile.js"></script>
</body>
</html>