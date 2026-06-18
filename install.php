<?php
require_once __DIR__ . '/includes/db_config.php';

try {
    // 1. Create Vendors Table
    $conn->exec("CREATE TABLE IF NOT EXISTS `vendors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `shop_name` VARCHAR(100) NOT NULL,
        `owner_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `shop_description` TEXT,
        `address` TEXT NOT NULL,
        `store_type` VARCHAR(100) NOT NULL,
        `contact_number` VARCHAR(20) NOT NULL,
        `qr_code_token` VARCHAR(100) NOT NULL UNIQUE,
        `logo_path` VARCHAR(255) DEFAULT NULL,
        `theme_color` VARCHAR(7) DEFAULT '#0284C7',
        `theme_bg` VARCHAR(20) DEFAULT 'cozy',
        `font_style` VARCHAR(50) DEFAULT 'outfit',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Create Items Table
    $conn->exec("CREATE TABLE IF NOT EXISTS `items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `vendor_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `description` TEXT,
        `price` DECIMAL(10,2) NOT NULL,
        `image_path` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Create Customers Table
    $conn->exec("CREATE TABLE IF NOT EXISTS `customers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(20) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if dummy data already exists to avoid duplication
    $stmt = $conn->query("SELECT COUNT(*) FROM `vendors`");
    $vendorCount = $stmt->fetchColumn();

    if ($vendorCount == 0) {
        // Insert dummy vendors (Passwords are 'password123')
        $dummyPassword = password_hash('password123', PASSWORD_DEFAULT);
        
        // Vendor 1: Organic Greens
        $stmt1 = $conn->prepare("INSERT INTO `vendors` (shop_name, owner_name, email, password, shop_description, address, store_type, contact_number, qr_code_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->execute([
            'Organic Greens & Co.', 
            'John Greens',
            'greens@localmart.com', 
            $dummyPassword, 
            'Your local destination for fresh, pesticide-free vegetables, fruits, and handpicked organic herbs sourced daily from regional farms.', 
            '123 Green Valley Road, Freshwood',
            'Grocery',
            '+1 (555) 123-4567',
            'token_greens'
        ]);
        $vendor1Id = $conn->lastInsertId();

        // Vendor 2: The Artisan Bakery
        $stmt2 = $conn->prepare("INSERT INTO `vendors` (shop_name, owner_name, email, password, shop_description, address, store_type, contact_number, qr_code_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->execute([
            'The Artisan Bakery', 
            'Sarah Baker',
            'bakery@localmart.com', 
            $dummyPassword, 
            'Crafting sourdough breads, artisanal croissants, pastries, and delectable sweet treats baked fresh every single morning using traditional stone-ground flour.', 
            '456 Oven Lane, Yeastville',
            'Bakery',
            '+1 (555) 987-6543',
            'token_bakery'
        ]);
        $vendor2Id = $conn->lastInsertId();

        // Insert items for Vendor 1 (Greens)
        $itemStmt = $conn->prepare("INSERT INTO `items` (vendor_id, name, description, price, image_path) VALUES (?, ?, ?, ?, ?)");
        $itemStmt->execute([$vendor1Id, 'Organic Avocado Box', 'A carton containing 4 ripe, creamy organic Hass avocados.', 8.50, 'assets/images/placeholder_avocado.svg']);
        $itemStmt->execute([$vendor1Id, 'Fresh Heirloom Tomatoes', '1kg basket of colorful and juicy heirloom tomatoes perfect for salads.', 4.90, 'assets/images/placeholder_tomatoes.svg']);
        $itemStmt->execute([$vendor1Id, 'Baby Spinach Leaves', '250g pack of pre-washed, crunchy, ready-to-eat baby spinach leaves.', 3.20, 'assets/images/placeholder_spinach.svg']);

        // Insert items for Vendor 2 (Bakery)
        $itemStmt->execute([$vendor2Id, 'Signature Sourdough', 'Our classic crusty sourdough bread loaf made with wild yeast starter.', 6.00, 'assets/images/placeholder_sourdough.svg']);
        $itemStmt->execute([$vendor2Id, 'French Butter Croissant', 'Flaky, golden, and layered with rich Normandy butter.', 3.50, 'assets/images/placeholder_croissant.svg']);
        $itemStmt->execute([$vendor2Id, 'Blueberry Almond Tart', 'Sweet pastry crust filled with almond frangipane and fresh blueberries.', 5.50, 'assets/images/placeholder_tart.svg']);

        // Insert dummy customers (Passwords are 'custpassword123')
        $dummyCustPassword = password_hash('custpassword123', PASSWORD_DEFAULT);
        $custStmt = $conn->prepare("INSERT INTO `customers` (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
        $custStmt->execute(['Alice Smith', 'alice@gmail.com', $dummyCustPassword, '+1 (555) 321-4321', '789 Maple Avenue, Oakville']);
        $custStmt->execute(['Bob Johnson', 'bob@gmail.com', $dummyCustPassword, '+1 (555) 654-7890', '321 Pine Lane, Mapleton']);
    }

    $setupSuccess = true;
} catch (PDOException $e) {
    $setupSuccess = false;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | LocalMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #FAF7F2;
            --card-bg: #FFFFFF;
            --text-main: #1C2D37;
            --primary-blue: #0284C7;
            --primary-green: #16A34A;
            --border-color: #EADEC9;
            --accent-gold: #B89047;
            --shadow: 0 10px 30px rgba(27, 43, 54, 0.05);
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: var(--shadow);
            text-align: center;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .status-box {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
        }
        .status-success {
            background-color: rgba(22, 163, 74, 0.1);
            border: 1px solid var(--primary-green);
            color: var(--primary-green);
        }
        .status-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid #EF4444;
            color: #EF4444;
        }
        p {
            line-height: 1.6;
            color: #556B77;
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-blue), #0369A1);
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LocalMart Setup</h1>
        
        <?php if (isset($setupSuccess) && $setupSuccess): ?>
            <div class="status-box status-success">
                Database initialized successfully!
            </div>
            <p>Tables <strong>vendors</strong> and <strong>items</strong> have been created. Mock stores and sample items are configured for instant testing.</p>
            <a href="index.php" class="btn">Go to Homepage</a>
        <?php else: ?>
            <div class="status-box status-error">
                Database Setup Failed
            </div>
            <p>Error: <?php echo htmlspecialchars($errorMessage); ?></p>
            <a href="install.php" class="btn">Try Again</a>
        <?php endif; ?>
    </div>
</body>
</html>
