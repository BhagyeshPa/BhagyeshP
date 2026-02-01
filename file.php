<?php
session_start();

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $userId = trim($_POST['user_id']);
    $password = trim($_POST['password']);
   
    if (empty($userId) || empty($password)) {
        $error = 'User ID and password are required.';
    } else {
        // Check credentials
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE user_id = ? AND password = ?");
        $stmt->execute([$userId, $password]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_id_str'] = $userId;
            header('Location: index2.php');
            exit;
        } else {
            $error = 'Invalid User ID or password.';
        }
    }
}

// For admin sign-up, redirect to usercreation.php (assuming it's for creating users/admins)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Kopran Limited | Savroli</title>
</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="usercreation.php" method="get"> <!-- Redirect to registration page -->
                <h1>Admin Only</h1>
                
                <span></span>
                <input type="email" placeholder="Email" name="email" required>
                <input type="password" placeholder="Password" name="password" required>
                <button type="submit" class="signup">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="post">
                <h1>User Login</h1>
                <?php if (!empty($error)): ?>
                    <span style="color: red;"><?php echo htmlspecialchars($error); ?></span>
                <?php endif; ?>
                <span></span>
                <input type="text" name="user_id" placeholder="User ID" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="login">Log In</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your details to use all of site features</p>
                    <button class="hidden" id="login">Log In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Kopran Limited Savroli</h1>
                    <p>Login with your details to use all of site features</p>
                    <button class="hidden" id="register">New Member</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });
    </script>
</body>

</html>