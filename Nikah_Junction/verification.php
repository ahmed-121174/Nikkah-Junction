<?php session_start() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification | Nikkah Junction</title>
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
            <h2 class="text-center">Verification Account</h2>
            <hr>
            
            <form action="#" method="POST">
                <div class="form-group">
                    <label for="otp">OTP Code:</label>
                    <input type="text" name="otp_code" id="otp" required autofocus>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="verify" class="btn btn-primary">Verify</button>
                </div>
            </form>
            
            <p class="text-center mt-2">
                <a href="#">Didn't receive OTP? Send again</a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <h2>Laakhon Verified Rishtey <br> Ab Aik Click Par!</h2>
        <img src="logo.png" alt="Nikkah Junction Logo" class="footer-logo">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </footer>
</body>
</html>
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
    if(isset($_POST["verify"])){
        $otp = $_SESSION['otp'];
        $email = $_SESSION['mail'];
        $otp_code = $_POST['otp_code'];

        if($otp != $otp_code){
            ?>
           <script>
               alert("Invalid OTP code");
           </script>
           <?php
        }else{
            mysqli_query($conn, "UPDATE users SET status = 1 WHERE email = '$email'");
            ?>
             <script>
                   window.location.replace("signup_details.php");
             </script>
             <?php
        }

    }

?>