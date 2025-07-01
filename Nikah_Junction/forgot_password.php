<?php 
    // Start session at the beginning
    session_start();
    
    if(isset($_POST["submit"])){
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
        
        // Prevent SQL injection by using prepared statements
        $email = mysqli_real_escape_string($connect, $_POST["email"]);

        $sql = "SELECT * FROM users WHERE email=?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $query = mysqli_num_rows($result);
        $fetch = mysqli_fetch_assoc($result);

        if($query <= 0){
            ?>
            <script>
                alert("Sorry, no account exists with this email");
            </script>
            <?php
        }else if(isset($fetch["status"]) && $fetch["status"] == 0){
            ?>
               <script>
                   alert("Sorry, your account must be verified first, before you recover your password!");
                   window.location.replace("index.php");
               </script>
           <?php
        }else{
            // Generate token by binaryhexa 
            $token = bin2hex(random_bytes(50));

            // Store token in session
            $_SESSION['token'] = $token;
            $_SESSION['email'] = $email;

            require "Mail/phpmailer/PHPMailerAutoload.php";
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host='smtp.gmail.com';
            $mail->Port=587;
            $mail->SMTPAuth=true;
            $mail->SMTPSecure='tls';

            // Email account credentials
            $mail->Username='p229341@pwr.nu.edu.pk';
            $mail->Password='sngv zyaa ewul saig';

            // Set sender information
            $mail->setFrom('p229341@pwr.nu.edu.pk', 'Nikkah Junction');
            
            // Get email from input
            $mail->addAddress($email);

            // HTML body
            $mail->isHTML(true);
            $mail->Subject="Reset Your Password - Nikkah Junction";
            $mail->Body="<b>Dear User,</b>
            <h3>We received a request to reset your password.</h3>
            <p>Kindly click the below link to reset your password:</p>
            <a href='http://localhost/login-System/Login-System-main/reset_psw.php?token=$token&email=$email'>
                Reset Password
            </a>
            <br><br>
            <p>If you did not request this password reset, please ignore this email.</p>
            <p>With regards,</p>
            <b>Nikkah Junction Team</b>";

            if(!$mail->send()){
                ?>
                    <script>
                        alert("Email could not be sent. Error: <?php echo $mail->ErrorInfo; ?>");
                    </script>
                <?php
            }else{
                ?>
                    <script>
                        window.location.replace("notification.html");
                    </script>
                <?php
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery | Nikkah Junction</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header animate-fadeIn">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>NIKKAH JUNCTION</h1>
        <p class="tagline">Laakhon Verified Rishtey Ab Aik Click Par!</p>
    </div>

    <div class="container">
        <div class="form-container animate-slideIn">
            <h2 class="text-center">Password Recovery</h2>
            <hr>
            
            <form action="" method="POST">
                <div class="form-group">
                    <label for="email_address">Enter a valid Email Address:</label>
                    <input type="email" name="email" id="email_address" required autofocus>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" name="submit">Recover Password</button>
                </div>
            </form>
            
            <p class="text-center mt-2">
                <a href="login.php">Remember your password? Login here</a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <h2>Laakhon Verified Rishtey <br> Ab Aik Click Par!</h2>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>
    <script type="text/javascript" src="enter_otp-forgot_pass-reset-pass.js" defer></script>
</body>
</html>