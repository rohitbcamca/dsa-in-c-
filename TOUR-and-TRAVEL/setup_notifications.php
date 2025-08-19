<?php
require_once 'db.php';

// Read the SQL file
$sql = file_get_contents('add_notifications_table.sql');

// Execute the SQL commands
if ($conn->multi_query($sql)) {
    echo "Notifications table created successfully!";
    
    // Clear out any remaining results
    while ($conn->more_results() && $conn->next_result()) {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    }
} else {
    echo "Error creating notifications table: " . $conn->error;
}

// Close the connection
$conn->close();
?> 