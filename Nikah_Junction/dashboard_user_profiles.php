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

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize variables for filtering
$search = $gender = $location = "";
$age_min = $age_max = 0;

// Process filter inputs if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["search"])) {
        $search = sanitize_input($_POST["search"]);
    }
    if (isset($_POST["gender"])) {
        $gender = sanitize_input($_POST["gender"]);
    }
    if (isset($_POST["age_min"])) {
        $age_min = (int)sanitize_input($_POST["age_min"]);
    }
    if (isset($_POST["age_max"])) {
        $age_max = (int)sanitize_input($_POST["age_max"]);
    }
    if (isset($_POST["location"])) {
        $location = sanitize_input($_POST["location"]);
    }
}

// Pagination settings
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Build the SQL query with JOINs and filters
$sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.phone, u.last_login,
               up.profile_picture, up.dob, up.gender, up.country, up.address,
               up.marital_status, up.religion, up.education, up.occupation,
               fr.status as request_status, fr.sender_id, fr.receiver_id
        FROM users u
        LEFT JOIN user_profiles up ON u.id = up.id
        LEFT JOIN friend_requests fr ON (fr.sender_id = ? AND fr.receiver_id = u.id) OR (fr.sender_id = u.id AND fr.receiver_id = ?)
        WHERE 1=1";

$count_sql = "SELECT COUNT(*) as total FROM users u 
             LEFT JOIN user_profiles up ON u.id = up.id
             WHERE 1=1";

// Add filters to the query if provided
if (!empty($search)) {
    $search_term = "%{$search}%";
    $sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?)";
    $count_sql .= " AND (u.firstname LIKE ? OR u.lastname LIKE ? OR u.email LIKE ?)";
}

if ($gender != "all" && !empty($gender)) {
    $sql .= " AND up.gender = ?";
    $count_sql .= " AND up.gender = ?";
}

if (!empty($location)) {
    $location_term = "%{$location}%";
    $sql .= " AND (up.country LIKE ? OR up.address LIKE ?)";
    $count_sql .= " AND (up.country LIKE ? OR up.address LIKE ?)";
}

if ($age_min > 0) {
    $sql .= " AND TIMESTAMPDIFF(YEAR, up.dob, CURDATE()) >= ?";
    $count_sql .= " AND TIMESTAMPDIFF(YEAR, up.dob, CURDATE()) >= ?";
}

if ($age_max > 0) {
    $sql .= " AND TIMESTAMPDIFF(YEAR, up.dob, CURDATE()) <= ?";
    $count_sql .= " AND TIMESTAMPDIFF(YEAR, up.dob, CURDATE()) <= ?";
}

// Exclude the current user from the results
$sql .= " AND u.id != ?";

// Add pagination
$sql .= " ORDER BY u.id DESC LIMIT ?, ?";

// Prepare the statements
$stmt = $conn->prepare($sql);
$count_stmt = $conn->prepare($count_sql);

// Bind parameters for the main query
$current_user_id = $_SESSION['user_id'];
$param_types = "ii"; // For the friend_requests JOIN parameters
$params = [$current_user_id, $current_user_id];

