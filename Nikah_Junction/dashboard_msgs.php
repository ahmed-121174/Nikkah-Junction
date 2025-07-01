<?php


// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login page
    exit;
}

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

$user_id = $_SESSION['user_id']; // Current logged-in user

// Process new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['conversation_id']) && isset($_POST['message_text'])) {
    $conversation_id = $_POST['conversation_id'];
    $message_text = trim($_POST['message_text']);
    
    if (empty($message_text)) {
        header("Location: dashboard_msgs.php?conversation_id=$conversation_id&error=empty_message");
        exit;
    }
    
    // Check if the conversation exists and user is a member
    $query = "SELECT cm.* FROM conversation_members cm
              WHERE cm.conversation_id = ? AND cm.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: dashboard_msgs.php?error=unauthorized_conversation");
        exit;
    }
    
    // Get the recipient ID
    $query = "SELECT user_id FROM conversation_members 
              WHERE conversation_id = ? AND user_id != ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: dashboard_msgs.php?error=invalid_conversation");
        exit;
    }
    
    $recipient = $result->fetch_assoc();
    $recipient_id = $recipient['user_id'];
    
    // Verify that there's an accepted friend request between the users
    $query = "SELECT * FROM friend_requests 
              WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
              AND status = 'accepted'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: dashboard_msgs.php?error=not_friends");
        exit;
    }
    
    // Insert the new message
    $query = "INSERT INTO messages (conversation_id, sender_id, recipient_id, message_text, is_read) 
              VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiis", $conversation_id, $user_id, $recipient_id, $message_text);
    
    if ($stmt->execute()) {
        // Update the last_message_id in the conversations table
        $message_id = $stmt->insert_id;
        $update_query = "UPDATE conversations SET last_message_id = ?, updated_at = NOW() WHERE conversation_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $message_id, $conversation_id);
        $update_stmt->execute();
        
        header("Location: dashboard_msgs.php?conversation_id=$conversation_id&success=message_sent");
        exit;
    } else {
        header("Location: dashboard_msgs.php?conversation_id=$conversation_id&error=send_failed");
        exit;
    }
}

// Process new conversation creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_conversation']) && isset($_POST['recipient_id'])) {
    $recipient_id = $_POST['recipient_id'];
    
    // Verify that there's an accepted friend request between the users
    $query = "SELECT * FROM friend_requests 
              WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
              AND status = 'accepted'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $user_id, $recipient_id, $recipient_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: dashboard_msgs.php?error=not_friends");
        exit;
    }
    
    // Check if a conversation already exists
    $query = "SELECT c.conversation_id FROM conversations c
              JOIN conversation_members cm1 ON c.conversation_id = cm1.conversation_id
              JOIN conversation_members cm2 ON c.conversation_id = cm2.conversation_id
              WHERE cm1.user_id = ? AND cm2.user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header("Location: dashboard_msgs.php?conversation_id=".$row['conversation_id']);
        exit;
    }
    
    // Create a new conversation
    $conn->begin_transaction();
    
    try {
        // Insert conversation
        $query = "INSERT INTO conversations (created_at, updated_at) VALUES (NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $conversation_id = $stmt->insert_id;
        
        // Add members to the conversation
        $query = "INSERT INTO conversation_members (conversation_id, user_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $conversation_id, $user_id);
        $stmt->execute();
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $conversation_id, $recipient_id);
        $stmt->execute();
        
        $conn->commit();
        
        header("Location: dashboard_msgs.php?conversation_id=$conversation_id");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: dashboard_msgs.php?error=creation_failed");
        exit;
    }
}

// Function to format time
function formatTime($timestamp) {
    $date = new DateTime($timestamp);

    return $date->format('H:i - d/m/Y');
}

// Get unread message count
$query = "SELECT COUNT(*) as unread_count FROM messages 
          WHERE recipient_id = ? AND is_read = 0";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread = $result->fetch_assoc();
$unread_count = $unread['unread_count'];

// Get current conversation details if any
$current_conversation = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;
$other_user_name = '';
$other_user_id = 0;
$can_message = false;

if ($current_conversation) {
    // Get the other user's name and ID
    $query = "SELECT u.id, u.firstname, u.lastname FROM users u
              JOIN conversation_members cm ON u.id = cm.user_id
              WHERE cm.conversation_id = ? AND u.id != ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $current_conversation, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $other_user = $result->fetch_assoc();
        $other_user_name = $other_user['firstname'] . " " . $other_user['lastname'];
        $other_user_id = $other_user['id'];
        
        // Check if there's an accepted friend request
        $query = "SELECT * FROM friend_requests 
                  WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                  AND status = 'accepted'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $can_message = ($result->num_rows > 0);
        
        // Mark messages as read
        $update_query = "UPDATE messages SET is_read = 1 
                        WHERE conversation_id = ? AND recipient_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $current_conversation, $user_id);
        $update_stmt->execute();
    }
}

