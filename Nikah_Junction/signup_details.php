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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Personal Details
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $religion = $_POST['religion'] ?? '';
    $caste = $_POST['caste'] ?? '';
    $mother_tongue = $_POST['mother_tongue'] ?? '';
    $height = $_POST['height'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $country = $_POST['country'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Education & Employment
    $education = $_POST['education'] ?? '';
    $employment = $_POST['employment'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $diet = $_POST['diet'] ?? '';
    $drinking = $_POST['drinking'] ?? '';
    $smoking = $_POST['smoking'] ?? '';
    
    // Family Details
    $father_occupation = $_POST['father_occupation'] ?? '';
    $mother_occupation = $_POST['mother_occupation'] ?? '';
    $siblings = $_POST['siblings'] ?? '';
    
    // Partner Preferences
    $min_age = $_POST['min_age'] ?? '';
    $max_age = $_POST['max_age'] ?? '';
    $min_height = $_POST['min_height'] ?? '';
    $max_height = $_POST['max_height'] ?? '';
    $additional_preferences = $_POST['additional_preferences'] ?? '';
    
    // Initialize profile picture variable
    $profile_picture = '';
    
    // Handle file upload for profile picture
    if (isset($_FILES['profile-picture']) && $_FILES['profile-picture']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile-picture']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (in_array(strtolower($filetype), $allowed)) {
            // Create unique file name
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $new_filename;
            
            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads/')) {
                mkdir('uploads/', 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile-picture']['tmp_name'], $upload_path)) {
                $profile_picture = $upload_path;
            }
        }
    }
    
    // Prepare SQL statement - using prepared statements to prevent SQL injection
    $sql = "INSERT INTO user_profiles (
                profile_picture, dob, gender, religion, caste, mother_tongue, 
                height, weight, country, address, education, employment, 
                occupation, marital_status, diet, drinking, smoking, 
                father_occupation, mother_occupation, siblings, 
                min_age_pref, max_age_pref, min_height_pref, max_height_pref, additional_preferences
            ) VALUES (
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, 
                ?, ?, ?, ?, ?
            )";
    
    // Create a prepared statement
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param(
            "sssssssssssssssssssssssss",
            $profile_picture, $dob, $gender, $religion, $caste, $mother_tongue,
            $height, $weight, $country, $address, $education, $employment,
            $occupation, $marital_status, $diet, $drinking, $smoking,
            $father_occupation, $mother_occupation, $siblings,
            $min_age, $max_age, $min_height, $max_height, $additional_preferences
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<div class='success-message'>Profile created successfully!</div>";
            // Redirect to a success page or dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<div class='error-message'>Error: " . $stmt->error . "</div>";
        }
        
        // Close statement
        $stmt->close();
    } else {
        echo "<div class='error-message'>Error preparing statement: " . $conn->error . "</div>";
    }
    
    // Handle marital status additional fields if needed
    if ($marital_status === 'Divorced' && isset($_POST['divorce_years'])) {
        $divorce_years = $_POST['divorce_years'];
        $user_id = $conn->insert_id; // Get the ID of the inserted user
        
        $sql = "UPDATE user_profiles SET divorce_years = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $divorce_years, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($marital_status === 'Widowed' && isset($_POST['widowed_years'])) {
        $widowed_years = $_POST['widowed_years'];
        $user_id = $conn->insert_id; // Get the ID of the inserted user
        
        $sql = "UPDATE user_profiles SET widowed_years = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $widowed_years, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nikkah Junction - Profile Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Essential styles that were previously added by JS */
        .invalid {
            border-color: #f44336 !important;
            background-color: rgba(244, 67, 54, 0.05);
        }
        
        .error-message {
            color: #f44336;
            font-size: 0.85em;
            margin-top: 5px;
            display: block;
        }
        
        .profile-preview-container {
            margin: 15px 0; 
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .preview-image-container {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            overflow: hidden;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .section-header {
            padding-bottom: 10px;
            border-bottom: 2px solid #2c3e50;
        }

        .age-container {
            display: inline-block;
            margin-left: 10px;
            color: #3498db;
            font-weight: bold;
        }

        .form-nav {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
        }
        
        .form-nav h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .form-nav-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .form-nav-list a {
            display: block;
            padding: 5px 10px;
            background-color: white;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 300px;
            z-index: 1000;
        }
        
        .notification {
            background-color: white;
            border-left: 4px solid #3498db;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .notification.success {
            border-left-color: #2ecc71;
        }
        
        .notification.error {
            border-left-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="Nikkah Junction Logo" class="logo">
        <h1>Complete Your Profile</h1>
        <p class="tagline">Tell us about yourself to find your perfect match</p>
    </div>

    <div class="form-container animate-fadeIn">
        <!-- Form section navigation -->
        <div class="form-nav">
            <h3>Jump to Section:</h3>
            <ul class="form-nav-list">
                <li><a href="#personal-details">Personal Details</a></li>
                <li><a href="#education-employment">Education & Employment</a></li>
                <li><a href="#family-details">Family Details</a></li>
                <li><a href="#partner-preferences">Partner Preferences</a></li>
            </ul>
        </div>

        <!-- Main form with action pointing to your backend -->
        <form action="" method="POST" class="animate-slideIn" enctype="multipart/form-data">
            <h2 class="section-header" id="profile-picture">Upload or Capture Profile Picture</h2>
            <div class="form-group">
                <label for="profile-picture-input">Profile Picture:</label>
                <input type="file" id="profile-picture-input" name="profile_picture" accept="image/*">
                
                <!-- Profile preview container -->
                <div id="profile-preview-container" class="profile-preview-container">
                    <div class="preview-image-container">
                        <img id="profile-preview" src="" alt="Profile Preview" style="display: none; max-width: 200px; max-height: 200px; border-radius: 50%;">
                    </div>
                    <button type="button" id="remove-photo" class="btn btn-small" style="display: none;">Remove Photo</button>
                </div>
            </div>

            <hr>

            <h2 class="section-header" id="personal-details">Personal Details</h2>
            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required 
                       min="1945-04-28" max="2007-04-28">
                <div class="age-container" id="age-container" style="display: none;">
                    <span id="age-display">0</span>
                    <span class="age-label"> years old</span>
                </div>
            </div>

            <div class="form-group">
                <label>Gender:</label>
                <div class="radio-group">
                    <label for="male"><input type="radio" name="gender" id="male" value="Male" required> Male</label>
                    <label for="female"><input type="radio" name="gender" id="female" value="Female" required> Female</label>
                </div>
            </div>

            <div class="form-group">
                <label for="religion">Religion:</label>
                <select id="religion" name="religion" required>
                    <option value="">Select</option>
                    <option value="Islam">Islam</option>
                    <option value="Christianity">Christianity</option>
                    <option value="Hinduism">Hinduism</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="caste">Caste:</label>
                <input type="text" id="caste" name="caste" placeholder="Enter Caste" required>
            </div>

            <div class="form-group">
                <label for="mother-tongue">Mother Tongue:</label>
                <input type="text" id="mother-tongue" name="mother_tongue" placeholder="Enter Mother Tongue" required>
            </div>

            <div class="form-group">
                <label for="height">Height:</label>
                <select id="height" name="height" required>
                    <option value="">Select</option>
                    <option value="4'6">4'6</option>
                    <option value="4'7">4'7</option>
                    <option value="4'8">4'8</option>
                    <option value="4'9">4'9</option>
                    <option value="4'10">4'10</option>
                    <option value="4'11">4'11</option>
                    <option value="5'0">5'0</option>
                    <option value="5'1">5'1</option>
                    <option value="5'2">5'2</option>
                    <option value="5'3">5'3</option>
                    <option value="5'4">5'4</option>
                    <option value="5'5">5'5</option>
                    <option value="5'6">5'6</option>
                    <option value="5'7">5'7</option>
                    <option value="5'8">5'8</option>
                    <option value="5'9">5'9</option>
                    <option value="5'10">5'10</option>
                    <option value="5'11">5'11</option>
                    <option value="6'0">6'0</option>
                    <option value="6'1">6'1</option>
                    <option value="6'2">6'2</option>
                    <option value="6'3">6'3</option>
                </select>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="number" id="weight" name="weight" placeholder="Enter Weight (kg)" min="30" max="200" required>
            </div>

            <div class="form-group">
                <label for="country">Country:</label>
                <select id="country" name="country" required>
                    <option value="">Select</option>
                    <option value="PAKISTAN">Pakistan</option>
                    <option value="INDIA">India</option>
                    <option value="USA">USA</option>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" placeholder="Enter Address" required></textarea>
            </div>

            <hr>

            <h2 class="section-header" id="education-employment">Education Employment</h2>
            <div class="form-group">
                <label for="education">Education:</label>
                <select id="education" name="education" required>
                    <option value="">Select</option>
                    <option value="Matriculation">Matriculation</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Bachelors">Bachelors</option>
                    <option value="Masters">Masters</option>
                    <option value="Phd">PhD</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="employment">Employment Status:</label>
                <select id="employment" name="employment" required>
                    <option value="">Select</option>
                    <option value="Employed">Employed</option>
                    <option value="Unemployed">Unemployed</option>
                </select>
            </div>

            <div class="form-group">
                <label for="occupation">Occupation:</label>
                <input type="text" id="occupation" name="occupation" placeholder="Enter Occupation" required>
            </div>

            <div class="form-group">
                <label for="marital-status">Marital Status:</label>
                <select id="marital-status" name="marital_status" required>
                    <option value="">Select</option>
                    <option value="Never Married">Never Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Widowed">Widowed</option>
                </select>
            </div>

            <!-- Additional fields for divorced or widowed status -->
            <div id="marital-status-details" class="form-group" style="display: none;">
                <div id="divorced-details" style="display: none;">
                    <label for="divorce-years">Years Since Divorce:</label>
                    <input type="number" id="divorce-years" name="divorce_years" min="0" max="50">
                </div>
                <div id="widowed-details" style="display: none;">
                    <label for="widowed-years">Years Since Spouse Passed Away:</label>
                    <input type="number" id="widowed-years" name="widowed_years" min="0" max="50">
                </div>
            </div>

            <div class="form-group">
                <label for="diet">Dietary Habits:</label>
                <select id="diet" name="diet" required>
                    <option value="">Select</option>
                    <option value="Vegetarian">Vegetarian</option>
                    <option value="Non-Vegetarian">Non-Vegetarian</option>
                    <option value="Eggetarian">Eggetarian</option>
                </select>
            </div>

            <div class="form-group">
                <label for="drinking">Drinking Habit:</label>
                <select id="drinking" name="drinking" required>
                    <option value="">Select</option>
                    <option value="No">No</option>
                    <option value="Occasionally">Occasionally</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <div class="form-group">
                <label for="smoking">Smoking Habit:</label>
                <select id="smoking" name="smoking" required>
                    <option value="">Select</option>
                    <option value="No">No</option>
                    <option value="Occasionally">Occasionally</option>
                    <option value="Yes">Yes</option>
                </select>
            </div>

            <hr>

            <h2 class="section-header" id="family-details">Family Details</h2>
            <div class="form-group">
                <label for="father-occupation">Father's Occupation:</label>
                <input type="text" id="father-occupation" name="father_occupation" placeholder="Enter Father's Occupation">
            </div>

            <div class="form-group">
                <label for="mother-occupation">Mother's Occupation:</label>
                <input type="text" id="mother-occupation" name="mother_occupation" placeholder="Enter Mother's Occupation">
            </div>

            <div class="form-group">
                <label for="siblings">Number of Siblings:</label>
                <input type="number" id="siblings" name="siblings" placeholder="Number of Siblings" min="0" max="20">
            </div>

            <hr>

            <h2 class="section-header" id="partner-preferences">Partner Preferences</h2>
            <div class="form-group">
                <label>Preferred Age Range:</label>
                <div class="flex-container gap-1">
                    <input type="number" id="min-age" name="min_age" placeholder="Min Age" min="18" max="70" value="25" required>
                    <span>to</span>
                    <input type="number" id="max-age" name="max_age" placeholder="Max Age" min="18" max="70" value="35" required>
                </div>
            </div>

            <div class="form-group">
                <label>Preferred Height Range:</label>
                <div class="flex-container gap-1">
                    <select id="min-height" name="min_height" required>
                        <option value="">Select Min Height</option>
                        <option value="4'6">4'6</option>
                        <option value="4'7">4'7</option>
                        <option value="4'8">4'8</option>
                        <option value="4'9">4'9</option>
                        <option value="4'10">4'10</option>
                        <option value="4'11">4'11</option>
                        <option value="5'0">5'0</option>
                        <option value="5'1">5'1</option>
                        <option value="5'2">5'2</option>
                        <option value="5'3">5'3</option>
                        <option value="5'4">5'4</option>
                        <option value="5'5">5'5</option>
                        <option value="5'6">5'6</option>
                        <option value="5'7">5'7</option>
                        <option value="5'8">5'8</option>
                        <option value="5'9">5'9</option>
                        <option value="5'10">5'10</option>
                        <option value="5'11">5'11</option>
                        <option value="6'0">6'0</option>
                        <option value="6'1">6'1</option>
                        <option value="6'2">6'2</option>
                        <option value="6'3">6'3</option>
                    </select>
                    <span>to</span>
                    <select id="max-height" name="max_height" required>
                        <option value="">Select Max Height</option>
                        <option value="4'6">4'6</option>
                        <option value="4'7">4'7</option>
                        <option value="4'8">4'8</option>
                        <option value="4'9">4'9</option>
                        <option value="4'10">4'10</option>
                        <option value="4'11">4'11</option>
                        <option value="5'0">5'0</option>
                        <option value="5'1">5'1</option>
                        <option value="5'2">5'2</option>
                        <option value="5'3">5'3</option>
                        <option value="5'4">5'4</option>
                        <option value="5'5">5'5</option>
                        <option value="5'6">5'6</option>
                        <option value="5'7">5'7</option>
                        <option value="5'8">5'8</option>
                        <option value="5'9">5'9</option>
                        <option value="5'10">5'10</option>
                        <option value="5'11">5'11</option>
                        <option value="6'0">6'0</option>
                        <option value="6'1">6'1</option>
                        <option value="6'2">6'2</option>
                        <option value="6'3">6'3</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="interests">Interests:</label>
                <div class="tag-container">
                    <div class="tag-suggestions">
                        <p>Select interests: </p>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Cooking"> Cooking</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Travel"> Travel</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Reading"> Reading</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Sports"> Sports</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Music"> Music</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Art"> Art</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Movies"> Movies</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Photography"> Photography</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Nature"> Nature</label>
                        <label class="tag-option"><input type="checkbox" name="interests[]" value="Technology"> Technology</label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="additional-prefs">Additional Preferences:</label>
                <textarea id="additional-prefs" name="additional_preferences" placeholder="Enter Additional Preferences"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">Complete Registration</button>
            </div>
        </form>
    </div>

    <div class="footer">
        <p>&copy; 2025 Nikkah Junction. All rights reserved.</p>
    </div>
    
    <!-- Notification container for showing messages -->
    <div id="notification-container" class="notification-container"></div>
    
    <script type="text/javascript" src="signup_details.js" defer></script>
</body>
</html>