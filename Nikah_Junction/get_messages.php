<?php

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check if conversation_id is provided
if (!isset($_GET['conversation_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No conversation ID provided']);
    exit;
}

$conversation_id = intval($_GET['conversation_id']);
$user_id = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nikah_junction";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Verify the user is a member of this conversation
$query = "SELECT COUNT(*) as count FROM conversation_members 
          WHERE conversation_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $conversation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}



$messages = [];
while ($message = $result->fetch_assoc()) {
    // Clean message data for JSON response
    $messages[] = [
        'message_id' => $message['message_id'],
        'message_type' => $message['message_type'],
        'message_text' => $message['message_text'],
        'sender_name' => $message['sender_name'],
        'created_at' => $message['created_at']
    ];
}

// Mark all received messages as read
$update_query = "UPDATE messages SET is_read = 1 
                WHERE conversation_id = ? AND recipient_id = ?";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bind_param("ii", $conversation_id, $user_id);
$update_stmt->execute();

// Return messages as JSON
header('Content-Type: application/json');
echo json_encode(['success' => true, 'messages' => $messages]);
exit;
?>