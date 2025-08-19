<?php
session_start();
require_once 'db.php';

// Check if user is logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: login.php");
    exit();
}

// Get driver details
$driver_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM cab_drivers WHERE id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();

// Handle form submission for updating driver details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_details'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $license_number = trim($_POST['license_number']);
        $vehicle_type = trim($_POST['vehicle_type']);
        $vehicle_number = trim($_POST['vehicle_number']);
        
        $update_stmt = $conn->prepare("UPDATE cab_drivers SET name=?, phone=?, address=?, city=?, state=?, license_number=?, vehicle_type=?, vehicle_number=? WHERE id=?");
        $update_stmt->bind_param("ssssssssi", $name, $phone, $address, $city, $state, $license_number, $vehicle_type, $vehicle_number, $driver_id);
        
        if ($update_stmt->execute()) {
            $success = "Driver details updated successfully!";
            // Refresh driver data
            $stmt->execute();
            $result = $stmt->get_result();
            $driver = $result->fetch_assoc();
        } else {
            $error = "Failed to update driver details. Please try again.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - Tour and Travel</title>
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
            <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($driver['name']); ?></h1>
            <div class="dashboard-nav">
                <a href="driver_dashboard.php" class="nav-link">Dashboard</a>
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
            <h2 class="section-title">Driver Details</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($driver['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($driver['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required><?php echo htmlspecialchars($driver['address']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($driver['city']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($driver['state']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="license_number">License Number</label>
                    <input type="text" id="license_number" name="license_number" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <input type="text" id="vehicle_type" name="vehicle_type" value="<?php echo htmlspecialchars($driver['vehicle_type']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="vehicle_number">Vehicle Number</label>
                    <input type="text" id="vehicle_number" name="vehicle_number" value="<?php echo htmlspecialchars($driver['vehicle_number']); ?>" required>
                </div>

                <button type="submit" name="update_details" class="btn">Update Details</button>
            </form>
        </div>
    </div>
</body>
</html> 