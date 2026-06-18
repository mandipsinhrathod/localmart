<?php
require_once 'includes/db_config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication Check
if (!isset($_SESSION['vendor_id'])) {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['vendor_id'];

// 2. Fetch Logged-in Vendor Details
try {
    $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
    $stmt->execute([$vendor_id]);
    $vendor = $stmt->fetch();

    if (!$vendor) {
        // Destroy session if vendor not found
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// 3. Generate QR Code URL
$http_host = $_SERVER['HTTP_HOST'];
$current_dir = dirname($_SERVER['PHP_SELF']);
$current_dir = str_replace('\\', '/', $current_dir);
if ($current_dir === '/') {
    $current_dir = '';
}
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$store_url = "$protocol://$http_host$current_dir/store.php?code=" . $vendor['qr_code_token'];
$qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($store_url);

// 4. Handle Printable Poster View
if (isset($_GET['print']) && $_GET['print'] === '1') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Store Signage | <?php echo htmlspecialchars($vendor['shop_name']); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-blue: #0284C7;
                --primary-green: #16A34A;
                --accent-gold: #B89047;
                --text-main: #1A2E3B;
                --text-muted: #5E717E;
                --border-color: #EADEC9;
            }
            body {
                font-family: 'Outfit', sans-serif;
                background-color: #ffffff;
                color: var(--text-main);
                margin: 0;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                text-align: center;
            }
            .poster-card {
                border: 4px solid var(--accent-gold);
                padding: 60px 40px;
                max-width: 500px;
                width: 100%;
                border-radius: 20px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
                position: relative;
                box-sizing: border-box;
            }
            .shop-title {
                font-family: 'Playfair Display', serif;
                font-size: 3rem;
                margin: 15px 0 25px 0;
                color: var(--text-main);
                font-weight: 700;
            }
            .qr-code {
                width: 280px;
                height: 280px;
                margin: 0 auto;
                padding: 15px;
                border: 1px solid var(--border-color);
                border-radius: 12px;
                background: #ffffff;
                display: block;
                box-shadow: 0 8px 25px rgba(0,0,0,0.02);
            }
            .btn-print {
                background: linear-gradient(135deg, var(--primary-blue), #0369A1);
                color: #ffffff;
                border: none;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 0.95rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-top: 35px;
                box-shadow: 0 4px 15px rgba(2, 132, 199, 0.2);
            }
            .btn-print:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(2, 132, 199, 0.3);
            }
            .btn-close {
                position: absolute;
                top: 20px;
                right: 20px;
                background: transparent;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--text-muted);
            }
            @media print {
                body {
                    padding: 0;
                    min-height: auto;
                }
                .poster-card {
                    border: none;
                    box-shadow: none;
                    padding: 20px;
                    margin: 0 auto;
                }
                .btn-print, .btn-close {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="poster-card">
            <button onclick="window.close()" class="btn-close">&times;</button>
            
            <!-- Logo Section -->
            <?php 
            if (!empty($vendor['logo_path']) && file_exists($vendor['logo_path'])) {
                echo '<img src="' . htmlspecialchars($vendor['logo_path']) . '" alt="' . htmlspecialchars($vendor['shop_name']) . ' Logo" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid var(--accent-gold); margin: 0 auto 15px auto; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">';
            } else {
                $store_emoji = '🏪';
                $store_type_lower = strtolower($vendor['store_type']);
                if (strpos($store_type_lower, 'bakery') !== false || strpos($store_type_lower, 'bake') !== false) {
                    $store_emoji = '🍞';
                } elseif (strpos($store_type_lower, 'grocer') !== false || strpos($store_type_lower, 'fruit') !== false || strpos($store_type_lower, 'veg') !== false || strpos($store_type_lower, 'green') !== false) {
                    $store_emoji = '🍎';
                } elseif (strpos($store_type_lower, 'pharmacy') !== false || strpos($store_type_lower, 'chemist') !== false || strpos($store_type_lower, 'drug') !== false || strpos($store_type_lower, 'medical') !== false) {
                    $store_emoji = '💊';
                } elseif (strpos($store_type_lower, 'cafe') !== false || strpos($store_type_lower, 'coffee') !== false || strpos($store_type_lower, 'tea') !== false) {
                    $store_emoji = '☕';
                } elseif (strpos($store_type_lower, 'cloth') !== false || strpos($store_type_lower, 'boutique') !== false || strpos($store_type_lower, 'fashion') !== false) {
                    $store_emoji = '👕';
                } elseif (strpos($store_type_lower, 'rest') !== false || strpos($store_type_lower, 'diner') !== false || strpos($store_type_lower, 'eat') !== false) {
                    $store_emoji = '🍔';
                }
                echo '<div style="font-size: 5rem; margin-bottom: 15px;">' . $store_emoji . '</div>';
            }
            ?>
            
            <h1 class="shop-title"><?php echo htmlspecialchars($vendor['shop_name']); ?></h1>
            
            <img src="<?php echo $qr_code_url; ?>" alt="QR Code" class="qr-code">
            
            <button onclick="window.print()" class="btn-print">Print Poster Signage</button>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// 5. Handle GET notifications & POST form submissions (CRUD Actions)
$notification = '';
if (isset($_GET['registered'])) {
    $notification = ['type' => 'success', 'message' => 'Store registered successfully! Welcome to your Vendor Admin Panel.'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // A. Update Shop Profile
    if ($action === 'update_profile') {
        $shop_name = trim($_POST['shop_name'] ?? '');
        $owner_name = trim($_POST['owner_name'] ?? '');
        $shop_description = trim($_POST['shop_description'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $store_type = trim($_POST['store_type'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        
        $theme_color = trim($_POST['theme_color'] ?? '#0284C7');
        $theme_bg = trim($_POST['theme_bg'] ?? 'cozy');
        $font_style = trim($_POST['font_style'] ?? 'outfit');

        // Basic validations
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $theme_color)) {
            $theme_color = '#0284C7';
        }
        if (!in_array($theme_bg, ['cozy', 'dark', 'clean', 'glass'])) {
            $theme_bg = 'cozy';
        }
        if (!in_array($font_style, ['outfit', 'inter', 'lora'])) {
            $font_style = 'outfit';
        }

        if (empty($shop_name) || empty($owner_name) || empty($address) || empty($store_type) || empty($contact_number)) {
            $notification = ['type' => 'error', 'message' => 'Please fill out all required profile fields.'];
        } else {
            try {
                // Handle Logo Upload
                $logo_path = $vendor['logo_path'] ?? null;
                if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
                    if ($logo_path && file_exists($logo_path)) {
                        unlink($logo_path);
                    }
                    $logo_path = null;
                }

                if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                    $logoTmpPath = $_FILES['logo']['tmp_name'];
                    $logoName = $_FILES['logo']['name'];
                    $logoExtension = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));

                    $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                    if (in_array($logoExtension, $allowed)) {
                        $uploadDir = 'assets/images/uploads/';
                        if (!file_exists($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        $newLogoName = 'logo_' . $vendor_id . '_' . time() . '.' . $logoExtension;
                        $destPath = $uploadDir . $newLogoName;
                        if (move_uploaded_file($logoTmpPath, $destPath)) {
                            // Delete old logo if exists
                            if ($logo_path && file_exists($logo_path)) {
                                unlink($logo_path);
                            }
                            $logo_path = $destPath;
                        }
                    } else {
                        $notification = ['type' => 'error', 'message' => 'Invalid logo image format. Allowed formats: JPG, JPEG, PNG, WEBP, SVG.'];
                    }
                }

                $updateStmt = $conn->prepare("UPDATE vendors SET shop_name = ?, owner_name = ?, shop_description = ?, address = ?, store_type = ?, contact_number = ?, logo_path = ?, theme_color = ?, theme_bg = ?, font_style = ? WHERE id = ?");
                $updateStmt->execute([$shop_name, $owner_name, $shop_description, $address, $store_type, $contact_number, $logo_path, $theme_color, $theme_bg, $font_style, $vendor_id]);
                
                // Refresh local vendor variables
                $_SESSION['vendor_name'] = $shop_name;
                $vendor['shop_name'] = $shop_name;
                $vendor['owner_name'] = $owner_name;
                $vendor['shop_description'] = $shop_description;
                $vendor['address'] = $address;
                $vendor['store_type'] = $store_type;
                $vendor['contact_number'] = $contact_number;
                $vendor['logo_path'] = $logo_path;
                $vendor['theme_color'] = $theme_color;
                $vendor['theme_bg'] = $theme_bg;
                $vendor['font_style'] = $font_style;
                
                $notification = ['type' => 'success', 'message' => 'Shop profile and branding settings updated successfully.'];
            } catch (PDOException $e) {
                $notification = ['type' => 'error', 'message' => 'Profile update failed: ' . $e->getMessage()];
            }
        }
    }

    // B. Add Catalog Product
    elseif ($action === 'add_item') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $availability = trim($_POST['availability'] ?? 'In Stock');
        $weight_qty = trim($_POST['weight_qty'] ?? '');
        $product_type = trim($_POST['product_type'] ?? '');
        $shelf_life = trim($_POST['shelf_life'] ?? '');
        $grade = trim($_POST['grade'] ?? 'No Grade');
        $price_unit = trim($_POST['price_unit'] ?? 'kg');
        $image_path = 'assets/images/placeholder_product.svg';

        if (empty($name) || $price <= 0) {
            $notification = ['type' => 'error', 'message' => 'Please enter a valid product name and positive price.'];
        } else {
            // Check image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                if (in_array($fileExtension, $allowed)) {
                    $uploadDir = 'assets/images/uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $newFileName = 'item_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                    $destPath = $uploadDir . $newFileName;
                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $image_path = $destPath;
                    }
                }
            } else {
                // Pick generic placeholder based on product name if matches
                $name_lower = strtolower($name);
                if (strpos($name_lower, 'avocado') !== false) {
                    $image_path = 'assets/images/placeholder_avocado.svg';
                } elseif (strpos($name_lower, 'croissant') !== false) {
                    $image_path = 'assets/images/placeholder_croissant.svg';
                } elseif (strpos($name_lower, 'sourdough') !== false || strpos($name_lower, 'bread') !== false) {
                    $image_path = 'assets/images/placeholder_sourdough.svg';
                } elseif (strpos($name_lower, 'spinach') !== false) {
                    $image_path = 'assets/images/placeholder_spinach.svg';
                } elseif (strpos($name_lower, 'tart') !== false || strpos($name_lower, 'pastry') !== false) {
                    $image_path = 'assets/images/placeholder_tart.svg';
                } elseif (strpos($name_lower, 'tomato') !== false) {
                    $image_path = 'assets/images/placeholder_tomatoes.svg';
                }
            }

            try {
                // Generate a unique 8-digit product ID
                $productId = 0;
                do {
                    $productId = rand(10000000, 99999999);
                    $checkItemId = $conn->prepare("SELECT id FROM items WHERE id = ?");
                    $checkItemId->execute([$productId]);
                } while ($checkItemId->fetch());

                $insertStmt = $conn->prepare("INSERT INTO items (id, vendor_id, name, description, price, image_path, availability, weight_qty, product_type, shelf_life, grade, price_unit) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insertStmt->execute([$productId, $vendor_id, $name, $description, $price, $image_path, $availability, $weight_qty, $product_type, $shelf_life, $grade, $price_unit]);
                $notification = ['type' => 'success', 'message' => 'Product listing created successfully!'];
            } catch (PDOException $e) {
                $notification = ['type' => 'error', 'message' => 'Failed to add item: ' . $e->getMessage()];
            }
        }
    }

    // C. Edit Catalog Product
    elseif ($action === 'edit_item') {
        $item_id = intval($_POST['item_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        
        $availability = trim($_POST['availability'] ?? 'In Stock');
        $weight_qty = trim($_POST['weight_qty'] ?? '');
        $product_type = trim($_POST['product_type'] ?? '');
        $shelf_life = trim($_POST['shelf_life'] ?? '');
        $grade = trim($_POST['grade'] ?? 'No Grade');
        $price_unit = trim($_POST['price_unit'] ?? 'kg');

        if (empty($name) || $price <= 0 || $item_id <= 0) {
            $notification = ['type' => 'error', 'message' => 'Please enter valid product specifications.'];
        } else {
            try {
                // Verify owner
                $checkStmt = $conn->prepare("SELECT id, image_path FROM items WHERE id = ? AND vendor_id = ?");
                $checkStmt->execute([$item_id, $vendor_id]);
                $existingItem = $checkStmt->fetch();

                if ($existingItem) {
                    $image_path = $existingItem['image_path'];

                    // Check if a new image was uploaded
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['image']['tmp_name'];
                        $fileName = $_FILES['image']['name'];
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                        $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                        if (in_array($fileExtension, $allowed)) {
                            $uploadDir = 'assets/images/uploads/';
                            $newFileName = 'item_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
                            $destPath = $uploadDir . $newFileName;
                            if (move_uploaded_file($fileTmpPath, $destPath)) {
                                // Delete old file if uploaded previously
                                if ($image_path && strpos($image_path, 'uploads/') !== false && file_exists($image_path)) {
                                    unlink($image_path);
                                }
                                $image_path = $destPath;
                            }
                        }
                    }

                    $updateItemStmt = $conn->prepare("UPDATE items SET name = ?, description = ?, price = ?, image_path = ?, availability = ?, weight_qty = ?, product_type = ?, shelf_life = ?, grade = ?, price_unit = ? WHERE id = ? AND vendor_id = ?");
                    $updateItemStmt->execute([$name, $description, $price, $image_path, $availability, $weight_qty, $product_type, $shelf_life, $grade, $price_unit, $item_id, $vendor_id]);
                    $notification = ['type' => 'success', 'message' => 'Product updated successfully.'];
                } else {
                    $notification = ['type' => 'error', 'message' => 'Unauthorized action or product not found.'];
                }
            } catch (PDOException $e) {
                $notification = ['type' => 'error', 'message' => 'Failed to edit item: ' . $e->getMessage()];
            }
        }
    }

    // D. Delete Catalog Product
    elseif ($action === 'delete_item') {
        $item_id = intval($_POST['item_id'] ?? 0);

        try {
            // Verify owner & retrieve image path
            $checkStmt = $conn->prepare("SELECT image_path FROM items WHERE id = ? AND vendor_id = ?");
            $checkStmt->execute([$item_id, $vendor_id]);
            $existingItem = $checkStmt->fetch();

            if ($existingItem) {
                $image_path = $existingItem['image_path'];
                
                // Delete image if it is an uploaded file
                if ($image_path && strpos($image_path, 'uploads/') !== false && file_exists($image_path)) {
                    unlink($image_path);
                }

                $deleteStmt = $conn->prepare("DELETE FROM items WHERE id = ? AND vendor_id = ?");
                $deleteStmt->execute([$item_id, $vendor_id]);
                $notification = ['type' => 'success', 'message' => 'Product listing removed.'];
            } else {
                $notification = ['type' => 'error', 'message' => 'Product not found or unauthorized.'];
            }
        } catch (PDOException $e) {
            $notification = ['type' => 'error', 'message' => 'Failed to delete item: ' . $e->getMessage()];
        }
    }
}

// 6. Fetch Catalog Items for Listing
try {
    $itemsStmt = $conn->prepare("SELECT * FROM items WHERE vendor_id = ? ORDER BY id DESC");
    $itemsStmt->execute([$vendor_id]);
    $products = $itemsStmt->fetchAll();
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

$page_title = "Vendor Admin Dashboard";
$custom_styles_vendor = $vendor;
require_once 'includes/header.php';
?>

<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Dashboard Status Messages -->
    <?php if (!empty($notification)): ?>
        <div class="alert alert-<?php echo $notification['type']; ?>" style="margin-bottom: 25px; padding: 15px; border-radius: var(--border-radius-sm); border: 1px solid; display: flex; align-items: center; gap: 10px; <?php echo $notification['type'] === 'success' ? 'background: rgba(22, 163, 74, 0.08); border-color: var(--primary-green); color: var(--primary-green);' : 'background: var(--danger-light); border-color: var(--danger); color: var(--danger);'; ?>">
            <span><?php echo $notification['type'] === 'success' ? '✅' : '⚠️'; ?></span>
            <span><?php echo htmlspecialchars($notification['message']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Dashboard Main View -->
    <div class="dashboard-grid">
        <!-- LEFT COLUMN: SIDEBAR PROFILE & QR CODE -->
        <div class="dashboard-sidebar-card">
            <!-- Vendor Info -->
            <div class="dashboard-profile-section">
                <?php if (!empty($vendor['logo_path']) && file_exists($vendor['logo_path'])): ?>
                    <img src="<?php echo htmlspecialchars($vendor['logo_path']); ?>" alt="<?php echo htmlspecialchars($vendor['shop_name']); ?> Logo" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid var(--border-color); margin-bottom: 15px; box-shadow: var(--shadow-sm); background-color: var(--card-bg);">
                <?php else: ?>
                    <?php 
                    $store_emoji = '🏪';
                    $store_type_lower = strtolower($vendor['store_type']);
                    if (strpos($store_type_lower, 'bakery') !== false || strpos($store_type_lower, 'bake') !== false) {
                        $store_emoji = '🍞';
                    } elseif (strpos($store_type_lower, 'grocer') !== false || strpos($store_type_lower, 'fruit') !== false || strpos($store_type_lower, 'veg') !== false || strpos($store_type_lower, 'green') !== false) {
                        $store_emoji = '🍎';
                    } elseif (strpos($store_type_lower, 'pharmacy') !== false || strpos($store_type_lower, 'chemist') !== false || strpos($store_type_lower, 'drug') !== false || strpos($store_type_lower, 'medical') !== false) {
                        $store_emoji = '💊';
                    } elseif (strpos($store_type_lower, 'cafe') !== false || strpos($store_type_lower, 'coffee') !== false || strpos($store_type_lower, 'tea') !== false) {
                        $store_emoji = '☕';
                    } elseif (strpos($store_type_lower, 'cloth') !== false || strpos($store_type_lower, 'boutique') !== false || strpos($store_type_lower, 'fashion') !== false) {
                        $store_emoji = '👕';
                    } elseif (strpos($store_type_lower, 'rest') !== false || strpos($store_type_lower, 'diner') !== false || strpos($store_type_lower, 'eat') !== false) {
                        $store_emoji = '🍔';
                    }
                    ?>
                    <div style="font-size: 3.5rem; margin-bottom: 15px;"><?php echo $store_emoji; ?></div>
                <?php endif; ?>
                <h2 class="dashboard-shop-name" style="font-family: var(--font-heading); font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($vendor['shop_name']); ?></h2>
                <div class="dashboard-shop-email" style="font-size: 0.9rem; color: var(--text-muted); font-weight: 500; margin-bottom: 5px;"><?php echo htmlspecialchars($vendor['store_type']); ?> Store</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); opacity: 0.8;"><?php echo htmlspecialchars($vendor['email']); ?></div>
            </div>

            <!-- Profile Meta Info Card List -->
            <div style="display: flex; flex-direction: column; gap: 12px; border-bottom: 1px solid var(--border-color-light); padding-bottom: 25px; margin-bottom: 10px;">
                <!-- Owner -->
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(0, 0, 0, 0.015); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color-light);">
                    <div style="font-size: 1.25rem; opacity: 0.75;">👤</div>
                    <div style="flex-grow: 1; text-align: left;">
                        <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; font-weight: 600;">Store Owner</span>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($vendor['owner_name']); ?></span>
                    </div>
                </div>

                <!-- Contact -->
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(0, 0, 0, 0.015); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color-light);">
                    <div style="font-size: 1.25rem; opacity: 0.75;">📞</div>
                    <div style="flex-grow: 1; text-align: left;">
                        <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; font-weight: 600;">Contact Number</span>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-main);"><?php echo htmlspecialchars($vendor['contact_number']); ?></span>
                    </div>
                </div>

                <!-- Address -->
                <div style="display: flex; align-items: center; gap: 12px; background: rgba(0, 0, 0, 0.015); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color-light);">
                    <div style="font-size: 1.25rem; opacity: 0.75;">📍</div>
                    <div style="flex-grow: 1; text-align: left;">
                        <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; font-weight: 600;">Store Address</span>
                        <span style="font-size: 0.95rem; font-weight: 500; color: var(--text-main); line-height: 1.4; display: block;"><?php echo htmlspecialchars($vendor['address']); ?></span>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($vendor['shop_description'])): ?>
                    <div style="display: flex; flex-direction: column; gap: 4px; background: rgba(0, 0, 0, 0.015); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color-light); text-align: left;">
                        <span style="display: block; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; font-weight: 600; margin-bottom: 2px;">Shop Description</span>
                        <span style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5; font-style: italic;"><?php echo htmlspecialchars($vendor['shop_description']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Shop Settings Actions -->
            <div style="margin-top: 15px; display: flex; gap: 10px; flex-direction: column;">
                <button onclick="openModal('edit-profile-modal')" class="btn btn-secondary" style="width: 100%; padding: 12px; font-size: 0.9rem; display: flex; justify-content: center; align-items: center; gap: 8px; font-weight: 600; border-radius: 8px; transition: var(--transition-smooth);">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Edit Store Profile &amp; Branding
                </button>
            </div>

            <!-- QR Code display -->
            <div class="qr-code-display-card">
                <h4 style="font-family: var(--font-heading); margin-bottom: 8px;">Storefront QR Code</h4>
                <p class="qr-code-meta">Let customers scan to explore products.</p>
                <img src="<?php echo $qr_code_url; ?>" alt="Store QR Code" class="qr-code-img">
                <div style="display: flex; gap: 10px; flex-direction: column; margin-top: 10px;">
                    <a href="dashboard.php?print=1" target="_blank" class="btn btn-primary" style="padding: 10px; font-size: 0.88rem; font-weight: 600; text-align: center; text-decoration: none; box-shadow: none;">
                        🖨️ Print Store Signage
                    </a>
                    <a href="store.php?code=<?php echo urlencode($vendor['qr_code_token']); ?>" target="_blank" style="font-size: 0.85rem; color: var(--primary-blue); font-weight: 600; text-decoration: underline; display: block; margin-top: 5px;">
                        Visit Public Storefront &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: ITEM MANAGEMENT AREA -->
        <div class="dashboard-main-area">
            <?php
            $avgPrice = 0;
            if (!empty($products)) {
                $totalVal = array_sum(array_column($products, 'price'));
                $avgPrice = $totalVal / count($products);
            }
            ?>
            <!-- Stats Cards Row -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 5px;">
                <!-- Card 1: Total Listings -->
                <div style="background: var(--card-bg); border: 1px solid var(--border-color-light); border-radius: 12px; padding: 18px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 1.8rem; background-color: var(--primary-blue-light); color: var(--primary-blue); width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">📦</div>
                    <div>
                        <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-main); line-height: 1.2;"><?php echo count($products); ?></div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Active Items</div>
                    </div>
                </div>

                <!-- Card 2: Avg Price -->
                <div style="background: var(--card-bg); border: 1px solid var(--border-color-light); border-radius: 12px; padding: 18px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 1.8rem; background-color: rgba(22, 163, 74, 0.08); color: var(--primary-green); width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">💰</div>
                    <div>
                        <div style="font-size: 1.4rem; font-weight: 700; color: var(--text-main); line-height: 1.2;">₹<?php echo number_format($avgPrice, 2); ?></div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Avg. Pricing</div>
                    </div>
                </div>

                <!-- Card 3: Status -->
                <div style="background: var(--card-bg); border: 1px solid var(--border-color-light); border-radius: 12px; padding: 18px; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 1.8rem; background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold); width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">✨</div>
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary-green); line-height: 1.2;">Live Store</div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px;">Status</div>
                    </div>
                </div>
            </div>

            <div class="items-list-container">
                <!-- Catalog Header -->
                <div class="dashboard-panel-header" style="border-bottom: 1px solid var(--border-color-light); padding-bottom: 20px; margin-bottom: 25px;">
                    <div>
                        <span style="font-weight: 600; color: var(--accent-gold); text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.8rem;">Catalog Management</span>
                        <h2 style="font-family: var(--font-heading); font-size: 1.8rem; margin-top: 5px;">Products Inventory</h2>
                    </div>
                    <button onclick="openModal('add-item-modal')" class="btn btn-primary" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Add New Product
                    </button>
                </div>

                <!-- Products Table -->
                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <h3 style="font-family: var(--font-heading); font-size: 1.4rem;">No Products Listed</h3>
                        <p style="color: var(--text-muted); margin-bottom: 20px; font-size: 0.95rem;">You haven't listed any items in your catalog yet. Click the button above to add your first product.</p>
                        <button onclick="openModal('add-item-modal')" class="btn btn-primary" style="padding: 10px 20px;">List Your First Product</button>
                    </div>
                <?php else: ?>
                    <div class="custom-table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Image</th>
                                    <th>Product Details</th>
                                    <th>Price</th>
                                    <th style="width: 150px; text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($prod['image_path']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="item-thumb">
                                        </td>
                                        <td>
                                            <div class="item-name-cell"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            <div style="font-size: 0.85rem; color: var(--text-muted); max-width: 380px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; margin-bottom: 5px;"><?php echo htmlspecialchars($prod['description'] ?: 'No description provided.'); ?></div>
                                            
                                            <!-- Specifications Badges -->
                                            <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                                                <!-- Availability Badge -->
                                                <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; <?php 
                                                    if (($prod['availability'] ?? 'In Stock') === 'In Stock') {
                                                        echo 'background-color: rgba(22, 163, 74, 0.08); color: var(--primary-green);';
                                                    } elseif (($prod['availability'] ?? '') === 'Pre-order') {
                                                        echo 'background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold);';
                                                    } else {
                                                        echo 'background-color: rgba(220, 38, 38, 0.08); color: var(--danger);';
                                                    }
                                                ?>"><?php echo htmlspecialchars($prod['availability'] ?? 'In Stock'); ?></span>
                                                
                                                <!-- Weight / Qty Badge -->
                                                <?php if (!empty($prod['weight_qty'])): ?>
                                                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 500; background-color: var(--primary-blue-light); color: var(--primary-blue);">⚖️ <?php echo htmlspecialchars($prod['weight_qty']); ?></span>
                                                <?php endif; ?>

                                                <!-- Product Type Badge -->
                                                <?php if (!empty($prod['product_type'])): ?>
                                                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 500; background-color: rgba(0,0,0,0.04); color: var(--text-main);"><?php 
                                                        $p_type = $prod['product_type'];
                                                        if ($p_type === 'Veg') echo '🟢 Veg';
                                                        elseif ($p_type === 'Non-Veg') echo '🔴 Non-Veg';
                                                        elseif ($p_type === 'Organic') echo '🌿 Organic';
                                                        elseif ($p_type === 'Packaged') echo '📦 Packaged';
                                                        elseif ($p_type === 'Homemade') echo '🏡 Homemade';
                                                        else echo htmlspecialchars($p_type);
                                                    ?></span>
                                                <?php endif; ?>

                                                <!-- Shelf Life Badge -->
                                                <?php if (!empty($prod['shelf_life'])): ?>
                                                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 500; background-color: rgba(0,0,0,0.04); color: var(--text-muted);" title="Shelf Life">⏳ <?php echo htmlspecialchars($prod['shelf_life']); ?></span>
                                                <?php endif; ?>

                                                <!-- Grade Badge -->
                                                <?php if (!empty($prod['grade']) && $prod['grade'] !== 'No Grade'): ?>
                                                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 20px; font-weight: 600; background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold);">⭐ <?php echo htmlspecialchars($prod['grade']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="item-price-cell" style="white-space: nowrap;">
                                            ₹<?php echo number_format($prod['price'], 2); ?> / <?php echo htmlspecialchars($prod['price_unit'] ?? 'kg'); ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                                <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($prod)); ?>)" class="btn-edit">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                                    Edit
                                                </button>
                                                
                                                <form action="dashboard.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');" style="margin: 0; padding: 0;">
                                                    <input type="hidden" name="action" value="delete_item">
                                                    <input type="hidden" name="item_id" value="<?php echo $prod['id']; ?>">
                                                    <button type="submit" class="btn-delete">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================================================
     MODALS SECTION
     ========================================================================== -->

