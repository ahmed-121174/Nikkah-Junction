<?php
// create_conversation.php - Creates a new conversation or returns an existing one

session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['recipient_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$recipient_id = intval($_POST['recipient_id']);

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

// First, check if a conversation already exists between these two users
$query = "SELECT c.conversation_id FROM conversations c
          JOIN conversation_members cm1 ON c.conversation_id = cm1.conversation_id
          JOIN conversation_members cm2 ON c.conversation_id = cm2.conversation_id
          WHERE cm1.user_id = ? AND cm2.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $recipient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Conversation already exists, return its ID
    $row = $result->fetch_assoc();
    $conversation_id = $row['conversation_id'];
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'conversation_id' => $conversation_id, 'is_new' => false]);
    exit;
}

// No existing conversation, create a new one
$conn->begin_transaction();

try {
    // Insert new conversation
    $query = "INSERT INTO conversations (created_at, updated_at) VALUES (NOW(), NOW())";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $conversation_id = $stmt->insert_id;
    
    // Add both users to the conversation
    $query = "INSERT INTO conversation_members (conversation_id, user_id, created_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $conversation_id, $user_id);
    $stmt->execute();
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $conversation_id, $recipient_id);
    $stmt->execute();
    
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'conversation_id' => $conversation_id, 'is_new' => true]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
    exit;
}
?>