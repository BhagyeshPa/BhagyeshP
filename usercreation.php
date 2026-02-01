<?php

// Include helpers for password hashing
require_once 'helpers.php';

// Database connection
$host = 'localhost';
$dbname = 'kopran';
$username = 'root';
$password_db = ''; // Replace with your database password

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password_db);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Could not connect to the database: " . $e->getMessage());
}

// Create the users table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) UNIQUE NOT NULL,
    emp_code VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL
)";
try {
  $pdo->exec($createTable);
} catch (PDOException $e) {
  die("Error creating table: " . $e->getMessage());
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullName = trim($_POST['fullName']);
  $userId = trim($_POST['userId']);
  $empCode = trim($_POST['empCode']);
  $department = trim($_POST['department']);
  $password = trim($_POST['password']);
  $confirmPassword = trim($_POST['confirmPassword']);

  // Validate inputs
  if (empty($fullName) || empty($userId) || empty($empCode) || empty($department) || empty($password) || empty($confirmPassword)) {
    $error = 'All fields are required.';
  } elseif ($password !== $confirmPassword) {
    $error = 'Passwords do not match.';
  } elseif (strlen($password) < 5) {
    $error = 'Password must be at least 5 characters long.';
  } else {
    // Check if userId or empCode already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id = ? OR emp_code = ?");
    $stmt->execute([$userId, $empCode]);

    if ($stmt->rowCount() > 0) {
      $error = 'User ID or Employee Code already exists.';
    } else {
      // Insert new user with plain text password
      $stmt = $pdo->prepare("INSERT INTO users (full_name, user_id, emp_code, department, password, password_changed_at) VALUES (?, ?, ?, ?, ?, NOW())");
      if ($stmt->execute([$fullName, $userId, $empCode, $department, $password])) {
        $success = 'Registration successful! You can now <a href="login.php">login here</a>.';
        
        // Log registration to audit trail
        logAudit($pdo, $userId, 'User Registration', "New user registered: $fullName", getClientIP());
        // Optional: Auto-redirect after 3 seconds
        // header('refresh:3;url=login.php');
        // exit;
      } else {
        $error = 'Registration failed. Please try again.';
      }
    }
  }
}

// Display messages (for debugging or if accessed directly)
if (!empty($error)) {
  echo "<p style='color: red;'>$error</p>";
}
if (!empty($success)) {
  echo "<p style='color: green;'>$success</p>";
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Kopran | New Registration </title>
  <link rel="stylesheet" href="usercreation.css">
</head>

<body>
  <div class="container">
    <!-- Title section -->
    <div class="title">Registration</div>
    <div class="content">
      <!-- Display error if any -->
      <?php if (!empty($error)): ?>
        <div style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <!-- Registration form -->
      <form action="" method="post">
        <div class="user-details">
          <!-- Input for Full Name -->
          <div class="input-box">
            <span class="details">Full Name</span>
            <input type="text" name="fullName" placeholder="Enter User name" required>
          </div>
          <!-- Input for User ID -->
          <div class="input-box">
            <span class="details">User id</span>
            <input type="text" name="userId" placeholder="Enter user id" required>
          </div>
          <!-- Input for Emp Code -->
          <div class="input-box">
            <span class="details">Emp Code</span>
            <input type="text" name="empCode" placeholder="Enter Emp Code" required>
          </div>
          <!-- Input for Department -->
          <div class="input-box">
            <span class="details">Department</span>
            <input type="text" name="department" placeholder="Department" required>
          </div>
          <!-- Input for Password -->
          <div class="input-box">
            <span class="details">Password</span>
            <input type="password" name="password" placeholder="Enter user password" required>
          </div>
          <!-- Input for Confirm Password -->
          <div class="input-box">
            <span class="details">Confirm Password</span>
            <input type="password" name="confirmPassword" placeholder="Confirm user password" required>
          </div>
        </div>

        <!-- Submit button -->
        <div class="button">
          <input type="submit" value="Register">
        </div>
      </form>
    </div>
  </div>
</body>

</html>