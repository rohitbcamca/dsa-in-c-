<?php
session_start();

// Include database connection
include 'db.php';

// Store username for the alert message
$username = isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : "User";

// Close database connection
if(isset($conn)) {
    mysqli_close($conn);
    echo "<script>
        alert('$username logged out successfully');
    </script>";
    
}

// Destroy the session
session_destroy();

// Redirect to login page with JavaScript alert
echo "<script>
    
    window.location.href = 'login.php';
</script>";
?>
