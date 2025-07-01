<?php
session_start();
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
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize default filter values and pagination
$min_age = isset($_GET['min_age']) ? $_GET['min_age'] : '';
$max_age = isset($_GET['max_age']) ? $_GET['max_age'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$religion = isset($_GET['religion']) ? $_GET['religion'] : 'All';

// Pagination settings for filtered users
$users_per_page = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $users_per_page;

// Function to calculate age from date of birth
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

// Get Incoming Match Requests
$incoming_requests_query = "SELECT mr.request_id, mr.sender_id, mr.created_at, 
                           u.firstname, u.lastname, u.email, 
                           up.profile_picture, up.dob, up.gender, up.religion, up.country
                           FROM match_requests mr
                           JOIN users u ON mr.sender_id = u.id
                           JOIN user_profiles up ON u.id = up.id
                           WHERE mr.receiver_id = ? AND mr.status = 'pending'
                           ORDER BY mr.created_at DESC";

$stmt = $conn->prepare($incoming_requests_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$incoming_result = $stmt->get_result();
$incoming_requests = $incoming_result->fetch_all(MYSQLI_ASSOC);

// Get Your Sent Match Requests
$sent_requests_query = "SELECT mr.request_id, mr.receiver_id, mr.status, mr.created_at, 
                       u.firstname, u.lastname, u.email, 
                       up.profile_picture, up.dob, up.gender, up.religion, up.country
                       FROM match_requests mr
                       JOIN users u ON mr.receiver_id = u.id
                       JOIN user_profiles up ON u.id = up.id
                       WHERE mr.sender_id = ?
                       ORDER BY mr.created_at DESC";

$stmt = $conn->prepare($sent_requests_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sent_result = $stmt->get_result();
$sent_requests = $sent_result->fetch_all(MYSQLI_ASSOC);

// Get Your Matches (accepted requests)
$matches_query = "SELECT m.match_id, m.matched_at, m.match_status,
                 (CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END) as matched_user_id,
                 u.firstname, u.lastname, u.email,
                 up.profile_picture, up.dob, up.gender, up.religion, up.country
                 FROM matches m
                 JOIN users u ON (CASE WHEN m.user1_id = ? THEN m.user2_id ELSE m.user1_id END) = u.id
                 JOIN user_profiles up ON u.id = up.id
                 WHERE (m.user1_id = ? OR m.user2_id = ?) AND m.match_status = 'active'
                 ORDER BY m.matched_at DESC";

$stmt = $conn->prepare($matches_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$matches_result = $stmt->get_result();
$matches = $matches_result->fetch_all(MYSQLI_ASSOC);

// Get all distinct countries and religions for filter dropdowns
$countries_query = "SELECT DISTINCT country FROM user_profiles WHERE country != '' ORDER BY country";
$religions_query = "SELECT DISTINCT religion FROM user_profiles WHERE religion != '' ORDER BY religion";

$countries_result = $conn->query($countries_query);
$religions_result = $conn->query($religions_query);

// Apply Filters - Handle search results with pagination
if (isset($_GET['apply_filters'])) {
    $filter_conditions = [];
    $filter_params = [];
    $param_types = "";
    
    // Build the base query for count (for pagination)
    $count_query = "SELECT COUNT(*) as total FROM users u
                   JOIN user_profiles up ON u.id = up.id
                   WHERE u.id != ? AND u.is_active = 1";
    
    // Build the base query for filtered users
    $filter_query = "SELECT u.id, u.firstname, u.lastname, u.email, 
                    up.profile_picture, up.dob, up.gender, up.religion, up.country
                    FROM users u
                    JOIN user_profiles up ON u.id = up.id
                    WHERE u.id != ? AND u.is_active = 1";
    
    $param_types .= "i";
    $filter_params[] = $user_id;
    
    // Add min age filter if provided
    if (!empty($min_age)) {
        $max_dob = date('Y-m-d', strtotime('-'.$min_age.' years'));
        $filter_conditions[] = "up.dob <= ?";
        $param_types .= "s";
        $filter_params[] = $max_dob;
    }
    
    // Add max age filter if provided
    if (!empty($max_age)) {
        $min_dob = date('Y-m-d', strtotime('-'.$max_age.' years -1 day'));
        $filter_conditions[] = "up.dob >= ?";
        $param_types .= "s";
        $filter_params[] = $min_dob;
    }
    
    // Add location filter if provided
    if (!empty($location)) {
        $filter_conditions[] = "up.country = ?";
        $param_types .= "s";
        $filter_params[] = $location;
    }
    
    // Add religion filter if provided and not 'All'
    if (!empty($religion) && $religion != 'All') {
        $filter_conditions[] = "up.religion = ?";
        $param_types .= "s";
        $filter_params[] = $religion;
    }
    
    // Add conditions to both queries if there are any
    if (!empty($filter_conditions)) {
        $condition_string = " AND " . implode(" AND ", $filter_conditions);
        $count_query .= $condition_string;
        $filter_query .= $condition_string;
    }
    
    // Add pagination to filter query
    $filter_query .= " ORDER BY u.firstname ASC LIMIT ? OFFSET ?";
    $param_types .= "ii";
    $filter_params[] = $users_per_page;
    $filter_params[] = $offset;
    
    // Get total count for pagination
    $stmt = $conn->prepare($count_query);
    if (!empty($filter_params)) {
        // Remove the last two parameters (limit and offset) for the count query
        $count_params = array_slice($filter_params, 0, -2);
        $count_param_types = substr($param_types, 0, -2);
        $stmt->bind_param($count_param_types, ...$count_params);
    }
    $stmt->execute();
    $total_result = $stmt->get_result()->fetch_assoc();
    $total_users = $total_result['total'];
    $total_pages = ceil($total_users / $users_per_page);
    
    // Prepare and execute the filtered users query
    $stmt = $conn->prepare($filter_query);
    if (!empty($filter_params)) {
        $stmt->bind_param($param_types, ...$filter_params);
    }
    $stmt->execute();
    $filtered_results = $stmt->get_result();
    $filtered_users = $filtered_results->fetch_all(MYSQLI_ASSOC);
} else {
    $filtered_users = [];
    $total_users = 0;
    $total_pages = 1;
}

// Check for existing match requests to disable send button for already requested users
function hasExistingRequest($conn, $sender_id, $receiver_id) {
    $check_query = "SELECT request_id FROM match_requests WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $sender_id, $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Check if users are already matched
function isMatched($conn, $user1_id, $user2_id) {
    $check_query = "SELECT match_id FROM matches 
                   WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iiii", $user1_id, $user2_id, $user2_id, $user1_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Handle actions (Accept/Reject/Cancel requests)
if (isset($_POST['action']) && isset($_POST['request_id'])) {
    $action = $_POST['action'];
    $request_id = $_POST['request_id'];
    
    if ($action == 'accept') {
        // Get the sender ID from the request
        $get_sender_query = "SELECT sender_id FROM match_requests WHERE request_id = ?";
        $stmt = $conn->prepare($get_sender_query);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request_data = $result->fetch_assoc();
        
        if ($request_data) {
            $sender_id = $request_data['sender_id'];
            
            // Update request status to accepted
            $update_query = "UPDATE match_requests SET status = 'accepted', updated_at = NOW() WHERE request_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            
            // Create a new match
            $create_match_query = "INSERT INTO matches (user1_id, user2_id, matched_at, match_status) VALUES (?, ?, NOW(), 'active')";
            $stmt = $conn->prepare($create_match_query);
            $stmt->bind_param("ii", $sender_id, $user_id);
            $stmt->execute();
            
            // Log the activity
            $log_query = "INSERT INTO user_activity_log (user_id, action) VALUES (?, 'accepted_match_request')";
            $stmt = $conn->prepare($log_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Redirect to refresh the page
            header("Location: dashboard_matches.php");
            exit();
        }
    } elseif ($action == 'reject') {
        // Update request status to rejected
        $update_query = "UPDATE match_requests SET status = 'rejected', updated_at = NOW() WHERE request_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        
        // Log the activity
        $log_query = "INSERT INTO user_activity_log (user_id, action) VALUES (?, 'rejected_match_request')";
        $stmt = $conn->prepare($log_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        header("Location: dashboard_matches.php");
        exit();
    } elseif ($action == 'cancel') {
        // Delete the request
        $delete_query = "DELETE FROM match_requests WHERE request_id = ? AND sender_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $request_id, $user_id);
        $stmt->execute();
        
        // Log the activity
        $log_query = "INSERT INTO user_activity_log (user_id, action) VALUES (?, 'cancelled_match_request')";
        $stmt = $conn->prepare($log_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        header("Location: dashboard_matches.php");
        exit();
    }
}

// Handle sending a new match request
if (isset($_POST['send_request']) && isset($_POST['receiver_id'])) {
    $receiver_id = $_POST['receiver_id'];
    
    // Check if a request already exists
    $check_query = "SELECT * FROM match_requests WHERE sender_id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $user_id, $receiver_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        // Insert new request
        $insert_query = "INSERT INTO match_requests (sender_id, receiver_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $user_id, $receiver_id);
        $stmt->execute();
        
        // Log the activity
        $log_query = "INSERT INTO user_activity_log (user_id, action) VALUES (?, 'sent_match_request')";
        $stmt = $conn->prepare($log_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        header("Location: dashboard_matches.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches Dashboard - Nikah Junction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B4513;
            --secondary-color: #D2B48C;
            --accent-color: #FFD700;
            --light-color: #FFF8DC;
            --dark-color: #4A3728;
        }
        
        body {
            background-color: #FFF8E7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
            background-color: white;
            height: 100%;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .profile-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 3px solid var(--secondary-color);
        }
        
        .filter-container {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .section-title {
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 10px;
            margin-bottom: 25px;
            color: var(--dark-color);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 80px;
            height: 3px;
            background-color: var(--accent-color);
        }
        
        .action-btn {
            margin-right: 5px;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-color);
            border-color: var(--dark-color);
        }
        
        .btn-warning {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: var(--dark-color);
        }
        
        .btn-warning:hover {
            background-color: #FFCC00;
            border-color: #FFCC00;
            color: var(--dark-color);
        }
        
        .no-data-message {
            text-align: center;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            color: #666;
            font-style: italic;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .card-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 12px;
        }
        
        .card-text {
            color: #555;
            font-size: 0.9rem;
        }
        
        .card-text i {
            width: 20px;
            color: var(--primary-color);
        }
        
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        
        .page-link {
            color: var(--primary-color);
            border-color: var(--secondary-color);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .section-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        .section-container::-webkit-scrollbar {
            width: 5px;
        }
        
        .section-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .section-container::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 10px;
        }
        
        .section-container::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }
        
        .status-badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        .status-pending {
            background-color: #FFC107;
            color: #333;
        }
        
        .status-accepted {
            background-color: #28A745;
            color: white;
        }
        
        .status-rejected {
            background-color: #DC3545;
            color: white;
        }
        
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
        }
        
        .stat-card p {
            margin: 5px 0 0;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .dashboard-nav ul {
                flex-direction: column;
                gap: 10px;
            }
            
            .stats-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <nav class="dashboard-nav">
            <h2>Dashboard</h2>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="dashboard_user_profiles.php">User Profiles</a></li>
                <li><a href="dashboard_matches.php" style="background-color: var(--primary-color);">Matches</a></li>
                <li><a href="dashboard_msgs.php">Messages</a></li>
                <li><a href="dashboard_settings.php">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <!-- Stats Overview -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><?php echo count($incoming_requests); ?></h3>
                <p>Incoming Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($sent_requests); ?></h3>
                <p>Sent Requests</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($matches); ?></h3>
                <p>Matches</p>
            </div>
        </div>
        
        <!-- Filters Section -->
        <div class="filter-container">
            <h4><i class="fas fa-filter"></i> Find Your Match</h4>
            <form method="GET" action="dashboard_matches.php" class="row g-3">
                <div class="col-md-3">
                    <label for="min_age" class="form-label">Min Age</label>
                    <input type="number" class="form-control" id="min_age" name="min_age" value="<?php echo htmlspecialchars($min_age); ?>" min="18" max="100">
                </div>
                <div class="col-md-3">
                    <label for="max_age" class="form-label">Max Age</label>
                    <input type="number" class="form-control" id="max_age" name="max_age" value="<?php echo htmlspecialchars($max_age); ?>" min="18" max="100">
                </div>
                <div class="col-md-3">
                    <label for="location" class="form-label">Location</label>
                    <select class="form-select" id="location" name="location">
                        <option value="">All</option>
                        <?php while ($country = $countries_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($country['country']); ?>" <?php echo ($location == $country['country']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($country['country']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="religion" class="form-label">Religion</label>
                    <select class="form-select" id="religion" name="religion">
                        <option value="All">All</option>
                        <?php while ($rel = $religions_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($rel['religion']); ?>" <?php echo ($religion == $rel['religion']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($rel['religion']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" name="apply_filters" class="btn btn-warning">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="dashboard_matches.php" class="btn btn-light">
                        <i class="fas fa-undo"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Filter Results Section -->
        <?php if (isset($_GET['apply_filters'])): ?>
            <div class="mb-5">
                <h2 class="section-title">Filter Results</h2>
                <?php if (empty($filtered_users)): ?>
                    <div class="no-data-message">
                        <i class="fas fa-search fa-2x mb-3" style="color: #ccc;"></i>
                        <p>No users match your filter criteria.</p>
                    </div>
                <?php else: ?>
                    <div class="section-container">
                        <div class="row">
                            <?php foreach ($filtered_users as $user): ?>
                                <?php 
                                    $age = calculateAge($user['dob']);
                                    $profile_pic = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default_profile.jpg';
                                    $has_request = hasExistingRequest($conn, $user_id, $user['id']);
                                    $is_matched = isMatched($conn, $user_id, $user['id']);
                                ?>
                                <div class="col-md-4 mb-4">
                                    <div class="profile-card">
                                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="profile-image" alt="Profile">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h5>
                                            <p class="card-text">
                                                <i class="fas fa-birthday-cake"></i> <?php echo $age; ?> years<br>
                                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['country']); ?><br>
                                                <i class="fas fa-pray"></i> <?php echo htmlspecialchars($user['religion']); ?>
                                            </p>
                                            <div class="d-grid gap-2">
                                                <?php if (!$has_request && !$is_matched): ?>
                                                    <form method="POST" action="dashboard_matches.php">
                                                        <input type="hidden" name="receiver_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="send_request" class="btn btn-primary btn-sm w-100">
                                                            <i class="fas fa-heart"></i> Send Match Request
                                                        </button>
                                                    </form>
                                                <?php elseif ($is_matched): ?>
                                                    <button class="btn btn-success btn-sm w-100" disabled>
                                                        <i class="fas fa-check-circle"></i> Already Matched
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-secondary btn-sm w-100" disabled>
                                                        <i class="fas fa-clock"></i> Request Sent
                                                    </button>
                                                <?php endif; ?>
                                                <a href="view_profile.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm w-100">
                                                    <i class="fas fa-user"></i> View Profile
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Pagination for filtered users -->
                    <!-- Pagination for filtered results -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="dashboard_matches.php?apply_filters=1&min_age=<?php echo urlencode($min_age); ?>&max_age=<?php echo urlencode($max_age); ?>&location=<?php echo urlencode($location); ?>&religion=<?php echo urlencode($religion); ?>&page=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
<?php endif; ?>

<!-- Incoming Match Requests Section -->
<h2 class="section-title">Incoming Match Requests</h2>
<?php if (empty($incoming_requests)): ?>
    <div class="no-data-message">
        <i class="fas fa-inbox fa-2x mb-3" style="color: #ccc;"></i>
        <p>You don't have any incoming match requests at the moment.</p>
    </div>
<?php else: ?>
    <div class="section-container">
        <div class="row">
            <?php foreach ($incoming_requests as $request): ?>
                <?php 
                    $age = calculateAge($request['dob']);
                    $profile_pic = !empty($request['profile_picture']) ? $request['profile_picture'] : 'default_profile.jpg';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="profile-card">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="profile-image" alt="Profile">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($request['firstname'] . ' ' . $request['lastname']); ?></h5>
                            <p class="card-text">
                                <i class="fas fa-birthday-cake"></i> <?php echo $age; ?> years<br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($request['country']); ?><br>
                                <i class="fas fa-pray"></i> <?php echo htmlspecialchars($request['religion']); ?>
                            </p>
                            <div class="d-flex mt-3">
                                <form method="POST" action="dashboard_matches.php" class="me-2">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <button type="submit" name="action" value="accept" class="btn btn-success btn-sm action-btn">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                <form method="POST" action="dashboard_matches.php" class="me-2">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm action-btn">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                                <a href="view_profile.php?id=<?php echo $request['sender_id']; ?>" class="btn btn-info btn-sm action-btn">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Sent Match Requests Section -->
<h2 class="section-title">Sent Match Requests</h2>
<?php if (empty($sent_requests)): ?>
    <div class="no-data-message">
        <i class="fas fa-paper-plane fa-2x mb-3" style="color: #ccc;"></i>
        <p>You haven't sent any match requests yet.</p>
    </div>
<?php else: ?>
    <div class="section-container">
        <div class="row">
            <?php foreach ($sent_requests as $request): ?>
                <?php 
                    $age = calculateAge($request['dob']);
                    $profile_pic = !empty($request['profile_picture']) ? $request['profile_picture'] : 'default_profile.jpg';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="profile-card">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="profile-image" alt="Profile">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($request['firstname'] . ' ' . $request['lastname']); ?></h5>
                            <p class="card-text">
                                <i class="fas fa-birthday-cake"></i> <?php echo $age; ?> years<br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($request['country']); ?><br>
                                <i class="fas fa-pray"></i> <?php echo htmlspecialchars($request['religion']); ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="status-badge <?php echo 'status-' . $request['status']; ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                                <div>
                                    <?php if ($request['status'] == 'pending'): ?>
                                        <form method="POST" action="dashboard_matches.php" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                            <button type="submit" name="action" value="cancel" class="btn btn-warning btn-sm action-btn">
                                                <i class="fas fa-ban"></i> Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="view_profile.php?id=<?php echo $request['receiver_id']; ?>" class="btn btn-info btn-sm action-btn">
                                        <i class="fas fa-user"></i> View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Active Matches Section -->
<h2 class="section-title">Your Matches</h2>
<?php if (empty($matches)): ?>
    <div class="no-data-message">
        <i class="fas fa-heart fa-2x mb-3" style="color: #ccc;"></i>
        <p>You don't have any matches yet. Keep exploring profiles and sending requests!</p>
    </div>
<?php else: ?>
    <div class="section-container">
        <div class="row">
            <?php foreach ($matches as $match): ?>
                <?php 
                    $age = calculateAge($match['dob']);
                    $profile_pic = !empty($match['profile_picture']) ? $match['profile_picture'] : 'default_profile.jpg';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="profile-card">
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" class="profile-image" alt="Profile">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($match['firstname'] . ' ' . $match['lastname']); ?></h5>
                            <p class="card-text">
                                <i class="fas fa-birthday-cake"></i> <?php echo $age; ?> years<br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($match['country']); ?><br>
                                <i class="fas fa-pray"></i> <?php echo htmlspecialchars($match['religion']); ?>
                            </p>
                            <div class="d-grid gap-2">
                                <a href="conversation.php?user_id=<?php echo $match['matched_user_id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-comments"></i> Send Message
                                </a>
                                <a href="view_profile.php?id=<?php echo $match['matched_user_id']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-user"></i> View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        });
    </script>
</body>
</html>
