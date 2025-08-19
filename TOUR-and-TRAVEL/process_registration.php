<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the role (user, hotel, or driver)
    $role = $_POST['role'];
    
    try {
        switch ($role) {
            case 'user':
                $email = trim($_POST['user_email']);
                $password = trim($_POST['user_password']);
                $phone = trim($_POST['user_phone']);
                $name = trim($_POST['user_name']);
                $address = trim($_POST['user_address']);
                $country = trim($_POST['user_country']);
                $state = trim($_POST['user_state']);
                $city = trim($_POST['user_city']);
                break;
                
            case 'hotel':
                $email = trim($_POST['hotel_email']);
                $password = trim($_POST['hotel_password']);
                $phone = trim($_POST['hotel_phone']);
                $owner_name = trim($_POST['hotel_owner_name']);
                $hotel_name = trim($_POST['hotel_name']);
                $address = trim($_POST['hotel_address']);
                $country = trim($_POST['hotel_country']);
                $state = trim($_POST['hotel_state']);
                $city = trim($_POST['hotel_city']);
                $rating = isset($_POST['hotel_rating']) ? (int)$_POST['hotel_rating'] : 1;
                break;
                
            case 'driver':
                $email = trim($_POST['driver_email']);
                $password = trim($_POST['driver_password']);
                $phone = trim($_POST['driver_phone']);
                $name = trim($_POST['driver_name']);
                $address = trim($_POST['driver_address']);
                $country = trim($_POST['driver_country']);
                $state = trim($_POST['driver_state']);
                $city = trim($_POST['driver_city']);
                $license_number = trim($_POST['driver_license']);
                $vehicle_type = trim($_POST['driver_vehicle_type']);
                $vehicle_number = trim($_POST['driver_vehicle_number']);
                break;
                
            default:
                throw new Exception("Invalid role selected");
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if email already exists
        $check_email = "SELECT email FROM users WHERE email = ? 
                        UNION 
                        SELECT email FROM hotels WHERE email = ? 
                        UNION 
                        SELECT email FROM cab_drivers WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("sss", $email, $email, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Email already exists. Please use a different email.";
            header("Location: register.php");
            exit();
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        switch ($role) {
            case 'user':
                $sql = "INSERT INTO users (name, email, password, phone, address, country, state, city) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }
                $stmt->bind_param("ssssssss", $name, $email, $hashed_password, $phone, $address, $country, $state, $city);
                break;
                
            case 'hotel':
                $sql = "INSERT INTO hotels (owner_name, hotel_name, email, password, phone, address, country, state, city, rating) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }
                $stmt->bind_param("sssssssssi", $owner_name, $hotel_name, $email, $hashed_password, $phone, $address, $country, $state, $city, $rating);
                break;
                
            case 'driver':
                $sql = "INSERT INTO cab_drivers (name, email, password, phone, address, country, state, city, 
                        license_number, vehicle_type, vehicle_number) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }
                $stmt->bind_param("sssssssssss", $name, $email, $hashed_password, $phone, $address, $country, $state, $city, 
                                $license_number, $vehicle_type, $vehicle_number);
                break;
        }
        
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = "Registration successful! Please login.";
            echo "<script>
            alert('Registration successful! Please login.');
            window.location.href = 'login.php';
            </script>";
        exit();
        } else {
            throw new Exception("Error in registration: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        if ($conn->connect_errno) {
            $conn->rollback();
        }
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: register.php");
        exit();
    }
    
    if (isset($stmt)) {
        $stmt->close();
    }
} else {
    // If not a POST request, redirect to registration page
    header("Location: register.php");
    exit();
}
?> 