<!-- 1. ADD ITEM MODAL -->
<div id="add-item-modal" class="modal-overlay">
    <div class="modal-card">
        <button onclick="closeModal('add-item-modal')" class="modal-close-btn">&times;</button>
        <h3 class="modal-title" style="font-family: var(--font-heading);">Add New Product</h3>
        
        <form action="dashboard.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="action" value="add_item">
            
            <div class="form-group">
                <label class="form-label" for="add-name" style="font-weight: 500; display: block; margin-bottom: 8px;">Product Name *</label>
                <input type="text" id="add-name" name="name" class="form-control" placeholder="e.g. Fresh Organic Strawberries" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
            </div>
            
            <!-- Grid for Price, Price Unit, and Weight/Qty -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.2fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="add-price" style="font-weight: 500; display: block; margin-bottom: 8px;">Price (₹) *</label>
                    <input type="number" step="0.01" id="add-price" name="price" class="form-control" placeholder="0.00" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-price-unit" style="font-weight: 500; display: block; margin-bottom: 8px;">Per Unit *</label>
                    <select id="add-price-unit" name="price_unit" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff; height: 48px;">
                        <option value="kg" selected>per kg</option>
                        <option value="piece">per piece</option>
                        <option value="packet">per packet</option>
                        <option value="litre">per litre</option>
                        <option value="dozen">per dozen</option>
                        <option value="gram">per gram</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-weight-qty" style="font-weight: 500; display: block; margin-bottom: 8px;">Weight / Quantity</label>
                    <input type="text" id="add-weight-qty" name="weight_qty" class="form-control" placeholder="e.g. 500g, 1 kg, 6 pcs" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
            </div>
            
            <!-- Grid for Availability and Product Type -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="add-availability" style="font-weight: 500; display: block; margin-bottom: 8px;">Availability *</label>
                    <select id="add-availability" name="availability" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="In Stock" selected>In Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                        <option value="Pre-order">Pre-order</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-product-type" style="font-weight: 500; display: block; margin-bottom: 8px;">Product Type</label>
                    <select id="add-product-type" name="product_type" class="form-control" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="">Select Type...</option>
                        <option value="Veg">🟢 Veg</option>
                        <option value="Non-Veg">🔴 Non-Veg</option>
                        <option value="Organic">🌿 Organic</option>
                        <option value="Packaged">📦 Packaged</option>
                        <option value="Homemade">🏡 Homemade</option>
                        <option value="Other">✨ Other</option>
                    </select>
                </div>
            </div>

            <!-- Grid for Shelf Life and Grade -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="add-shelf-life" style="font-weight: 500; display: block; margin-bottom: 8px;">Shelf Life</label>
                    <input type="text" id="add-shelf-life" name="shelf_life" class="form-control" placeholder="e.g. 3 Days, 6 Months" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="add-grade" style="font-weight: 500; display: block; margin-bottom: 8px;">Grade</label>
                    <select id="add-grade" name="grade" class="form-control" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="No Grade" selected>No Grade</option>
                        <option value="Premium">Premium</option>
                        <option value="Grade A">Grade A</option>
                        <option value="Standard">Standard</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="add-desc" style="font-weight: 500; display: block; margin-bottom: 8px;">Description</label>
                <textarea id="add-desc" name="description" class="form-control" placeholder="e.g. Sweet, juicy strawberries picked from local fields this morning." style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; min-height: 80px; font-family: var(--font-body);"></textarea>
            </div>

           
            <div class="form-group">
                <label class="form-label" for="add-image" style="font-weight: 500; display: block; margin-bottom: 8px;">Product Image</label>
                <input type="file" id="add-image" name="image" accept="image/*" class="form-control" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Supported formats: JPG, JPEG, PNG, WEBP, SVG. Falls back to default placeholder if empty.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px;">Publish Product</button>
        </form>
    </div>
