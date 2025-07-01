<?php
// Start the session
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: home.html");
    exit();
}

// Check if ID is provided in the URL
if(!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect back to profiles page if ID is not provided
    header("Location: dashboard_user_profiles.php");
    exit();
}

// Get ID of the profile to approve
$profile_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];

// Dont allow self-approvals
if($profile_id == $current_user_id) {
    $_SESSION['error_message'] = "You cannot approve your own profile.";
    header("Location: dashboard_user_profiles.php");
    exit();
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

// Check if the user exists
$check_user_sql = "SELECT id FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_user_sql);
$check_stmt->bind_param("i", $profile_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if($check_result->num_rows === 0) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: dashboard_user_profiles.php");
    $check_stmt->close();
    $conn->close();
    exit();
}

// Check if a request already exists between these users
$check_request_sql = "SELECT * FROM friend_requests 
                     WHERE (sender_id = ? AND receiver_id = ?) 
                     OR (sender_id = ? AND receiver_id = ?)";
$request_stmt = $conn->prepare($check_request_sql);
$request_stmt->bind_param("iiii", $current_user_id, $profile_id, $profile_id, $current_user_id);
$request_stmt->execute();
$request_result = $request_stmt->get_result();

// If a request already exists, update it instead of creating a new one
if($request_result->num_rows > 0) {
    $request_row = $request_result->fetch_assoc();
    $request_id = $request_row['request_id'];
    $current_timestamp = date('Y-m-d H:i:s');
    
    $update_sql = "UPDATE friend_requests 
                  SET status = 'approved', updated_at = ? 
                  WHERE request_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $current_timestamp, $request_id);
    
    if($update_stmt->execute()) {
        $_SESSION['success_message'] = "Profile request has been approved successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating request: " . $conn->error;
    }
    
    $update_stmt->close();
} else {
    // Create a new approval request
    $current_timestamp = date('Y-m-d H:i:s');
    $status = "approved";
    
    $insert_sql = "INSERT INTO friend_requests (sender_id, receiver_id, status, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisss", $current_user_id, $profile_id, $status, $current_timestamp, $current_timestamp);
    
    if($insert_stmt->execute()) {
        $_SESSION['success_message'] = "Profile has been approved successfully.";
    } else {
        $_SESSION['error_message'] = "Error approving profile: " . $conn->error;
    }
    
    $insert_stmt->close();
}

// Close database connection
$check_stmt->close();
$request_stmt->close();
$conn->close();

// Redirect back to profiles page
header("Location: dashboard_user_profiles.php");
exit();
?>