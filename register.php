<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "shubham@50554";
$dbname = "railway_reservation";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_message = "Username or Email already exists!";
        } else {
            // Secure password hashing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $email);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Error in registration. Please try again!";
            }
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Railway Reservation</title>
    <style>
        body {
            background: url('register.jpeg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: white;
        }
        .register-container {
            padding: 40px;
            width: 400px;
            text-align: center;
        }
        h2 {
            margin-bottom: 25px;
            font-size: 26px;
            font-weight: bold;
            color: white;
            background: black;
            padding: 10px;
            border-radius: 5px;
        }
        .error {
            color: red;
            font-size: 16px;
            margin-bottom: 15px;
            background: rgba(255, 0, 0, 0.7);
            padding: 10px;
            border-radius: 5px;
        }
        label {
            font-weight: bold;
            display: block;
            margin: 12px 0 5px;
            font-size: 16px;
            color: white;
            background: black;
            padding: 5px;
            border-radius: 5px;
        }
        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid white;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.9);
            color: black;
            outline: none;
        }
        input[type="text"]::placeholder, input[type="password"]::placeholder, input[type="email"]::placeholder {
            color: black;
        }
        input[type="submit"] {
            width: 100%;
            padding: 14px;
            background-color: white;
            border: none;
            border-radius: 8px;
            color: black;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }
        input[type="submit"]:hover {
            background-color: gray;
        }
    </style>
    <script>
        function validateForm() {
            let password = document.forms["registerForm"]["password"].value;
            let confirmPassword = document.forms["registerForm"]["confirm_password"].value;
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form name="registerForm" method="POST" action="" onsubmit="return validateForm()">
            <label for="username">Username</label>
            <input type="text" name="username" placeholder="Enter your username" required>

            <label for="password">Create Password</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" placeholder="Confirm your password" required>

            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" placeholder="Enter your full name" required>

            <label for="email">Email</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <input type="submit" value="Register">
        </form>
    </div>
</body>
</html>