</div>

<!-- 2. EDIT ITEM MODAL -->
<div id="edit-item-modal" class="modal-overlay">
    <div class="modal-card">
        <button onclick="closeModal('edit-item-modal')" class="modal-close-btn">&times;</button>
        <h3 class="modal-title" style="font-family: var(--font-heading);">Edit Product Listing</h3>
        
        <form action="dashboard.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" name="action" value="edit_item">
            <input type="hidden" id="edit-id" name="item_id">
            
            <div class="form-group">
                <label class="form-label" for="edit-name" style="font-weight: 500; display: block; margin-bottom: 8px;">Product Name *</label>
                <input type="text" id="edit-name" name="name" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
            </div>
            
            <!-- Grid for Price, Price Unit, and Weight/Qty -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1.2fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="edit-price" style="font-weight: 500; display: block; margin-bottom: 8px;">Price (₹) *</label>
                    <input type="number" step="0.01" id="edit-price" name="price" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-price-unit" style="font-weight: 500; display: block; margin-bottom: 8px;">Per Unit *</label>
                    <select id="edit-price-unit" name="price_unit" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff; height: 48px;">
                        <option value="kg">per kg</option>
                        <option value="piece">per piece</option>
                        <option value="packet">per packet</option>
                        <option value="litre">per litre</option>
                        <option value="dozen">per dozen</option>
                        <option value="gram">per gram</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-weight-qty" style="font-weight: 500; display: block; margin-bottom: 8px;">Weight / Quantity</label>
                    <input type="text" id="edit-weight-qty" name="weight_qty" class="form-control" placeholder="e.g. 500g, 1 kg, 6 pcs" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
            </div>
            
            <!-- Grid for Availability and Product Type -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="edit-availability" style="font-weight: 500; display: block; margin-bottom: 8px;">Availability *</label>
                    <select id="edit-availability" name="availability" class="form-control" required style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="In Stock">In Stock</option>
                        <option value="Out of Stock">Out of Stock</option>
                        <option value="Pre-order">Pre-order</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-product-type" style="font-weight: 500; display: block; margin-bottom: 8px;">Product Type</label>
                    <select id="edit-product-type" name="product_type" class="form-control" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="">Select Type...</option>
                        <option value="Veg">🟢 Veg</option>
                        <option value="Non-Veg">🔴 Non-Veg</option>
                        <option value="Organic">🌿 Organic</option>
                        <option value="Packaged">📦 Packaged</option>
                        <option value="Homemade">🏡 Homemade</option>
                        <option value="Other">✨ Other</option>
                    </select>
                </div>
            </div>

            <!-- Grid for Shelf Life and Grade -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="edit-shelf-life" style="font-weight: 500; display: block; margin-bottom: 8px;">Shelf Life</label>
                    <input type="text" id="edit-shelf-life" name="shelf_life" class="form-control" placeholder="e.g. 3 Days, 6 Months" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
                <div class="form-group">
                    <label class="form-label" for="edit-grade" style="font-weight: 500; display: block; margin-bottom: 8px;">Grade</label>
                    <select id="edit-grade" name="grade" class="form-control" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="No Grade">No Grade</option>
                        <option value="Premium">Premium</option>
                        <option value="Grade A">Grade A</option>
                        <option value="Standard">Standard</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="edit-desc" style="font-weight: 500; display: block; margin-bottom: 8px;">Description</label>
                <textarea id="edit-desc" name="description" class="form-control" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; min-height: 80px; font-family: var(--font-body);"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="edit-image" style="font-weight: 500; display: block; margin-bottom: 8px;">Replace Product Image</label>
                <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                    <img id="edit-img-preview" src="" alt="Current Image" style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--border-radius-sm); border: 1px solid var(--border-color); background-color: var(--bg-color);">
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Current product photo preview</div>
                </div>
                <input type="file" id="edit-image" name="image" accept="image/*" class="form-control" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                <small style="color: var(--text-muted); display: block; margin-top: 5px;">Leave empty to keep current image.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px;">Save Changes</button>
        </form>
    </div>
