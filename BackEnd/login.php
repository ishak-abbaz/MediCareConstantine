<?php
session_start();

// if already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ../AdminDashboard/dashboard.php');
    exit();
}

require_once 'connection.php';

$error_message = '';
$success_message = '';

// handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // validation
    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $pdo = getDatabaseConnection();

            // fetch user from database
            $stmt = $pdo->prepare("SELECT * FROM authentication WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'] ?? 'admin';

                // redirect to admin dashboard
                header('Location: ../AdminDashboard/dashboard.php');
                exit();
            } else {
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error_message = "An error occurred. Please try again.";
        }
    }
}

// handle Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];

    // validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error_message = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        try {
            $pdo = getDatabaseConnection();

            // check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM authentication WHERE username = :username");
            $stmt->execute(['username' => $username]);

            if ($stmt->fetch()) {
                $error_message = "Username already exists. Please choose another.";
            } else {
                // hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO authentication (username, password) VALUES (:username, :password)");
                $stmt->execute([
                        'username' => $username,
                        'password' => $hashed_password,
                ]);

                $success_message = "Account created successfully! You can now login.";
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error_message = "An error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MediCare Constantine</title>
    <link rel="icon" type="image/png" href="../FrontEnd/HomePage/clinic_icon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 550px;
        }

        .login-side, .register-side {
            flex: 1;
            padding: 50px 40px;
        }

        .login-side {
            background: white;
        }

        .register-side {
            background: linear-gradient(135deg, #4a90e2 0%, #67b3e8 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .brand {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand h1 {
            color: #4a90e2;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .brand p {
            color: #666;
            font-size: 14px;
        }

        .form-title {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #4a90e2, #67b3e8);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 144, 226, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .switch-form {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .switch-form a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 600;
        }

        .switch-form a:hover {
            text-decoration: underline;
        }

        .register-content h2 {
            font-size: 32px;
            margin-bottom: 15px;
        }

        .register-content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features {
            text-align: left;
            margin-top: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .feature-item::before {
            content: "✓";
            display: inline-block;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 12px;
            font-weight: bold;
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .home-link:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .login-container.show-register .login-side {
            display: none;
        }

        .login-container.show-register .register-side {
            display: none;
        }

        .register-form-side {
            display: none;
            flex: 1;
            padding: 50px 40px;
            background: white;
        }

        .login-container.show-register .register-form-side {
            display: block;
        }

        .login-promo-side {
            background: linear-gradient(135deg, #4a90e2 0%, #67b3e8 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            flex: 1;
            padding: 50px 40px;
        }

        .login-container.show-register .login-promo-side {
            display: none;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .register-side, .login-promo-side {
                display: none !important;
            }

            .login-side, .register-form-side {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
<a href="../FrontEnd/HomePage/index.html" class="home-link">← Back to Home</a>

<div class="login-container" id="loginContainer">
    <div class="login-side">
        <div class="brand">
            <h1>MediCare Constantine</h1>
            <p>Admin Portal</p>
        </div>

        <h2 class="form-title">Welcome Back</h2>

        <?php if (!empty($error_message) && !isset($_POST['register'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>

            <button type="submit" name="login" class="submit-btn">Login</button>

            <div class="switch-form">
                Don't have an account? <a href="#" onclick="toggleForm(); return false;">Create Account</a>
            </div>
        </form>
    </div>

    <div class="register-form-side">
        <div class="brand">
            <h1>MediCare Constantine</h1>
            <p>Create Admin Account</p>
        </div>

        <h2 class="form-title">Create Your Account</h2>

        <?php if (!empty($error_message) && isset($_POST['register'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="reg_username" required minlength="3" value="<?php echo isset($_POST['reg_username']) ? htmlspecialchars($_POST['reg_username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="reg_password" required minlength="6">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>

            <button type="submit" name="register" class="submit-btn">Create Account</button>

            <div class="switch-form">
                Already have an account? <a href="#" onclick="toggleForm(); return false;">Login</a>
            </div>
        </form>
    </div>

</div>

<script>
    function toggleForm() {
        const container = document.getElementById('loginContainer');
        container.classList.toggle('show-register');
    }

    // If there was a registration error, show register form
    <?php if (!empty($error_message) && isset($_POST['register'])): ?>
    toggleForm();
    <?php endif; ?>
</script>
</body>
</html>