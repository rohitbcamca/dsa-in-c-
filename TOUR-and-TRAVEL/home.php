<?php
session_start();
require_once 'db.php'; // Include database connection file

// Get user information from session
$user_name = isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"]) : "";
$user_email = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";

// Initialize variables for notifications
$unread_count = 0;
$messages = [];

// Get unread messages count and messages if user is logged in
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    
    // Get unread count from both contact_messages and notifications
    $stmt = $conn->prepare("
        SELECT (
            (SELECT COUNT(*) FROM contact_messages WHERE user_id = ? AND status = 0) +
            (SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0)
        ) as total_count
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $unread_count = $row['total_count'];
        $stmt->close();
    }
    
    // Get recent messages and notifications
    $stmt = $conn->prepare("
        (SELECT 
            'message' as type,
            m.message_id as id,
            m.subject,
            m.message,
            m.reply,
            m.created_at,
            m.status,
            a.name as admin_name
        FROM contact_messages m 
        LEFT JOIN users a ON m.admin_id = a.id 
        WHERE m.user_id = ? 
        ORDER BY m.created_at DESC 
        LIMIT 5)
        UNION ALL
        (SELECT 
            'notification' as type,
            n.id,
            'Notification' as subject,
            n.message,
            NULL as reply,
            n.created_at,
            n.is_read as status,
            NULL as admin_name
        FROM notifications n
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC 
        LIMIT 5)
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($message = $result->fetch_assoc()) {
            $messages[] = $message;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SARP Tour and Travels<?php echo $user_name ? " - Welcome " . $user_name : ""; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f);
            color: white;
            text-align: center;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .welcome-banner p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .welcome-banner .user-info {
            margin-top: 15px;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .slider-container {
            position: relative;
            width: 100%;
            height: 70vh;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slider-buttons {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .slider-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .slider-btn.active {
            background: white;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 15px;
            cursor: pointer;
            border: none;
            font-size: 20px;
            transition: background 0.3s ease;
        }

        .slider-arrow:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
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

        .user-dropdown {
            position: relative;
            display: inline-block;
        }

        .user-dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 200px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1;
            right: 0;
            border-radius: 5px;
        }

        .user-dropdown-content a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }

        .user-dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .user-dropdown:hover .user-dropdown-content {
            display: block;
        }

        .user-dropdown-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
        }

        .user-dropdown-btn i {
            font-size: 20px;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: fixed;
            background-color: #f9f9f9;
            width: 100%;
            height: fit-content;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 0;
            overflow-x: auto;
            padding-top: 80px;
        }

        .dropdown-content.show {
            display: block;
        }

        .dropdown-content-wrapper {
            display: flex;
            flex-direction: row;
            min-width: max-content;
            padding: 0 20px;
        }

        .direction-section {
            padding: 15px;
            border-right: 1px solid #ddd;
            min-width: 250px;
            flex: 0 0 auto;
        }

        .direction-section:last-child {
            border-right: none;
        }

        .direction-section h3 {
            color: #333;
            margin-bottom: 10px;
            text-align: left;
            padding-left: 15px;
            position: sticky;
            top: 0;
            background: #f9f9f9;
            z-index: 1;
        }

        .state-list {
            list-style: none;
        }

        .state-list li a {
            color: #333;
            padding: 10px 20px;
            display: block;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .state-list li a:hover {
            background-color: #ddd;
        }

        .close-menu {
            position: fixed;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #333;
            z-index: 2;
        }

        .about-parent {
            padding: 80px 40px;
            background-color: #f8f9fa;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-content {
            text-align: left;
        }

        .about-content h2 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 30px;
            position: relative;
        }

        .about-content h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 60px;
            height: 3px;
            background-color: #007bff;
        }

        .about-content p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .about-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 40px 0;
            text-align: center;
        }

        .stat-item {
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .stat-item:hover {
            transform: translateY(-5px);
        }

        .quote-text {
            color: #333;
            font-size: 1.1rem;
            line-height: 1.6;
            position: relative;
        }

        .quote-text i {
            color: #007bff;
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .quote-text p {
            margin-bottom: 10px;
            font-style: italic;
        }

        .quote-author {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 40px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background-color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
        }

        .feature-text h4 {
            color: #333;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .feature-text p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .about-image {
            position: relative;
            height: 600px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .about-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @media (max-width: 992px) {
            .about-container {
                grid-template-columns: 1fr;
            }
            
            .about-image {
                height: 400px;
            }

            .about-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .about-features {
                grid-template-columns: 1fr;
            }
            
            .about-parent {
                padding: 60px 20px;
            }

            .about-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Contact Section Styles */
        .contact-parent {
            padding: 80px 40px;
            background-color: #fff;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .contact-info {
            padding: 30px;
        }

        .contact-info h2 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 30px;
            position: relative;
        }

        .contact-info h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 60px;
            height: 3px;
            background-color: #007bff;
        }

        .contact-details {
            margin-top: 30px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateX(10px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background-color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-right: 20px;
        }

        .contact-text h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .contact-text p {
            color: #666;
            font-size: 1rem;
        }

        .contact-form {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }

        .form-group textarea {
            height: 150px;
            resize: vertical;
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

        .map-container {
            grid-column: 1 / -1;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 50px;
        }

        .map-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        @media (max-width: 992px) {
            .contact-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .contact-parent {
                padding: 60px 20px;
            }
            
            .contact-form {
                padding: 20px;
            }
        }

        /* Footer Styles */
        .footer {
            background-color: #333;
            color: #fff;
            padding: 60px 40px 20px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: #fff;
            font-size: 1.5rem;
            margin-bottom: 20px;
            position: relative;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
                left: 0;
            bottom: -8px;
            width: 40px;
            height: 2px;
            background-color: #007bff;
        }

        .footer-section p {
            color: #ccc;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #007bff;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background-color: #444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: #007bff;
            transform: translateY(-3px);
        }

        .social-links a i {
            font-size: 18px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
        }

        .footer-bottom p {
            color: #ccc;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .footer-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .footer {
                padding: 40px 20px 20px;
            }
            
            .footer-container {
                grid-template-columns: 1fr;
            }
        }

        /* Services Section Styles */
        .services-parent {
            padding: 80px 40px;
            background-color: #fff;
        }

        .services-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .services-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .services-header h2 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .services-header h2::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background-color: #007bff;
        }

        .services-header p {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }

        .service-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background-color: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 35px;
            color: #fff;
        }

        .service-card h3 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .service-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .service-card a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .service-card a:hover {
            color: #0056b3;
        }

        @media (max-width: 992px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .services-parent {
                padding: 60px 20px;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
        }

        .notification-icon {
            position: relative;
            margin-right: 10px;
        }

        .notification-link {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .notification-link:hover {
            background-color: #f0f0f0;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 300px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: none;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-icon:hover .notification-dropdown {
            display: block;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.completed {
            background-color: #f8f9fa;
        }

        .notification-item h4 {
            margin: 0 0 5px 0;
            font-size: 1rem;
            color: #333;
        }

        .notification-item p {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
        }

        .notification-item .reply {
            margin-top: 5px;
            padding-left: 10px;
            border-left: 3px solid #4CAF50;
            font-style: italic;
        }

        .notification-time {
            font-size: 0.8rem;
            color: #999;
            margin-top: 5px;
        }

        .notification-type {
            border-left: 3px solid #007bff;
        }

        .notification-type h4 {
            color: #007bff;
        }
    </style>
</head>
<body>
    
    <nav>
        <a href="#" class="logo">SARP <span>Tour and Travels</span></a>
        <ul class="nav-links">
            <li><a href="home.php">Home</a></li>
            <li class="dropdown">
                <a href="#" id="destinations-link">Destinations</a>
                <div class="dropdown-content">
                    <button class="close-menu">×</button>
                    <div class="dropdown-content-wrapper">
                    <div class="direction-section">
                        <h3>North India</h3>
                        <ul class="state-list">
                            <li><a href="Jammu & Kashmir.php">Jammu & Kashmir</a></li>
                            <li><a href="States/Himachal.php">Himachal Pradesh</a></li>
                            <li><a href="States/Punjab.php">Punjab</a></li>
                            <li><a href="States/Uttarakhand.php">Uttarakhand</a></li>
                            <li><a href="States/Haryana.php">Haryana</a></li>
                            <li><a href="States/Delhi.php">Delhi</a></li>
                        </ul>
                    </div>

                    <div class="direction-section">
                        <h3>South India</h3>
                        <ul class="state-list">
                            <li><a href="States/Kerla.php">Kerala</a></li>
                            <li><a href="States/TamilNadu.php">Tamil Nadu</a></li>
                            <li><a href="States/karnatka.php">Karnataka</a></li>
                            <li><a href="States/Andhra.php">Andhra Pradesh</a></li>
                            <li><a href="States/Telangana.php">Telangana</a></li>
                        </ul>
                    </div>

                    <div class="direction-section">
                        <h3>East India</h3>
                        <ul class="state-list">
                            <li><a href="States/WestBangal.php">West Bengal</a></li>
                            <li><a href="States/Odisha.php">Odisha</a></li>
                            <li><a href="States/Bihar.php">Bihar</a></li>
                            <li><a href="States/Jharkhand.php">Jharkhand</a></li>
                            <li><a href="States/Sikkim.php">Sikkim</a></li>
                        </ul>
                    </div>

                    <div class="direction-section">
                        <h3>West India</h3>
                        <ul class="state-list">
                            <li><a href="States/Gujrat.php">Gujarat</a></li>
                            <li><a href="States/Maharastra.php">Maharashtra</a></li>
                            <li><a href="States/Goa.php">Goa</a></li>
                            <li><a href="States/Rajasthan.php">Rajasthan</a></li>
                        </ul>
                    </div>

                    <div class="direction-section">
                        <h3>Northeast India</h3>
                        <ul class="state-list">
                            <li><a href="States/Asam.php">Assam</a></li>
                            <li><a href="States/Arunachal.php">Arunachal Pradesh</a></li>
                            <li><a href="States/Manipur.php">Manipur</a></li>
                            <li><a href="States/Meghalaya.php">Meghalaya</a></li>
                            <li><a href="States/Mizoram.php">Mizoram</a></li>
                            <li><a href="States/Nagaland.php">Nagaland</a></li>
                            <li><a href="States/Tripura.php">Tripura</a></li>
                        </ul>
                    </div>

                    <div class="direction-section">
                        <h3>Central India</h3>
                        <ul class="state-list">
                            <li><a href="States/Madhya.php">Madhya Pradesh</a></li>
                            <li><a href="States/Chhattisgarh.php">Chhattisgarh</a></li>
                        </ul>
                        </div>
                    </div>
                </div>
            </li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
            <?php if(isset($_SESSION["user_id"])): ?>
                <li><a href="profile.php" class="profile-link"><i class="fas fa-user-circle"></i> <?php echo $user_name; ?></a></li>
            <li><a href="Logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <?php if(isset($_SESSION["user_id"])): ?>
    <div class="welcome-banner">
        <h1>Welcome, <?php echo $user_name; ?>!</h1>
        <p>Your trusted partner in creating unforgettable travel experiences</p>
        <div class="user-info">
            <?php echo $user_email; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="slider-container">
        <div class="slide active">
            <img src="images/full-shot-travel-concept-with-landmarks.jpg" alt="Travel Concept">
        </div>
        <div class="slide">
            <img src="images/pietro-de-grandi-T7K4aEPoGGk-unsplash.jpg" alt="Travel Image 1">
        </div>
        <div class="slide">
            <img src="images/pexels-michelle-chadwick-2149425616-31332788.jpg" alt="Travel Image 2">
        </div>
        <div class="slide">
            <img src="images/pexels-tomfisk-31326224.jpg" alt="Travel Image 3">
        </div>
        <div class="slide">
            <img src="images/pexels-andreimike-1271619.jpg" alt="Travel Image 4">
        </div>
        
        <button class="slider-arrow prev-btn">❮</button>
        <button class="slider-arrow next-btn">❯</button>
        
        <div class="slider-buttons">
            <button class="slider-btn active"></button>
            <button class="slider-btn"></button>
            <button class="slider-btn"></button>
            <button class="slider-btn"></button>
            <button class="slider-btn"></button>
        </div>
     </div>
    
    <section class="about-parent" id="about">
        <div class="about-container">
            <div class="about-content">
        <h2>About Us</h2>
                <p>Welcome to <strong>SARP Tour and Travels</strong>, your trusted partner in creating unforgettable travel experiences since 2010. We are more than just a travel agency; we are your gateway to exploring the world's most beautiful destinations with comfort and confidence.</p>
                
                <p>Our journey began with a simple mission: to make travel accessible, enjoyable, and hassle-free for everyone. Over the years, we've grown into a full-service travel company that handles everything from flight bookings to luxury accommodations, ensuring your travel dreams become reality.</p>

                <div class="about-stats">
                    <div class="stat-item">
                        <div class="quote-text">
                            <i class="fas fa-quote-left"></i>
                            <p>"Travel is the only thing you buy that makes you richer"</p>
                            <span class="quote-author">- Anonymous</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="quote-text">
                            <i class="fas fa-quote-left"></i>
                            <p>"The world is a book, and those who do not travel read only one page"</p>
                            <span class="quote-author">- Saint Augustine</span>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="quote-text">
                            <i class="fas fa-quote-left"></i>
                            <p>"Life is either a daring adventure or nothing at all"</p>
                            <span class="quote-author">- Helen Keller</span>
                        </div>
                    </div>
                </div>
                
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">✈️</div>
                        <div class="feature-text">
                            <h4>Expert Travel Planning</h4>
                            <p>Our experienced travel consultants provide personalized guidance to create your perfect itinerary</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🏨</div>
                        <div class="feature-text">
                            <h4>Premium Accommodations</h4>
                            <p>Access to exclusive hotel deals and luxury accommodations worldwide</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🚗</div>
                        <div class="feature-text">
                            <h4>Reliable Transportation</h4>
                            <p>Safe and comfortable travel options with professional drivers and modern vehicles</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🎯</div>
                        <div class="feature-text">
                            <h4>Customized Experiences</h4>
                            <p>Tailored travel plans designed to match your preferences and budget</p>
                        </div>
                    </div>
                </div>

                <p style="margin-top: 30px;">At SARP Tour and Travels, we believe that every journey should be extraordinary. Our commitment to excellence, attention to detail, and passion for travel ensures that you receive the highest quality service at every step of your journey. Whether you're planning a romantic getaway, family vacation, or business trip, we're here to make your travel dreams come true.</p>
            </div>
            <div class="about-image">
                <img src="images/full-shot-travel-concept-with-landmarks.jpg" alt="Travel Experience">
            </div>
        </div>
    </section>

    <section class="services-parent" id="services">
        <div class="services-container">
            <div class="services-header">
                <h2>Our Top Recommendations</h2>
                <p>Discover our handpicked selection of must-visit destinations and experiences</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🏔️</div>
                    <h3>Himalayan Adventure</h3>
                    <p>Experience the majestic beauty of the Himalayas with our curated trekking and cultural tours.</p>
                    <a href="destinations.php?region=himalayan">Explore More →</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏖️</div>
                    <h3>Beach Paradise</h3>
                    <p>Unwind at India's most beautiful beaches with luxury resorts and water sports activities.</p>
                    <a href="destinations.php?region=beach">Explore More →</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏰</div>
                    <h3>Heritage Trails</h3>
                    <p>Journey through India's rich history with visits to ancient monuments and cultural sites.</p>
                    <a href="destinations.php?region=heritage">Explore More →</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌿</div>
                    <h3>Wildlife Safari</h3>
                    <p>Get up close with India's diverse wildlife in our carefully planned jungle safaris.</p>
                    <a href="destinations.php?region=wildlife">Explore More →</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">🛕</div>
                    <h3>Spiritual Journey</h3>
                    <p>Explore India's spiritual heritage with visits to sacred temples and meditation centers.</p>
                    <a href="destinations.php?region=spiritual">Explore More →</a>
                </div>
                <div class="service-card">
                    <div class="service-icon">🍛</div>
                    <h3>Culinary Tours</h3>
                    <p>Discover India's diverse cuisine with food tours and cooking experiences across regions.</p>
                    <a href="destinations.php?region=culinary">Explore More →</a>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-parent" id="contact">
        <div class="contact-container">
            <div class="contact-info">
                <h2>Contact Us</h2>
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-text">
                            <h4>Our Location</h4>
                            <p>123 Travel Street, Tourism City, TC 12345</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-text">
                            <h4>Phone Number</h4>
                            <p>+91 9876543210</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">✉️</div>
                        <div class="contact-text">
                            <h4>Email Address</h4>
                            <p>info@sarptourandtravels.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon">⏰</div>
                        <div class="contact-text">
                            <h4>Working Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <h2>Send us a Message</h2>
                <form action="process_contact.php" method="POST" id="contactForm">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" placeholder="Subject" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Your Message" required></textarea>
                    </div>
                    <button type="submit" class="submit-btn">Send Message</button>
                </form>
            </div>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.5960965552337!2d77.59129931500001!3d12.939176789999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae15986765d7d9%3A0x3565475c3365c3b1!2sBangalore%20Palace!5e0!3m2!1sen!2sin!4v1647681234567!5m2!1sen!2sin" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>About SARP Tour and Travels</h3>
                <p>Your trusted partner in creating unforgettable travel experiences since 2010. We specialize in providing exceptional travel services and making your journey memorable.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Destinations</a></li>
                    <li><a href="#">Packages</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Our Recommendations</h3>
                <ul class="footer-links">
                    <li><a href="#">Himalayan Adventure</a></li>
                    <li><a href="#">Beach Paradise</a></li>
                    <li><a href="#">Heritage Trails</a></li>
                    <li><a href="#">Wildlife Safari</a></li>
                    <li><a href="#">Spiritual Journey</a></li>
                    <li><a href="#">Culinary Tours</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Newsletter</h3>
                <p>Subscribe to our newsletter for travel updates and exclusive offers.</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit" class="submit-btn">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 SARP Tour and Travels. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-btn');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');
        let currentSlide = 0;

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            currentSlide = (n + slides.length) % slides.length;
            
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function prevSlide() {
            showSlide(currentSlide - 1);
        }

        // Event listeners
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // New dropdown menu code
        const destinationsLink = document.getElementById('destinations-link');
        const dropdownContent = document.querySelector('.dropdown-content');
        const closeMenu = document.querySelector('.close-menu');

        destinationsLink.addEventListener('click', (e) => {
            e.preventDefault();
            dropdownContent.classList.add('show');
        });

        closeMenu.addEventListener('click', () => {
            dropdownContent.classList.remove('show');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownContent.contains(e.target) && !destinationsLink.contains(e.target)) {
                dropdownContent.classList.remove('show');
            }
        });

        // Notification dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const notificationIcon = document.querySelector('.notification-icon');
            const dropdown = document.querySelector('.notification-dropdown');
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!notificationIcon.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });
            
            // Prevent dropdown from closing when clicking inside
            dropdown.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    </script>
</body>
</html>
