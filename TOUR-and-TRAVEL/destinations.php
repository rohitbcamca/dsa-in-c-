<?php
session_start();
require_once 'db.php';

// Fetch all unique regions from the destinations table
$regions_result = $conn->query("SELECT DISTINCT region FROM destinations ORDER BY region ASC");
$regions = [];
while ($row = $regions_result->fetch_assoc()) {
    $regions[] = $row['region'];
}

// Get the region from the URL parameter
$region = isset($_GET['region']) ? $_GET['region'] : 'all';

// Map region names to display titles
$region_titles = [
    'himalayan' => 'Himalayan Adventure',
    'beach' => 'Beach Paradise',
    'heritage' => 'Heritage Trails',
    'wildlife' => 'Wildlife Safari',
    'spiritual' => 'Spiritual Journey',
    'culinary' => 'Culinary Tours',
    'all' => 'Top Recommendations'
];

$page_title = isset($region_titles[$region]) ? $region_titles[$region] : 'Top Recommendations';

// Get destinations based on region
$query = "SELECT * FROM destinations";
if ($region != 'all') {
    $query .= " WHERE region = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $region);
} else {
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SARP Tour and Travels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .header {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .destinations-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .region-filter {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            background: white;
            border: 2px solid #1a2a6c;
            border-radius: 5px;
            color: #1a2a6c;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #1a2a6c;
            color: white;
        }

        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .destination-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .destination-card:hover {
            transform: translateY(-5px);
        }

        .destination-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .destination-content {
            padding: 20px;
        }

        .destination-content h3 {
            color: #1a2a6c;
            margin-bottom: 10px;
        }

        .destination-content p {
            color: #666;
            margin-bottom: 15px;
        }

        .destination-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        .price {
            color: #b21f1f;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .view-btn {
            padding: 8px 15px;
            background: #1a2a6c;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .view-btn:hover {
            background: #b21f1f;
        }

        .no-destinations {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        @media (max-width: 768px) {
            .destinations-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $page_title; ?></h1>
        <p>Discover amazing destinations and experiences</p>
    </div>

    <div class="destinations-container">
        <div style="margin-bottom: 20px;">
            <a href="home.php" style="display:inline-block; padding:10px 20px; background:#1a2a6c; color:#fff; border-radius:5px; text-decoration:none; font-weight:500;">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <div class="region-filter">
            <a href="destinations.php?region=all" class="filter-btn <?php echo $region == 'all' ? 'active' : ''; ?>">All</a>
            <?php foreach ($regions as $reg): ?>
                <a href="destinations.php?region=<?php echo urlencode($reg); ?>" class="filter-btn <?php echo $region == $reg ? 'active' : ''; ?>">
                    <?php echo ucfirst($reg); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="destinations-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($destination = $result->fetch_assoc()): ?>
                    <div class="destination-card">
                        <img src="uploads/<?php echo htmlspecialchars($destination['image_url']); ?>" alt="<?php echo htmlspecialchars($destination['name']); ?>" class="destination-image">
                        <div class="destination-content">
                            <h3><?php echo htmlspecialchars($destination['name']); ?></h3>
                            <p><?php echo htmlspecialchars($destination['description']); ?></p>
                            <div class="destination-meta">
                                <span class="price">₹<?php echo number_format($destination['price']); ?></span>
                                <a href="destination_details.php?id=<?php echo $destination['id']; ?>" class="view-btn">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-destinations">
                    <h3>No destinations found for this category</h3>
                    <p>Please check back later or explore other categories</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentRegion = '<?php echo $region; ?>';
            const filterButtons = document.querySelectorAll('.filter-btn');

            filterButtons.forEach(button => {
                if (button.getAttribute('href').includes(currentRegion)) {
                    button.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
