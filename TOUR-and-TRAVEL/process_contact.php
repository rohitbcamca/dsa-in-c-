<?php
session_start();
include 'db.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo "<script>
            alert('All fields are required!');
            window.location.href = 'home.php#contact';
        </script>";
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
            alert('Invalid email format!');
            window.location.href = 'home.php#contact';
        </script>";
        exit();
    }

    // Check if user exists in the users table
    $sql_user = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql_user);
    
    if (!$stmt) {
        die("Query Error (users lookup): " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();
    } else {
        // Insert new user (if not found)
        $insert_user = "INSERT INTO users (name, email, password) VALUES (?, ?, '')";
        $stmt_user = $conn->prepare($insert_user);
        if (!$stmt_user) {
            die("Query Error (insert user): " . $conn->error);
        }
        $stmt_user->bind_param("ss", $name, $email);
        $stmt_user->execute();
        $user_id = $stmt_user->insert_id;
        $stmt_user->close();
    }
    $stmt->close();

    // Insert message into contact_messages table
    $sql_insert = "INSERT INTO contact_messages (user_id, subject, message) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    
    if (!$stmt_insert) {
        die("Query Error (insert message): " . $conn->error);
    }

    $stmt_insert->bind_param("iss", $user_id, $subject, $message);

    if ($stmt_insert->execute()) {
        echo "<script>
            alert('Thank you for your message. We will get back to you soon!');
            window.location.href = 'home.php#contact';
        </script>";
    } else {
        echo "<script>
            alert('Sorry, there was an error sending your message. Please try again later.');
            window.location.href = 'home.php#contact';
        </script>";
    }

    $stmt_insert->close();
    $conn->close();
} else {
    // If someone tries to access this file directly
    header("Location: home.php#contact");
    exit();
}
?>
