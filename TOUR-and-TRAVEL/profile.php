<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch user information from database
$user_id = $_SESSION["user_id"];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Store user data in session
$_SESSION["user_name"] = $user_data['name'];
$_SESSION["user_email"] = $user_data['email'];
$_SESSION["user_phone"] = $user_data['phone'];
$_SESSION["user_address"] = $user_data['address'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - SARP Tour and Travels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
        }

        nav {
            background-color: #333;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo span {
            color: #007bff;
        }

        .nav-links {
            list-style: none;
            display: flex;
            margin-left: auto;
        }

        .nav-links li {
            margin-right: 20px;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-size: 18px;
        }

        .nav-links a i {
            margin-right: 5px;
        }

        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .profile-sidebar {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: fit-content;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 60px;
        }

        .profile-name {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-email {
            color: #666;
            margin-bottom: 20px;
        }

        .profile-menu {
            list-style: none;
        }

        .profile-menu li {
            margin-bottom: 10px;
        }

        .profile-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #333;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .profile-menu a:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        .profile-menu a.active {
            background-color: #007bff;
            color: white;
        }

        .profile-menu a i {
            margin-right: 10px;
            width: 20px;
        }

        .profile-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .section-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #007bff;
        }

        .section-header h2 {
            color: #333;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #007bff;
        }

        .submit-btn {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        @media (max-width: 992px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .profile-container {
                margin: 20px auto;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
                font-size: 48px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href="home.php" class="logo">SARP <span>Tour and Travels</span></a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li><a href="home.php#about">About</a></li>
            <li><a href="home.php#contact">Contact</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="profile-container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-grid">
            <div class="profile-sidebar">
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-name"><?php echo isset($_SESSION["user_name"]) ? htmlspecialchars($_SESSION["user_name"]) : 'User'; ?></div>
                    <div class="profile-email"><?php echo isset($_SESSION["user_email"]) ? htmlspecialchars($_SESSION["user_email"]) : 'No email set'; ?></div>
                </div>
                <ul class="profile-menu">
                    <li><a href="#" class="active" data-section="personal-info"><i class="fas fa-user-circle"></i> Personal Information</a></li>
                    <li><a href="#" data-section="settings"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#" data-section="notifications"><i class="fas fa-bell"></i> Notifications</a></li>
                </ul>
            </div>

            <div class="profile-content">
                <div class="content-section active" id="personal-info">
                    <div class="section-header">
                        <h2>Personal Information</h2>
                    </div>
                    <form action="update_profile.php" method="POST">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" value="<?php echo isset($_SESSION["user_name"]) ? htmlspecialchars($_SESSION["user_name"]) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_SESSION["user_email"]) ? htmlspecialchars($_SESSION["user_email"]) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($_SESSION["user_phone"]) ? htmlspecialchars($_SESSION["user_phone"]) : ''; ?>" placeholder="Enter your phone number">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?php echo isset($_SESSION["user_address"]) ? htmlspecialchars($_SESSION["user_address"]) : ''; ?>" placeholder="Enter your address">
                        </div>
                        <button type="submit" name="update_profile" class="submit-btn">Save Changes</button>
                    </form>
                </div>

                <div class="content-section" id="settings">
                    <div class="section-header">
                        <h2>Account Settings</h2>
                    </div>
                    <form>
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" required>
                        </div>
                        <button type="submit" class="submit-btn">Update Password</button>
                    </form>
                </div>

                <div class="content-section" id="notifications">
                    <div class="section-header">
                        <h2>Notifications</h2>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item">
                            <p>Welcome to SARP Tour and Travels! We're here to make your journey memorable.</p>
                            <small>2 hours ago</small>
                        </div>
                        <div class="notification-item">
                            <p>"Life is either a daring adventure or nothing at all." - Helen Keller</p>
                            <small>1 day ago</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Profile menu navigation
        const menuLinks = document.querySelectorAll('.profile-menu a');
        const sections = document.querySelectorAll('.content-section');

        menuLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all links and sections
                menuLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked link and corresponding section
                link.classList.add('active');
                const sectionId = link.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>
</body>
</html> 