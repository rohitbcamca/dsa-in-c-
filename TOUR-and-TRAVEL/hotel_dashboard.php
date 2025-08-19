<?php
session_start();
require_once 'db.php';

// Add columns if not exist
addColumnIfNotExists($conn, 'hotels', 'description', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'amenities', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'room_types', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'price_range', 'VARCHAR(100)');

// Check if user is logged in and is a hotel
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hotel') {
    header("Location: login.php");
    exit();
}

// Get hotel details
$hotel_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM hotels WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

// Handle form submission for updating hotel details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_details'])) {
        $owner_name = trim($_POST['owner_name']);
        $hotel_name = trim($_POST['hotel_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $description = trim($_POST['description']);
        $amenities = trim($_POST['amenities']);
        $room_types = trim($_POST['room_types']);
        $price_range = trim($_POST['price_range']);
        
        $update_stmt = $conn->prepare("UPDATE hotels SET owner_name=?, hotel_name=?, phone=?, address=?, city=?, state=?, description=?, amenities=?, room_types=?, price_range=? WHERE id=?");
        $update_stmt->bind_param("ssssssssssi", $owner_name, $hotel_name, $phone, $address, $city, $state, $description, $amenities, $room_types, $price_range, $hotel_id);
        
        if ($update_stmt->execute()) {
            $success = "Hotel details updated successfully!";
            // Refresh hotel data
            $stmt->execute();
            $result = $stmt->get_result();
            $hotel = $result->fetch_assoc();
        } else {
            $error = "Failed to update hotel details. Please try again.";
        }
    }
}

// Get hotel bookings
$bookings_query = "
    SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    WHERE b.hotel_id = ? 
    ORDER BY b.check_in_date DESC
";

$bookings_stmt = $conn->prepare($bookings_query);
if ($bookings_stmt === false) {
    $booking_error = "Error preparing bookings query: " . $conn->error;
} else {
    $bookings_stmt->bind_param("i", $hotel_id);
    if ($bookings_stmt->execute()) {
        $bookings = $bookings_stmt->get_result();
    } else {
        $booking_error = "Error executing bookings query: " . $bookings_stmt->error;
    }
}

function addColumnIfNotExists($conn, $table, $column, $definition) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($result && $result->num_rows == 0) {
        // Column does not exist, add it
        $alter = "ALTER TABLE `$table` ADD `$column` $definition";
        if ($conn->query($alter)) {
            // Success
            // echo "Column $column added to $table.<br>";
        } else {
            // echo "Error adding column $column: " . $conn->error . "<br>";
        }
    }
}

// Usage example:
addColumnIfNotExists($conn, 'hotels', 'description', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'amenities', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'room_types', 'TEXT');
addColumnIfNotExists($conn, 'hotels', 'price_range', 'VARCHAR(100)');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Dashboard - Tour and Travel</title>
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
            background: var(--light-gray);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .welcome-message {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .dashboard-nav {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .nav-link {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: var(--secondary-color);
        }

        .dashboard-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-color);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 10px 20px;
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

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .bookings-table th {
            background: var(--light-gray);
            color: var(--primary-color);
        }

        .status-pending {
            color: #ffc107;
        }

        .status-confirmed {
            color: #28a745;
        }

        .status-cancelled {
            color: #dc3545;
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            color: #28a745;
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($hotel['owner_name']); ?></h1>
            <div class="dashboard-nav">
                <a href="hotel_dashboard.php" class="nav-link">Dashboard</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="dashboard-section">
            <h2 class="section-title">Hotel Details</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="owner_name">Owner Name</label>
                    <input type="text" id="owner_name" name="owner_name" value="<?php echo htmlspecialchars($hotel['owner_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="hotel_name">Hotel Name</label>
                    <input type="text" id="hotel_name" name="hotel_name" value="<?php echo htmlspecialchars($hotel['hotel_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($hotel['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($hotel['address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($hotel['city']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($hotel['state']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Hotel Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo $hotel['description'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="amenities">Amenities (comma separated)</label>
                    <textarea id="amenities" name="amenities" rows="3"><?php echo $hotel['amenities'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="room_types">Room Types (comma separated)</label>
                    <textarea id="room_types" name="room_types" rows="3"><?php echo $hotel['room_types'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price_range">Price Range</label>
                    <input type="text" id="price_range" name="price_range" value="<?php echo $hotel['price_range'] ?? ''; ?>">
                </div>

                <button type="submit" name="update_details" class="btn">Update Details</button>
            </form>
        </div>

        <div class="dashboard-section">
            <h2 class="section-title">Recent Bookings</h2>
            <?php if (isset($booking_error)): ?>
                <div class="alert alert-warning">
                    <?php echo htmlspecialchars($booking_error); ?>
                </div>
            <?php else: ?>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['user_name']); ?><br>
                                        <small><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('d M Y', strtotime($booking['check_out_date'])); ?></td>
                                    <td class="status-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo $booking['status']; ?>
                                    </td>
                                    <td>
                                        <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No bookings found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 