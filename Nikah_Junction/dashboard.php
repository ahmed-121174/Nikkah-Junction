<?php

// Start the session
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: index.html");
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

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get user information
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Get total users count
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users_row = mysqli_fetch_assoc($total_users_result);
$total_users = $total_users_row['total'];

// Get new users this week
$new_users_query = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
$new_users_result = mysqli_query($conn, $new_users_query);
$new_users_row = mysqli_fetch_assoc($new_users_result);
$new_users = $new_users_row['new_users'];

// Matches data - Using the field values from the image
$total_matches = 150;
$pending_requests = 25;

// Messages data
$messages_query = "SELECT COUNT(*) as total FROM messages WHERE recipient_id = $user_id OR sender_id = $user_id";
$messages_result = mysqli_query($conn, $messages_query);
$messages_row = mysqli_fetch_assoc($messages_result);
$total_messages = $messages_row['total'] ? $messages_row['total'] : 85; // Default value from image if query fails

// Get unread messages
$unread_query = "SELECT COUNT(*) as unread FROM messages WHERE recipient_id = $user_id AND is_read = 0";
$unread_result = mysqli_query($conn, $unread_query);
$unread_row = mysqli_fetch_assoc($unread_result);
$unread_messages = $unread_row['unread'] ? $unread_row['unread'] : 10; // Default value if query fails

// Pending approvals data from image
$pending_approvals = 12;
$profiles_review = 3;

// Get last login and account creation date
$login_query = "SELECT last_login, created_at FROM users WHERE id = $user_id";
$login_result = mysqli_query($conn, $login_query);
$login_info = mysqli_fetch_assoc($login_result);
$last_login = date('M d, Y, h:i A', strtotime($login_info['last_login']));
$account_created = date('M d, Y', strtotime($login_info['created_at']));

// Profile completion - default value based on image
$profile_completion = 65;

// Recent activities data - based on the image
$recent_activities = [
    ['id' => 1, 'user' => 'Mahmud Jafri', 'activity' => 'Updated Profile', 'date' => 'Feb 15, 2025', 'details' => 'Changed profile picture'],
    ['id' => 2, 'user' => 'Sheila Khan', 'activity' => 'Updated Profile', 'date' => 'Feb 25, 2025', 'details' => 'Changed profile picture'],
    ['id' => 3, 'user' => 'Jamal Raza', 'activity' => 'Updated Profile', 'date' => 'Feb 20, 2025', 'details' => 'Changed profile picture'],
    ['id' => 4, 'user' => 'Fateh Hayat', 'activity' => 'Updated Profile', 'date' => 'Feb 3, 2025', 'details' => 'Changed profile picture'],
    ['id' => 5, 'user' => 'Ghulam', 'activity' => 'Sent a Message', 'date' => 'Feb 23, 2025', 'details' => 'Message sent to user ID 1993'],
    ['id' => 6, 'user' => 'Rabeya Khatun', 'activity' => 'New Match Found', 'date' => 'Feb 21, 2025', 'details' => 'Matched with John Doe'],
    ['id' => 7, 'user' => 'Ijaz Ahmad', 'activity' => 'Profile Updated', 'date' => 'Feb 22, 2025', 'details' => 'Added verified profile details']
];

// Chart data for weekly user activity
$chart_data = [
    'labels' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    'values' => [65, 59, 80, 81, 56, 55, 40]
];

// Handle AJAX search request
if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $search_query = "SELECT id as user_id, firstname, lastname, email FROM users
                    WHERE firstname LIKE '%$search%'
                    OR lastname LIKE '%$search%' 
                    OR email LIKE '%$search%'
                    LIMIT 10";
    
    $search_result = mysqli_query($conn, $search_query);
    
    $results = array();
    while($row = mysqli_fetch_assoc($search_result)) {
        // Add dummy data for display purposes
        $row['full_name'] = $row['firstname'] . ' ' . $row['lastname'];
        $row['age'] = rand(21, 45);  // Random age
        $row['city'] = ['Karachi', 'Lahore', 'Islamabad', 'Dubai', 'London'][rand(0, 4)]; // Random city
        $row['profile_pic'] = 'default-avatar.png';
        $results[] = $row;
    }
    
    echo json_encode($results);
    exit();
}