if (!empty($search)) {
    $param_types .= "sss";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($gender != "all" && !empty($gender)) {
    $param_types .= "s";
    $params[] = $gender;
}

if (!empty($location)) {
    $param_types .= "ss";
    $params[] = $location_term;
    $params[] = $location_term;
}

if ($age_min > 0) {
    $param_types .= "i";
    $params[] = $age_min;
}

if ($age_max > 0) {
    $param_types .= "i";
    $params[] = $age_max;
}

// Add current_user_id parameter for excluding current user from results
$param_types .= "i";
$params[] = $current_user_id;

// Add pagination parameters to main query
$param_types .= "ii";
$params[] = $offset;
$params[] = $results_per_page;

// Bind parameters for the main query
if (!empty($params)) {
    $bind_params = array_merge([$param_types], $params);
    $ref_params = [];
    
    foreach ($bind_params as $key => $value) {
        $ref_params[$key] = &$bind_params[$key];
    }
    
    call_user_func_array([$stmt, 'bind_param'], $ref_params);
}

// Bind parameters for the count query
$count_param_types = "";
$count_params = [];

if (!empty($search)) {
    $count_param_types .= "sss";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

if ($gender != "all" && !empty($gender)) {
    $count_param_types .= "s";
    $count_params[] = $gender;
}

if (!empty($location)) {
    $count_param_types .= "ss";
    $count_params[] = $location_term;
    $count_params[] = $location_term;
}

if ($age_min > 0) {
    $count_param_types .= "i";
    $count_params[] = $age_min;
}

if ($age_max > 0) {
    $count_param_types .= "i";
    $count_params[] = $age_max;
}

// Bind parameters for the count query
if (!empty($count_params)) {
    $count_bind_params = array_merge([$count_param_types], $count_params);
    $count_ref_params = [];
    
    foreach ($count_bind_params as $key => $value) {
        $count_ref_params[$key] = &$count_bind_params[$key];
    }
    
    call_user_func_array([$count_stmt, 'bind_param'], $count_ref_params);
}

// Execute count query
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_results = $count_row['total'];
$total_pages = ceil($total_results / $results_per_page);

// Execute main query
$stmt->execute();
$result = $stmt->get_result();

// Calculate age from DOB
function calculateAge($dob) {
    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $today->diff($birthdate)->y;
    return $age;
}



// Get friendship status between current user and profile user
function getFriendshipStatus($request_status, $sender_id, $receiver_id, $current_user_id) {
    if (empty($request_status)) {
        return "none";
    }
    
    // If the status is approved, return approved regardless of who sent it
    if ($request_status == "approved") {
        return "approved";
    } else if ($request_status == "rejected") {
        return "rejected";
    } else if ($request_status == "pending") {
        // If the current user is the sender, they sent the request
        if ($sender_id == $current_user_id) {
            return "sent";
        } else {
            return "received";
        }
    }
    
    return "none";
}

// Function to display profile image with fallback
function getProfileImagePath($profile_picture, $gender) {
    if (!empty($profile_picture) && file_exists("uploads/profiles/{$profile_picture}")) {
        return "uploads/profiles/{$profile_picture}";
    } else {
        // Default image based on gender
        return $gender === "Female" ? "assets/images/female_default.jpg" : "assets/images/male_default.jpg";
    }
}

// Display success/error messages if available
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear the session messages after displaying
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    unset($_SESSION['error_message']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profiles | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles for status messages */
        .message-container {
            margin: 20px 0;
        }
        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
         Status badges
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-approved {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .status-rejected {
            background-color: #f2dede;
            color: #a94442;
        }
        .status-pending {
            background-color: #fcf8e3;
            color: #8a6d3b;
        } 
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>NIKKAH JUNCTION</h1>
        <p class="tagline">Laakhon Verified Rishtey Ab Aik Click Par!</p>
    </div>

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

    <header class="dashboard-header">
        <h1>User Profiles</h1>
        <p>Here you can view, manage, and filter user profiles.</p>
    </header>

    <?php if (!empty($success_message) || !empty($error_message)): ?>
    <div class="message-container">
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <section class="dashboard-section">
        <h2>Search & Filters</h2>
        <form class="form-container" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="flex-container gap-2">
                <div class="form-group">
                    <input type="text" name="search" placeholder="Search by name or email" value="<?php echo $search; ?>">
                </div>
                
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="all" <?php if($gender == "all" || empty($gender)) echo "selected"; ?>>All</option>
                        <option value="Male" <?php if($gender == "Male") echo "selected"; ?>>Male</option>
                        <option value="Female" <?php if($gender == "Female") echo "selected"; ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="age">Age Range:</label>
                    <div class="flex-container gap-1">
                        <input type="number" id="age_min" name="age_min" placeholder="Min Age" value="<?php echo $age_min > 0 ? $age_min : ''; ?>">
                        <input type="number" id="age_max" name="age_max" placeholder="Max Age" value="<?php echo $age_max > 0 ? $age_max : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location:</label>
                    <input type="text" id="location" name="location" placeholder="City, Country" value="<?php echo $location; ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn">Reset</a>
            </div>
        </form>
    </section>

    <section class="dashboard-section">
        <h2>Registered Users</h2>
        <table class="data-table">
            <tr>
                <th>Profile Picture</th>
                <th>Name</th>
                <th>Email</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Location</th>
                <!-- <th>Status</th> -->
                <th>Friendship Status</th>
                <th>Actions</th>
            </tr>
            
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $age = !empty($row['dob']) ? calculateAge($row['dob']) : 'N/A';
                    $fullname = $row['firstname'] . ' ' . $row['lastname'];
                    $location = !empty($row['country']) ? $row['country'] : '';
                    if (!empty($row['address'])) {
                        $address_parts = explode(',', $row['address']);
                        $city = trim(end($address_parts));
                        $location = !empty($city) ? $city . ', ' . $location : $location;
                    }
                    $profile_img = getProfileImagePath($row['profile_picture'], $row['gender']);
                    
                    // Get friendship status
                    $friendship_status = getFriendshipStatus(
                        $row['request_status'], 
                        $row['sender_id'], 
                        $row['receiver_id'], 
                        $current_user_id
                    );
            ?>
            <tr>
                <td><img src="<?php echo $profile_img; ?>" alt="<?php echo $fullname; ?>" width="50"></td>
                <td><?php echo $fullname; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $age; ?></td>
                <td><?php echo !empty($row['gender']) ? $row['gender'] : 'N/A'; ?></td>
                <td><?php echo !empty($location) ? $location : 'N/A'; ?></td>
                
                <td>
                    <?php 
                    if ($friendship_status == "approved") {
                        echo '<span class="status-badge status-approved">Approved</span>';
                    } else if ($friendship_status == "rejected") {
                        echo '<span class="status-badge status-rejected">Rejected</span>';
                    } else if ($friendship_status == "sent") {
                        echo '<span class="status-badge status-pending">Request Sent</span>';
                    } else if ($friendship_status == "received") {
                        echo '<span class="status-badge status-pending">Request Received</span>';
                    } else {
                        echo 'No Request';
                    }
                    ?>
                </td>
                <td class="table-actions">
                    <a href="view_profile.php?id=<?php echo $row['id']; ?>">View</a>
                    
                    <?php if ($friendship_status === "none" || $friendship_status === "rejected"): ?>
                        <a href="approve_profile.php?id=<?php echo $row['id']; ?>">Approve</a>
                        <?php if ($friendship_status !== "rejected"): ?>
                            <a href="reject_profile.php?id=<?php echo $row['id']; ?>">Reject</a>
                        <?php endif; ?>
                    <?php elseif ($friendship_status === "received"): ?>
                        <a href="approve_profile.php?id=<?php echo $row['id']; ?>">Accept</a>
                        <a href="reject_profile.php?id=<?php echo $row['id']; ?>">Reject</a>
                    <?php elseif ($friendship_status === "sent" || $friendship_status === "approved"): ?>
                        <!-- Don't show approve/reject buttons if user already sent a request or already approved -->
                    <?php endif; ?>
                    
                    <a href="dashboard_msgs.php?id=<?php echo $row['id']; ?>">Message</a>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo '<tr><td colspan="9" class="no-results">No user profiles found.</td></tr>';
            }
            ?>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . ($page - 1); ?>" class="btn btn-small">Previous</a>
            <?php else: ?>
            <button class="btn btn-small" disabled>Previous</button>
            <?php endif; ?>
            
            <span>Page <?php echo $page; ?> of <?php echo $total_pages > 0 ? $total_pages : 1; ?></span>
            
            <?php if ($page < $total_pages): ?>
            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?page=' . ($page + 1); ?>" class="btn btn-small">Next</a>
            <?php else: ?>
            <button class="btn btn-small" disabled>Next</button>
            <?php endif; ?>
        </div>
    </section>

    <footer class="footer">
        <h2>Laakhon Verified Rishtey <br> Ab Aik Click Par!</h2>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; <?php echo date('Y'); ?> Nikkah Junction. All rights reserved.</p>
    </footer>
    
    <script>
    // Simple JavaScript to handle the profile preview functionality
document.addEventListener('DOMContentLoaded', function() {

    
    // Auto-hide success and error messages after 5 seconds
    setTimeout(function() {
        const successMsg = document.querySelector('.success-message');
        const errorMsg = document.querySelector('.error-message');
        
        if (successMsg) {
            successMsg.style.display = 'none';
        }
        
        if (errorMsg) {
            errorMsg.style.display = 'none';
        }
    }, 5000);
});
</script>
</body>
</html>
<?php
// Close database connection
$stmt->close();
$count_stmt->close();
if (isset($preview_stmt)) {
    $preview_stmt->close();
}
$conn->close();
?> 