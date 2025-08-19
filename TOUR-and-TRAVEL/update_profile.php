<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $user_id = $_SESSION["user_id"];
    $name = trim($_POST["fullName"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    // Check if email already exists for other users
    $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
    $stmt = $conn->prepare($check_email);
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION["error"] = "Email already exists. Please use a different email.";
        header("Location: profile.php");
        exit();
    }

    // Update user information
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        // Update session variables
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
        $_SESSION["user_phone"] = $phone;
        $_SESSION["user_address"] = $address;
        
        $_SESSION["success"] = "Profile updated successfully!";
    } else {
        $_SESSION["error"] = "Error updating profile. Please try again.";
    }

    header("Location: profile.php");
    exit();
} else {
    header("Location: profile.php");
    exit();
}
?> 