// Handle AJAX refresh request
if(isset($_GET['refresh']) && $_GET['refresh'] == 'true') {
    $response = [
        'total_users' => $total_users,
        'new_users' => $new_users,
        'total_matches' => $total_matches,
        'pending_requests' => $pending_requests,
        'total_messages' => $total_messages,
        'unread_messages' => $unread_messages,
        'pending_approvals' => $pending_approvals,
        'profiles_review' => $profiles_review,
        'activities' => $recent_activities
    ];
    
    echo json_encode($response);
    exit();
}

// Get chart data endpoint
if(isset($_GET['chart_data']) && $_GET['chart_data'] == 'true') {
    echo json_encode($chart_data);
    exit();
}

// Get notifications endpoint
if(isset($_GET['notifications']) && $_GET['notifications'] == 'true') {
    $notifications = [
        ['id' => 1, 'message' => 'You have a new match!'],
        ['id' => 2, 'message' => 'Your profile has been verified'],
        ['id' => 3, 'message' => 'You have 5 unread messages']
    ];
    
    echo json_encode(['notifications' => $notifications]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional CSS from dashboard.js */
        .dashboard-time {
            text-align: right;
            margin-top: 10px;
            font-size: 0.9em;
            color: #666;
        }
        
        .chart-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .notification-area {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .notification-badge {
            background: #E6A847;
            color: #333;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .notification-panel {
            display: none;
            position: absolute;
            top: 30px;
            right: 0;
            width: 300px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            border-radius: 8px;
            padding: 15px;
        }
        
        .notification-panel.show {
            display: block;
        }
        
        .notification-panel h3 {
            margin-top: 0;
        }
        
        .notification-panel ul {
            list-style-type: none;
            padding: 0;
        }
        
        .notification-panel li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            position: relative;
        }
        
        .mark-read-btn {
            position: absolute;
            right: 0;
            top: 8px;
            background: #E6A847;
            border: none;
            border-radius: 4px;
            color: white;
            padding: 2px 5px;
            font-size: 0.8em;
            cursor: pointer;
        }
        
        .dashboard-search {
            display: flex;
            margin-bottom: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .dashboard-search input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px 0 0 8px;
            font-size: 16px;
        }
        
        .dashboard-search button {
            border-radius: 0 8px 8px 0;
            padding: 10px 20px;
        }
        
        .search-results-container {
            display: none;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
        }
        
        .search-results-container h3 {
            margin-top: 0;
            color: #8B4513;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .user-search-results {
            list-style: none;
            padding: 0;
        }
        
        .user-search-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-info h4 {
            margin: 0 0 5px 0;
        }
        
        .user-info p {
            margin: 0;
            color: #666;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 0.9em;
        }
        
        #searchResults p {
            font-style: italic;
            color: #666;
        }
        
        .dark-mode-toggle {
            position: fixed;
            bottom: 30px;
            right: 20px;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background: #8B4513;
            color: white;
            font-size: 20px;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .stat-card.updating {
            transition: background-color 0.5s;
            background-color: #ffffd0;
        }
        
        .dark-mode {
            background-color: #333;
            color: #f0f0f0;
        }
        
        .dark-mode .dashboard-section, 
        .dark-mode .dashboard-header,
        .dark-mode .stat-card,
        .dark-mode .chart-container,
        .dark-mode .notification-panel,
        .dark-mode .search-results-container {
            background-color: #444;
            color: #f0f0f0;
        }
        
        .dark-mode .data-table th {
            background-color: #555;
        }
        
        .dark-mode .data-table tr:nth-child(even) {
            background-color: #505050;
        }
        
        .dark-mode .data-table tr:hover {
            background-color: #606060;
        }
        
        .dark-mode .user-search-item {
            border-bottom-color: #555;
        }
        
        .dark-mode .user-info p {
            color: #ccc;
        }
        
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            pointer-events: none;
            animation: ripple-animation 0.6s linear;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        /* Animation classes */
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .animate-slideIn {
            animation: slideIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>NIKKAH JUNCTION</h1>
        <p class="tagline">Connecting Hearts Through Faith</p>
    </div>

    <!-- Navigation Menu -->
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

    <div class="dashboard-header animate-fadeIn">
        <h1>Welcome to Your Dashboard<?php echo ", " . htmlspecialchars($user['firstname'] . " " . $user['lastname']); ?></h1>
        <hr>
        <p>Manage your account efficiently. View insights, track activities, and manage your connections with ease.</p>
        <div class="dashboard-time">
            <!-- Time will be inserted by JS -->
        </div>
    </div>
    
    <!-- Search form - moved from JS -->
    <form class="dashboard-search">
        <input type="text" id="searchInput" placeholder="Search users by name">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <section class="dashboard-section animate-slideIn">
        <h2>Overview</h2>
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Registered Users</h3>
                <p><?php echo $total_users; ?></p>
                <small>New users this week: <?php echo $new_users; ?></small>
            </div>

            <div class="stat-card">
                <h3>New Matches</h3>
                <p><?php echo $total_matches; ?></p>
                <small>Pending Requests: <?php echo $pending_requests; ?></small>
            </div>

            <div class="stat-card">
                <h3>Messages</h3>
                <p><?php echo $total_messages; ?></p>
                <small>Unread Messages: <?php echo $unread_messages; ?></small>
            </div>

            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <p><?php echo $pending_approvals; ?></p>
                <small>Profiles Under Review: <?php echo $profiles_review; ?></small>
            </div>
        </div>
    </section>
    
    <!-- Chart container - moved from JS -->
    <div class="chart-container">
        <canvas id="userActivityChart" width="400" height="200"></canvas>
    </div>

    <!-- Recent Activities Section -->
    <section class="dashboard-section">
        <h2>Recent Activities</h2>
        <table class="data-table" id="activities-table">
            <tr>
                <th>User</th>
                <th>Activity</th>
                <th>Date</th>
                <th>Details</th>
            </tr>
            <?php foreach($recent_activities as $activity) { ?>
            <tr>
                <td><?php echo htmlspecialchars($activity['user']); ?></td>
                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                <td><?php echo htmlspecialchars($activity['date']); ?></td>
                <td><?php echo htmlspecialchars($activity['details']); ?></td>
            </tr>
            <?php } ?>
        </table>
        <button class="btn btn-secondary" id="exportBtn">Export Data</button>
    </section>

    <section class="dashboard-section">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="dashboard_user_profiles.php" class="btn btn-primary">View & Manage User Profiles</a>
            <a href="dashboard_matches.php" class="btn btn-primary">Check New Matches</a>
            <a href="dashboard_msgs.php" class="btn btn-primary">Open Messages</a>
            <a href="dashboard_settings.php" class="btn btn-primary">Update Account Settings</a>
        </div>
    </section>

    <section class="dashboard-section">
        <h2>Account Statistics</h2>
        <p><strong>Last Login:</strong> <?php echo $last_login; ?></p>
        <p><strong>Account Created:</strong> <?php echo $account_created; ?></p>
        <p><strong>Profile Completion:</strong> <?php echo $profile_completion; ?>%</p>
    </section>

    <div id="searchResultsContainer" class="search-results-container">
        <h3>Search Results</h3>
        <div id="searchResults" class="search-results-list"></div>
    </div>
    

    <footer class="footer">
        <h2>Nikkah Junction</h2>
        <p>Connecting Hearts Through Faith</p>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>
    <script type="text/javascript" src="dashboard.js" defer></script>
</body>
</html>

<?php
// Close database connection
mysqli_close($conn);
?>