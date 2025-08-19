<?php
session_start();
require_once 'db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION['role'] === 'hotel') {
        header("Location: hotel_dashboard.php");
    } elseif ($_SESSION['role'] === 'driver') {
        header("Location: driver_dashboard.php");
    } else {
        header("Location: home.php");
    }
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Special admin login check - moved before database check
        if ($role === 'admin' && $email === 'abhinav@gmail.com' && $password === 'Admin@123') {
            $_SESSION['user_id'] = 1;
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = 'Administrator';
            header("Location: admin/dashboard.php");
            exit();
        }

        // Determine which table to query based on role
        $table = '';
        switch ($role) {
            case 'user':
                $table = 'users';
                break;
            case 'hotel':
                $table = 'hotels';
                break;
            case 'driver':
                $table = 'cab_drivers';
                break;
            case 'admin':
                $table = 'users';
                break;
        }

        // Skip database check for admin login
        if ($role !== 'admin') {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $role;
                    $_SESSION['name'] = $user['name'];

                    // Redirect based on role
                    if ($role === 'admin') {
                        header("Location: admin/dashboard.php");
                    } elseif ($role === 'hotel') {
                        header("Location: hotel_dashboard.php");
                    } elseif ($role === 'driver') {
                        header("Location: driver_dashboard.php");
                    } else {
                        header("Location: home.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that email.";
            }
        } else {
            $error = "Invalid admin credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tour and Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a2a6c;
            --secondary-color: #b21f1f;
            --accent-color: #fdbb2d;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .role-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .role-option {
            flex: 1;
            text-align: center;
            padding: 10px;
            border: 2px solid var(--border-color);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .role-option:hover {
            border-color: var(--primary-color);
        }

        .role-option.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .role-option i {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-to-home a:hover {
            color: var(--secondary-color);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .register-link a:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Login to Your Account</h1>
            <p>Select your account type to continue</p>
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="role-options">
                <label class="role-option" for="role_user">
                    <input type="radio" name="role" id="role_user" value="user" checked>
                    <i class="fas fa-user"></i>
                    <div>User</div>
                </label>
                <label class="role-option" for="role_hotel">
                    <input type="radio" name="role" id="role_hotel" value="hotel">
                    <i class="fas fa-hotel"></i>
                    <div>Hotel</div>
                </label>
                <label class="role-option" for="role_driver">
                    <input type="radio" name="role" id="role_driver" value="driver">
                    <i class="fas fa-car"></i>
                    <div>Driver</div>
                </label>
                <label class="role-option" for="role_admin">
                    <input type="radio" name="role" id="role_admin" value="admin">
                    <i class="fas fa-user-shield"></i>
                    <div>Admin</div>
                </label>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php"><i class="fas fa-user-plus"></i> Register here</a>
        </div>

        <div class="back-to-home">
            <a href="home.php">
                <i class="fas fa-home"></i>
                Back to Home
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleOptions = document.querySelectorAll('.role-option');
            
            roleOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    roleOptions.forEach(opt => opt.classList.remove('active'));
                    // Add active class to clicked option
                    this.classList.add('active');
                    // Check the radio button
                    this.querySelector('input[type="radio"]').checked = true;
                });
            });

            // Set initial active state
            document.querySelector('.role-option input[type="radio"]:checked')
                .closest('.role-option')
                .classList.add('active');
        });
    </script>
</body>
</html>