// Get all conversations for the current user with accepted friend requests
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query to get conversations with accepted friend requests
$base_query = "SELECT c.conversation_id, u.id, u.firstname, u.lastname, up.profile_picture, 
         m.message_text as last_message, m.created_at as time,
         (SELECT COUNT(*) FROM messages 
          WHERE conversation_id = c.conversation_id 
          AND recipient_id = ? AND is_read = 0) as unread_count,
         CASE
           WHEN EXISTS (
             SELECT 1 FROM friend_requests fr
             WHERE ((fr.sender_id = ? AND fr.receiver_id = u.id) OR (fr.sender_id = u.id AND fr.receiver_id = ?))
             AND fr.status = 'accepted'
           ) THEN 1
           ELSE 0
         END as is_friend
         FROM conversations c
         JOIN conversation_members cm ON c.conversation_id = cm.conversation_id
         JOIN users u ON cm.user_id = u.id
         LEFT JOIN user_profiles up ON u.id = up.id
         LEFT JOIN messages m ON m.message_id = c.last_message_id
         WHERE cm.conversation_id IN (
             SELECT conversation_id FROM conversation_members WHERE user_id = ?
         )
         AND u.id != ?";

if (!empty($search)) {
    $query = $base_query . " AND (u.firstname LIKE ? OR u.lastname LIKE ?)
         ORDER BY c.updated_at DESC";
    $stmt = $conn->prepare($query);
    $search_param = '%' . $search . '%';
    $stmt->bind_param("iiiiiss", $user_id, $user_id, $user_id, $user_id, $user_id, $search_param, $search_param);
} else {
    $query = $base_query . " ORDER BY c.updated_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$conversations = [];
while ($row = $result->fetch_assoc()) {
    // Handle null profile picture
    if (empty($row['profile_picture'])) {
        $row['profile_picture'] = 'default-avatar.png';
    }
    
    // Create full name
    $row['name'] = $row['firstname'] . ' ' . $row['lastname'];
    
    $conversations[] = $row;
}

// Get messages for current conversation if any
$messages = [];
if ($current_conversation) {
    $query = "SELECT m.*, 
             IF(m.sender_id = ?, 'sent', 'received') as message_type,
             CONCAT(u.firstname, ' ', u.lastname) as sender_name
             FROM messages m
             JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ?
             ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $current_conversation);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($message = $result->fetch_assoc()) {
        $messages[] = $message;
    }
}

// Get accepted friend requests who don't have a conversation yet
$query = "SELECT u.id, CONCAT(u.firstname, ' ', u.lastname) as name, up.profile_picture
         FROM friend_requests fr
         JOIN users u ON (
            (fr.sender_id = ? AND fr.receiver_id = u.id) OR 
            (fr.sender_id = u.id AND fr.receiver_id = ?)
         )
         LEFT JOIN user_profiles up ON u.id = up.id
         WHERE fr.status = 'accepted'
         AND NOT EXISTS (
             SELECT 1 FROM conversation_members cm1
             JOIN conversation_members cm2 ON cm1.conversation_id = cm2.conversation_id
             WHERE cm1.user_id = ? AND cm2.user_id = u.id
         )";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$new_contacts = [];
while ($row = $result->fetch_assoc()) {
    if (empty($row['profile_picture'])) {
        $row['profile_picture'] = 'default-avatar.png';
    }
    $new_contacts[] = $row;
}

// Check if there's an error or success message
$error_message = '';
$success_message = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_message':
            $error_message = "Message cannot be empty.";
            break;
        case 'invalid_conversation':
            $error_message = "Invalid conversation.";
            break;
        case 'send_failed':
            $error_message = "Failed to send message. Please try again.";
            break;
        case 'not_friends':
            $error_message = "You can only message users who have accepted your friend request.";
            break;
        case 'unauthorized_conversation':
            $error_message = "You are not authorized to access this conversation.";
            break;
        case 'creation_failed':
            $error_message = "Failed to create conversation. Please try again.";
            break;
    }
}