</div>

<!-- 3. EDIT PROFILE MODAL -->
<div id="edit-profile-modal" class="modal-overlay">
    <div class="modal-card" style="max-width: 650px;">
        <button onclick="closeModal('edit-profile-modal')" class="modal-close-btn">&times;</button>
        <h3 class="modal-title" style="font-family: var(--font-heading);">Edit Store Profile &amp; Styles</h3>
        
        <form action="dashboard.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 18px;">
            <input type="hidden" name="action" value="update_profile">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="profile-shop-name" style="font-weight: 500; display: block; margin-bottom: 6px;">Shop Name *</label>
                    <input type="text" id="profile-shop-name" name="shop_name" class="form-control" value="<?php echo htmlspecialchars($vendor['shop_name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="profile-owner-name" style="font-weight: 500; display: block; margin-bottom: 6px;">Owner Name *</label>
                    <input type="text" id="profile-owner-name" name="owner_name" class="form-control" value="<?php echo htmlspecialchars($vendor['owner_name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="profile-store-type" style="font-weight: 500; display: block; margin-bottom: 6px;">Category *</label>
                    <select id="profile-store-type" name="store_type" class="form-control" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="Grocery" <?php echo $vendor['store_type'] === 'Grocery' ? 'selected' : ''; ?>>Grocery</option>
                        <option value="Bakery" <?php echo $vendor['store_type'] === 'Bakery' ? 'selected' : ''; ?>>Bakery</option>
                        <option value="Cafe" <?php echo $vendor['store_type'] === 'Cafe' ? 'selected' : ''; ?>>Cafe</option>
                        <option value="Pharmacy" <?php echo $vendor['store_type'] === 'Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
                        <option value="Boutique" <?php echo $vendor['store_type'] === 'Boutique' ? 'selected' : ''; ?>>Boutique</option>
                        <option value="Restaurant" <?php echo $vendor['store_type'] === 'Restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                        <option value="Other" <?php echo !in_array($vendor['store_type'], ['Grocery', 'Bakery', 'Cafe', 'Pharmacy', 'Boutique', 'Restaurant']) ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="profile-contact" style="font-weight: 500; display: block; margin-bottom: 6px;">Contact Number *</label>
                    <input type="text" id="profile-contact" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($vendor['contact_number']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="profile-address" style="font-weight: 500; display: block; margin-bottom: 6px;">Physical Address *</label>
                <input type="text" id="profile-address" name="address" class="form-control" value="<?php echo htmlspecialchars($vendor['address']); ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none;">
            </div>
            
            <div class="form-group">
                <label class="form-label" for="profile-desc" style="font-weight: 500; display: block; margin-bottom: 6px;">Shop Description</label>
                <textarea id="profile-desc" name="shop_description" class="form-control" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; min-height: 70px; font-family: var(--font-body);"><?php echo htmlspecialchars($vendor['shop_description']); ?></textarea>
            </div>
            
            <!-- Section Divider -->
            <div style="border-top: 1px solid var(--border-color); margin-top: 5px; padding-top: 15px;">
                <h4 style="font-family: var(--font-heading); font-size: 1.15rem; margin-bottom: 12px; color: var(--text-main);">Store Branding &amp; Styles</h4>
            </div>

            <!-- Grid for Logo Upload and Font Style -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="profile-logo" style="font-weight: 500; display: block; margin-bottom: 6px;">Store Logo</label>
                    <?php if (!empty($vendor['logo_path']) && file_exists($vendor['logo_path'])): ?>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                            <img src="<?php echo htmlspecialchars($vendor['logo_path']); ?>" alt="Current Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%; border: 1px solid var(--border-color);">
                            <label style="font-size: 0.85rem; color: var(--danger); cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                <input type="checkbox" name="remove_logo" value="1"> Remove logo
                            </label>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="profile-logo" name="logo" accept="image/*" class="form-control" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; font-size: 0.85rem;">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="profile-font-style" style="font-weight: 500; display: block; margin-bottom: 6px;">Typography Style</label>
                    <select id="profile-font-style" name="font_style" class="form-control" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="outfit" <?php echo ($vendor['font_style'] ?? 'outfit') === 'outfit' ? 'selected' : ''; ?>>Playfair &amp; Outfit (Elegant Serif)</option>
                        <option value="inter" <?php echo ($vendor['font_style'] ?? 'outfit') === 'inter' ? 'selected' : ''; ?>>Inter &amp; Roboto (Modern Clean)</option>
                        <option value="lora" <?php echo ($vendor['font_style'] ?? 'outfit') === 'lora' ? 'selected' : ''; ?>>Lora &amp; Merriweather (Classic Editorial)</option>
                    </select>
                </div>
            </div>

            <!-- Grid for Theme and Color -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="profile-theme-bg" style="font-weight: 500; display: block; margin-bottom: 6px;">Theme Mode</label>
                    <select id="profile-theme-bg" name="theme_bg" class="form-control" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); outline: none; background: #ffffff;">
                        <option value="cozy" <?php echo ($vendor['theme_bg'] ?? 'cozy') === 'cozy' ? 'selected' : ''; ?>>Cozy Cream (Default)</option>
                        <option value="clean" <?php echo ($vendor['theme_bg'] ?? 'cozy') === 'clean' ? 'selected' : ''; ?>>Clean Slate (Light)</option>
                        <option value="dark" <?php echo ($vendor['theme_bg'] ?? 'cozy') === 'dark' ? 'selected' : ''; ?>>Sleek Charcoal (Dark)</option>
                        <option value="glass" <?php echo ($vendor['theme_bg'] ?? 'cozy') === 'glass' ? 'selected' : ''; ?>>Modern Glassmorphism</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 500; display: block; margin-bottom: 6px;">Theme Primary Color</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="color" id="profile-theme-color" name="theme_color" value="<?php echo htmlspecialchars($vendor['theme_color'] ?? '#0284C7'); ?>" style="border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); width: 44px; height: 40px; padding: 2px; cursor: pointer; background: #ffffff; box-sizing: border-box;">
                        
                        <!-- Presets helper -->
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <span onclick="setPresetColor('#0284C7')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #0284C7; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="LocalMart Blue"></span>
                            <span onclick="setPresetColor('#16A34A')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #16A34A; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="Emerald Green"></span>
                            <span onclick="setPresetColor('#B89047')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #B89047; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="Royal Gold"></span>
                            <span onclick="setPresetColor('#8B5CF6')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #8B5CF6; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="Lavender Purple"></span>
                            <span onclick="setPresetColor('#DC2626')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #DC2626; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="Crimson Red"></span>
                            <span onclick="setPresetColor('#334155')" style="width: 22px; height: 22px; border-radius: 50%; background-color: #334155; display: inline-block; cursor: pointer; border: 1px solid rgba(0,0,0,0.15);" title="Slate Gray"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; margin-top: 10px;">Save Profile Settings</button>
        </form>
    </div>
</div>

<!-- ==========================================================================
     SCRIPTS SECTION
     ========================================================================== -->
<script>
    // Open/Close Modal Utilities
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }
    
    // Close modal when clicking on background overlay
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.style.display = 'none';
        }
    }
    
    // Setup and open Edit Product Modal
    function editProduct(product) {
        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-price').value = product.price;
        document.getElementById('edit-desc').value = product.description || '';
        document.getElementById('edit-img-preview').src = product.image_path || 'assets/images/placeholder_product.svg';
        
        // Populate new specification fields
        document.getElementById('edit-availability').value = product.availability || 'In Stock';
        document.getElementById('edit-weight-qty').value = product.weight_qty || '';
        document.getElementById('edit-product-type').value = product.product_type || '';
        document.getElementById('edit-shelf-life').value = product.shelf_life || '';
        document.getElementById('edit-grade').value = product.grade || 'No Grade';
        document.getElementById('edit-price-unit').value = product.price_unit || 'kg';
        
        openModal('edit-item-modal');
    }

    // Set brand theme color from presets
    function setPresetColor(color) {
        document.getElementById('profile-theme-color').value = color;
    }
</script>

<?php
require_once 'includes/footer.php';
?>