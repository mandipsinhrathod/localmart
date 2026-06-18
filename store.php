<?php
require_once 'includes/db_config.php';

// Validate store token
$token = trim($_GET['code'] ?? '');

if (empty($token)) {
    header("Location: index.php");
    exit();
}

try {
    // Fetch store details including branding properties
    $stmt = $conn->prepare("SELECT id, shop_name, shop_description, store_type, qr_code_token, logo_path, theme_color, theme_bg, font_style FROM vendors WHERE qr_code_token = ?");
    $stmt->execute([$token]);
    $store = $stmt->fetch();

    if (!$store) {
        $storeNotFound = true;
    } else {
        $storeNotFound = false;
        // Fetch all products listed by this store including specifications
        $itemsStmt = $conn->prepare("SELECT name, description, price, image_path, availability, weight_qty, product_type, shelf_life, grade, price_unit FROM items WHERE vendor_id = ? ORDER BY id DESC");
        $itemsStmt->execute([$store['id']]);
        $products = $itemsStmt->fetchAll();
    }
} catch (PDOException $e) {
    die("Error retrieving store catalog: " . $e->getMessage());
}

$page_title = $storeNotFound ? "Store Not Found" : $store['shop_name'];
if (!$storeNotFound) {
    $custom_styles_vendor = $store;
}
require_once 'includes/header.php';
?>

<?php if ($storeNotFound): ?>
    <!-- Store Not Found State -->
    <div class="container" style="padding: 100px 0; text-align: center; max-width: 600px;">
        <div style="font-size: 4rem; margin-bottom: 20px;">🕵️‍♂️</div>
        <h1 style="font-family: var(--font-heading); margin-bottom: 15px;">Store Not Found</h1>
        <p style="margin-bottom: 30px;">The store QR code you scanned does not match any active shop in our system. The vendor may have updated their profile, or the link may be outdated.</p>
        <a href="index.php" class="btn btn-primary">Return to Homepage</a>
    </div>
<?php else: ?>
    <!-- Active Store View -->
    <!-- Store Hero Banner -->
    <section class="store-hero-header">
        <div class="container">
            <?php if (!empty($store['logo_path']) && file_exists($store['logo_path'])): ?>
                <img src="<?php echo htmlspecialchars($store['logo_path']); ?>" alt="<?php echo htmlspecialchars($store['shop_name']); ?> Logo" style="height: 100px; width: 100px; object-fit: cover; border-radius: 50%; border: 3px solid var(--border-color); background-color: var(--card-bg); margin: 0 auto 15px auto; display: block; box-shadow: var(--shadow-sm);">
            <?php else: ?>
                <?php 
                $store_emoji = '🏪';
                $store_type_lower = isset($store['store_type']) ? strtolower($store['store_type']) : '';
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
                <div class="store-header-logo"><?php echo $store_emoji; ?></div>
            <?php endif; ?>
            <h1 class="store-title"><?php echo htmlspecialchars($store['shop_name']); ?></h1>
            <?php if (!empty($store['shop_description'])): ?>
                <p class="store-desc"><?php echo htmlspecialchars($store['shop_description']); ?></p>
            <?php else: ?>
                <p class="store-desc" style="font-style: italic; opacity: 0.7;">Welcome to our storefront! Browse our active catalog below.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Public Store Catalog Grid -->
    <section style="padding-bottom: 80px;">
        <div class="container">
            <!-- Catalog Toolbar -->
            <div class="directory-actions" style="margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px;">
                <div class="search-bar">
                    <input type="text" id="product-search-input" placeholder="Search products in this store...">
                </div>
                <div style="font-weight: 500; color: var(--text-muted);">
                    Showing <span id="product-count" style="color: var(--accent-gold); font-weight: 700;"><?php echo count($products); ?></span> products
                </div>
            </div>

            <!-- Products Display -->
            <?php if (empty($products)): ?>
                <div class="items-list-container" style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">📦</div>
                    <h3>Catalog Currently Empty</h3>
                    <p>This merchant has not listed any products for sale yet. Check back soon!</p>
                </div>
            <?php else: ?>
                <div class="product-grid" id="product-list-grid">
                    <?php foreach ($products as $product): ?>
                        <?php 
                        // Verify thumbnail exists
                        $thumb = $product['image_path'];
                        if (empty($thumb) || !file_exists($thumb)) {
                            $thumb = 'assets/images/placeholder_product.svg';
                        }
                        ?>
                        <div class="product-card" data-name="<?php echo htmlspecialchars(strtolower($product['name'])); ?>" data-desc="<?php echo htmlspecialchars(strtolower($product['description'])); ?>">
                            <div class="product-img-wrapper">
                                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
                            </div>
                            <div class="product-card-body">
                                <h3 class="product-card-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-card-desc" style="margin-bottom: 8px;"><?php echo htmlspecialchars($product['description']); ?></p>
                                
                                <!-- Specifications Badges -->
                                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; align-items: center;">
                                    <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; <?php 
                                        if (($product['availability'] ?? 'In Stock') === 'In Stock') {
                                            echo 'background-color: rgba(22, 163, 74, 0.08); color: var(--primary-green);';
                                        } elseif (($product['availability'] ?? '') === 'Pre-order') {
                                            echo 'background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold);';
                                        } else {
                                            echo 'background-color: rgba(220, 38, 38, 0.08); color: var(--danger);';
                                        }
                                    ?>"><?php echo htmlspecialchars($product['availability'] ?? 'In Stock'); ?></span>
                                    
                                    <?php if (!empty($product['weight_qty'])): ?>
                                        <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 500; background-color: var(--primary-blue-light); color: var(--primary-blue);">⚖️ <?php echo htmlspecialchars($product['weight_qty']); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($product['product_type'])): ?>
                                        <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 500; background-color: rgba(0,0,0,0.04); color: var(--text-main);"><?php 
                                            $pt = $product['product_type'];
                                            if ($pt === 'Veg') echo '🟢 Veg';
                                            elseif ($pt === 'Non-Veg') echo '🔴 Non-Veg';
                                            elseif ($pt === 'Organic') echo '🌿 Organic';
                                            elseif ($pt === 'Packaged') echo '📦 Packaged';
                                            elseif ($pt === 'Homemade') echo '🏡 Homemade';
                                            else echo htmlspecialchars($pt);
                                        ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($product['shelf_life'])): ?>
                                        <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 500; background-color: rgba(0,0,0,0.04); color: var(--text-muted);" title="Shelf Life">⏳ <?php echo htmlspecialchars($product['shelf_life']); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($product['grade']) && $product['grade'] !== 'No Grade'): ?>
                                        <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold);">⭐ <?php echo htmlspecialchars($product['grade']); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-card-footer">
                                    <span class="product-price">₹<?php echo number_format($product['price'], 2); ?> / <?php echo htmlspecialchars($product['price_unit'] ?? 'kg'); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Javascript for Instant product filtering in client-side -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('product-search-input');
    const productCards = document.querySelectorAll('.product-card');
    const countEl = document.getElementById('product-count');

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            productCards.forEach(card => {
                const name = card.getAttribute('data-name') || '';
                const desc = card.getAttribute('data-desc') || '';

                if (name.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countEl) {
                countEl.textContent = visibleCount;
            }
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>
