<?php
// Database configuration
$servername = "localhost";
$username = "root";     // Default XAMPP username
$password = "";         // Default XAMPP password
$dbname = "sarp_db";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4 for proper character encoding
$conn->set_charset("utf8mb4");

// Function to safely close the database connection
function closeConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
    }
}

// Register the closeConnection function to be called when the script ends
register_shutdown_function('closeConnection');

// Create hotels table
$sql = "CREATE TABLE IF NOT EXISTS hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    phone VARCHAR(20),
    price VARCHAR(50),
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    rating DECIMAL(2,1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating hotels table: " . $conn->error);
}

// Create trekking_services table
$sql = "CREATE TABLE IF NOT EXISTS trekking_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    phone VARCHAR(20),
    price VARCHAR(50),
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    service_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating trekking_services table: " . $conn->error);
}
?>