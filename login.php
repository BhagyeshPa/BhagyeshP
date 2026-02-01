<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'kopran';
$username = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

$error = '';

// Include helpers for password functions
require_once 'helpers.php';

// Check if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userInput = trim($_POST['user_input']);
    $password = trim($_POST['password']);

    if (empty($userInput) || empty($password)) {
        $error = 'Please enter both User ID/Emp Code and Password.';
    } else {
        // Check against user_id or emp_code
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (user_id = ? OR emp_code = ?) AND is_active = TRUE");
        $stmt->execute([$userInput, $userInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = 'Invalid credentials. Please try again.';
            logAudit($pdo, $userInput, 'Failed Login', 'User not found or inactive', getClientIP());
        } elseif (isAccountLocked($pdo, $user['user_id'])) {
            $error = 'Account is locked due to multiple failed login attempts. Please contact administrator.';
            logAudit($pdo, $user['user_id'], 'Failed Login', 'Account locked', getClientIP());
        } elseif ($password === $user['password']) {
            // Login successful
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['department'] = $user['department'];
            $_SESSION['emp_code'] = $user['emp_code'];
            $_SESSION['role'] = $user['role'] ?? 'user';

            // Reset failed login attempts and update last login
            resetFailedLogins($pdo, $user['user_id']);

            // Log successful login
            logAudit($pdo, $user['user_id'], 'Login', 'User logged in successfully', getClientIP());

            header('Location: dashboard.php');
            exit;
        } else {
            // Invalid password
            recordFailedLogin($pdo, $user['user_id']);
            $error = 'Invalid credentials. Please try again.';
            logAudit($pdo, $userInput, 'Failed Login', 'Invalid password', getClientIP());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kopran - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .login-container {
            height: 100vh;
            display: flex;
        }

        .login-left {
            flex: 1;
            background: url('assets/cover.png') center/cover no-repeat;
            /* Image is now the main background */
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
            /* Semi-transparent gradient overlay */
            z-index: 1;
        }

        .login-left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            padding: 40px;
        }

        .login-left-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .login-left-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 500px;
            margin: 0 auto;
        }

        .login-right {
            flex: 1;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 450px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container img {
            max-width: 180px;
            height: auto;
            margin-bottom: 20px;
        }

        .logo-container h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .logo-container p {
            color: #718096;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .input-group-text {
            background: transparent;
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 10px 0 0 10px;
            color: #718096;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.05rem;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 14px 18px;
        }

        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #718096;
            font-size: 0.9rem;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #718096;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #764ba2;
        }

        @media (max-width: 992px) {
            .login-left {
                display: none;
            }

            .login-right {
                flex: 1;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-form-wrapper {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Section - Cover Image -->
        <div class="login-left">
            <div class="login-left-content">
                <h1>Welcome </h1>
                <p>Access your dashboard and manage your work efficiently with Kopran's integrated platform.</p>
            </div>
        </div>

        <!-- Right Section - Login Form -->
        <div class="login-right">
            <div class="login-form-wrapper">
                <div class="logo-container">
                    <img src="assets/logo.png" alt="Kopran Logo">
                    <h2>Sign In</h2>
                    <p>Enter your credentials to access your account</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="user_input" class="form-label">User ID / Employee Code</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" id="user_input" name="user_input"
                                placeholder="Enter your User ID or Emp Code" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

               

                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>