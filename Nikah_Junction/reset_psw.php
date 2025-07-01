<?php
    session_start();
    
    // Only process if this is a POST request with reset parameter
    if(isset($_POST["reset"])) {
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "nikah_junction";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            $_SESSION['error'] = "Database connection failed: " . $conn->connect_error;
            header("Location: reset_psw.php");
            exit;
        }

        // Get and validate input
        if(isset($_POST["password"]) && !empty($_POST["password"]) && isset($_POST["email"]) && !empty($_POST["email"])) {
            $psw = $_POST["password"];
            $email = $_POST["email"];
            
            // Hash the password
            $hash = password_hash($psw, PASSWORD_DEFAULT);
            
            // First check if the user exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if($result->num_rows == 0) {
                $_SESSION['error'] = "No account found with this email address.";
                header("Location: reset_psw.php");
                exit;
            }
            $check_stmt->close();
            
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE email=?");
            
            if(!$stmt) {
                $_SESSION['error'] = "Failed to prepare statement: " . $conn->error;
                header("Location: reset_psw.php");
                exit;
            }
            
            $stmt->bind_param("ss", $hash, $email);
            
            if($stmt->execute()) {
                // Check if any rows were affected
                if($stmt->affected_rows > 0) {
                    // Password updated successfully
                    $_SESSION['success'] = "Your password has been successfully reset";
                    header("Location: login.php");
                    exit;
                } else {
                    $_SESSION['error'] = "No changes were made. Please try again.";
                    header("Location: reset_psw.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Failed to update password: " . $stmt->error;
                header("Location: reset_psw.php");
                exit;
            }
            
            $stmt->close();
        } else {
            $_SESSION['error'] = "Password and email are required";
            header("Location: reset_psw.php");
            exit;
        }
        
        $conn->close();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css" />
</head>
<body>
    <div class="header animate-fadeIn">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>NIKKAH JUNCTION</h1>
        <p class="tagline">Laakhon Verified Rishtey Ab Aik Click Par!</p>
    </div>

    <div class="container">
        <div class="form-container animate-slideIn">
            <h2 class="text-center">Reset Your Password</h2>
            <hr>
            
            <?php
            // Display any error messages
            if(isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            
            // Display success message
            if(isset($_SESSION['success'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" required>
                        <i class="bi bi-eye-slash" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" name="reset">Reset Password</button>
                </div>
            </form>
            
            <p class="text-center mt-2">
                <a href="login.php">Back to Login</a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <h2>Laakhon Verified Rishtey <br> Ab Aik Click Par!</h2>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>
    
    <script type="text/javascript">
        const togglePassword = document.querySelector("#togglePassword");
        const password = document.querySelector("#password");

        togglePassword.addEventListener("click", function () {
            // Toggle the type attribute
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);
            
            // Toggle the icon
            this.classList.toggle("bi-eye");
            this.classList.toggle("bi-eye-slash");
        });
    </script>
</body>
</html>