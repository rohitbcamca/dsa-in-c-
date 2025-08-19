<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<h2>Invalid destination ID.</h2>';
    exit();
}
$destination_id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo '<h2>Destination not found.</h2>';
    exit();
}
$destination = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['name']); ?> - Destination Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; }
        .details-container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); overflow: hidden; }
        .details-image { width: 100%; height: 350px; object-fit: cover; }
        .details-content { padding: 30px; }
        .details-content h1 { color: #1a2a6c; margin-bottom: 10px; }
        .details-content .region { color: #888; font-size: 1rem; margin-bottom: 15px; }
        .details-content .price { color: #b21f1f; font-size: 1.3rem; font-weight: bold; margin-bottom: 10px; }
        .details-content .duration { color: #1a2a6c; font-size: 1.1rem; margin-bottom: 20px; }
        .details-content p { color: #444; font-size: 1.1rem; line-height: 1.7; }
        .back-btn { display: inline-block; margin-top: 25px; padding: 10px 20px; background: #1a2a6c; color: #fff; border-radius: 5px; text-decoration: none; transition: background 0.2s; }
        .back-btn:hover { background: #b21f1f; }
    </style>
</head>
<body>
    <div class="details-container">
          <img src="uploads/<?php echo htmlspecialchars($destination['image_url']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>" class="destination-image">
        <div class="details-content">
            <h1><?php echo htmlspecialchars($destination['name']); ?></h1>
            <div class="region"><i class="fas fa-map-marker-alt"></i> <?php echo ucfirst(htmlspecialchars($destination['region'])); ?></div>
            <div class="price">Price: ₹<?php echo number_format($destination['price']); ?></div>
            <div class="duration">Duration: <?php echo htmlspecialchars($destination['duration']); ?></div>
            <p><?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
            <a href="destinations.php?region=<?php echo urlencode($destination['region']); ?>" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Destinations</a>
        </div>
    </div>
</body>
</html> 