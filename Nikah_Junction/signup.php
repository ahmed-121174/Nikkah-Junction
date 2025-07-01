<?php
    
session_start();
$error = ""; // Initialize error variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    // Sanitize inputs to prevent SQL injection
    $email = mysqli_real_escape_string($conn, $email);
    $firstname = mysqli_real_escape_string($conn, $firstname);
    $lastname = mysqli_real_escape_string($conn, $lastname);
    $phone = mysqli_real_escape_string($conn, $phone);

    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($check_query);
    $rowCount = $result->num_rows;

    if(!empty($email) && !empty($password)){
        if($rowCount > 0){
            $error = "User with email already exists!";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $insert_query = "INSERT INTO users (firstname, lastname, email, phone, password_hash, status) 
                           VALUES ('$firstname', '$lastname', '$email', '$phone', '$password_hash', 0)";
            
            if($conn->query($insert_query) === TRUE){
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['mail'] = $email;
                
                require "Mail/phpmailer/PHPMailerAutoload.php";
                $mail = new PHPMailer;

                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 587;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = 'tls';

                $mail->Username = 'p229341@pwr.nu.edu.pk';
                $mail->Password = 'sngv zyaa ewul saig';

                $mail->setFrom('p229341@pwr.nu.edu.pk', 'OTP Verification');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your verify code";
                $mail->Body = "<p>Dear $firstname, </p> <h3>Your verify OTP code is $otp <br></h3>
                <br><br>
                <p>With regards,</p>
                <b>Nikkah Junction</b>";

                if(!$mail->send()){
                    $error = "Registration Failed, Invalid Email";
                } else {
                    // Redirect using PHP header instead of JavaScript
                    $_SESSION['registration_success'] = "Registration Successful, OTP sent to " . $email;
                    $insert_query = "INSERT INTO users (firstname, lastname, email, phone, password_hash, status) 
                           VALUES ('$firstname', '$lastname', '$email', '$phone', '$password_hash', 0)";

                    header("Location: verification.php");
                    exit();
                }
            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    } else {
        $error = "Email and password are required!";
    }
    
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nikkah Junction - Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
            color: #d9534f;
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            color: #3c763d;
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>Sign Up Portal</h1>
        <p class="tagline">Begin your journey to a blessed union</p>
    </div>

    <div class="form-container animate-fadeIn">
        <?php if (!empty($error)): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['registration_success'])): ?>
            <div class="success-message">
                <?php 
                    echo $_SESSION['registration_success']; 
                    unset($_SESSION['registration_success']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="animate-slideIn">
            <div class="form-group">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" placeholder="Enter First Name" required value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" placeholder="Enter Last Name" required value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" placeholder="Enter Email Address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter Phone Number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">Next</button>
            </div>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </div>
    <script type="text/javascript" src="signup.js" defer></script>
</body>
</html>