if (isset($_GET['success']) && $_GET['success'] == 'message_sent') {
    $success_message = "Message sent successfully.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .notification-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        
        .typing-indicator {
            background-color: #f1f1f1;
            font-style: italic;
            opacity: 0.7;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        
        .message {
            position: relative;
            max-width: 80%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 10px;
            word-wrap: break-word;
        }
        
        .message-sent {
            align-self: flex-end;
            background-color: #dcf8c6;
            margin-left: auto;
            border-top-right-radius: 0;
        }
        
        .message-received {
            align-self: flex-start;
            background-color: #f1f1f1;
            border-top-left-radius: 0;
        }
        
        .message p {
            margin: 0;
        }
        
        .message-time {
            font-size: 0.7em;
            color: #777;
            text-align: right;
            margin-top: 5px;
        }
        
        .highlight-row {
            background-color: #FFF5E1;
        }
        
        .message-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .message-form textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            resize: none;
        }
        
        .message-form button {
            align-self: flex-end;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mt-2 {
            margin-top: 1rem;
        }
        
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Status indicators */
        .status-unread {
            font-weight: bold;
            color: #dc3545;
        }
        
        .status-read {
            color: #6c757d;
        }
        
        .status-unavailable {
            color: #dc3545;
            font-style: italic;
        }
        
        .new-contacts {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        
        .contact-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
            width: 180px;
        }
        
        .contact-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .contact-name {
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
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
            <li><a href="dashboard_msgs.php">Messages</a>
                <?php if($unread_count > 0): ?>
                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </li>
            <li><a href="dashboard_settings.php">Settings</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="dashboard-header animate-fadeIn">
        <h1>Messages</h1>
        <hr>
        <p>Check and send messages to your matches. You can only message users who have accepted your friend request.</p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <section class="dashboard-section animate-slideIn">
        <h2>Search Conversations</h2>
        <form class="form-container" method="GET" action="dashboard_msgs.php">
            <div class="flex-container gap-1">
                <div class="form-group" style="flex: 1;">
                    <input type="text" name="search" placeholder="Search by name" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <?php if(!empty($search)): ?>
                    <a href="dashboard_msgs.php" class="btn">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <?php if (count($new_contacts) > 0): ?>
    <section class="dashboard-section">
        <h2>New Contacts</h2>
        <p>Start a conversation with these accepted contacts:</p>
        <div class="new-contacts">
            <?php foreach ($new_contacts as $contact): ?>
                <div class="contact-card">
                    <img src="<?php echo htmlspecialchars($contact['profile_picture']); ?>" alt="<?php echo htmlspecialchars($contact['name']); ?>">
                    <div class="contact-name"><?php echo htmlspecialchars($contact['name']); ?></div>
                    <form method="POST" action="dashboard_msgs.php">
                        <input type="hidden" name="start_conversation" value="1">
                        <input type="hidden" name="recipient_id" value="<?php echo $contact['id']; ?>">
                        <button type="submit" class="btn btn-primary">Start Chat</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="dashboard-section">
        <h2>Inbox</h2>
        <table class="data-table">
            <tr>
                <th>Profile</th>
                <th>Name</th>
                <th>Last Message</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php if (count($conversations) > 0): ?>
                <?php foreach ($conversations as $row): ?>
                    <?php 
                        $highlight = ($row['conversation_id'] == $current_conversation) ? 'highlight-row' : '';
                        $status_class = $row['is_friend'] == 0 ? 'status-unavailable' : ($row['unread_count'] > 0 ? 'status-unread' : 'status-read');
                        $status = $row['is_friend'] == 0 ? 'Not available' : ($row['unread_count'] > 0 ? 'Unread' : 'Read');
                        $time = !empty($row['time']) ? formatTime($row['time']) : 'No messages yet';
                    ?>
                    <tr class="<?php echo $highlight; ?>">
                        <td><img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="profile-img"></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo !empty($row['last_message']) ? htmlspecialchars($row['last_message']) : 'No messages yet'; ?></td>
                        <td><?php echo $time; ?></td>
                        <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
                        <td><a href="?conversation_id=<?php echo $row['conversation_id']; ?>" class="btn btn-small">Open Chat</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No conversations found. Start by connecting with matches and becoming friends.</td></tr>
            <?php endif; ?>
        </table>
    </section>

    <?php if ($current_conversation): ?>
    <section class="dashboard-section">
        <h2>Chat with <?php echo htmlspecialchars($other_user_name); ?></h2>
        <div class="chat-container" id="chat-container">
            <?php if (count($messages) > 0): ?>
                <?php foreach ($messages as $message): ?>
                    <?php $sender = $message['message_type'] == 'sent' ? 'You' : $message['sender_name']; ?>
                    <div class="message message-<?php echo $message['message_type']; ?>">
                        <p><b><?php echo htmlspecialchars($sender); ?>:</b> <?php echo htmlspecialchars($message['message_text']); ?></p>
                        <div class="message-time"><?php echo formatTime($message['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="typing-indicator">No messages yet. Start the conversation!</div>
            <?php endif; ?>
        </div>

        <?php if ($can_message): ?>
        <form class="message-form mt-2" method="POST" action="dashboard_msgs.php">
            <input type="hidden" name="conversation_id" value="<?php echo $current_conversation; ?>">
            <textarea name="message_text" rows="3" placeholder="Type your message here..." required></textarea>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
        <?php else: ?>
        <div class="alert alert-error mt-2">
            You cannot send messages to this user because there is no accepted friend request between you.
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <footer class="footer">
        <h2>Nikkah Junction</h2>
        <p>Connecting Hearts Through Faith</p>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>

    <script src="dashboard_msgs.js"></script>
</body>
</html>