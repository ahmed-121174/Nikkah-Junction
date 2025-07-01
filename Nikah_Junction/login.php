<?php
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

$error_message = "";  // Initialize error_message

if (isset($_POST["submit"])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    $sql = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $count = mysqli_num_rows($sql);

    if ($count > 0) {
        $fetch = mysqli_fetch_assoc($sql);
        $hashpassword = $fetch["password_hash"];

        if ($fetch["status"] == 0) {
            $error_message = "Please verify your email account before logging in.";
        } elseif (password_verify($password, $hashpassword)) {
            // Start session and set session variables
            session_start();
            $_SESSION['user_id'] = $fetch['id'];  
            $_SESSION['email'] = $fetch['email']; 
            // Redirect to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Email or password invalid, please try again.";
        }
    } else {
        $error_message = "No account found with this email.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nikah Junction</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px 15px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    text-align: center;
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="logo" class="logo">
        <h1 class="site-title">NIKAH JUNCTION</h1>
    </div>
    
    <div class="form-container animate-fadeIn">
        <h1 class="section-title text-center">Login Portal</h1>
        
        <?php if(!empty($error_message)): ?>
            <div class="error-message">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" placeholder="Enter email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            
            <div class="form-actions">
            <button type="submit" name="submit" class="btn btn-primary">Login</button>
            </div>
            
            <div class="text-center mt-2">
                <a href="recover_psw.php">Forgot Password?</a>
            </div>
        </form>
    </div>
    
    <div class="text-center mt-3">
        <p>Don't have an account?</p>
        <a href="signup.php" class="btn btn-secondary">Register Now</a>
    </div>
    <script type="text/javascript" src="login.js" defer></script>
</body>
</html>