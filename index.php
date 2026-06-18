<?php
require_once 'includes/db_config.php';

// If already logged in as a vendor, redirect to dashboard immediately
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['vendor_id'])) {
    header("Location: dashboard.php");
    exit();
}

$page_title = "LocalMart Merchant Portal";
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" style="padding: 100px 0 80px 0; background: radial-gradient(circle at top, rgba(2, 132, 199, 0.05) 0%, transparent 70%);">
    <div class="container">
        <span class="hero-tagline">Empowering Local Stores</span>
        <h1 class="hero-title" style="font-size: 3.2rem; line-height: 1.2;">Digital Storefront &amp; QR Codes. <br><span style="color: var(--accent-gold); font-style: italic;">Made Simple.</span></h1>
        <p class="hero-description" style="font-size: 1.2rem; max-width: 700px; color: var(--text-muted);">
            Create your digital shop catalog, manage your inventory in real-time, and get a custom, printable storefront QR code. Let your customers browse and order in a single scan.
        </p>
        <div class="hero-cta-group">
            <a href="register.php" class="btn btn-primary" style="padding: 16px 36px; font-size: 1.05rem;">Register Your Shop</a>
            <a href="login.php" class="btn btn-secondary" style="padding: 16px 36px; font-size: 1.05rem;">Merchant Sign In</a>
        </div>
    </div>
</section>

<!-- Value Proposition/Features Section -->
<section class="section-wrapper" style="background-color: var(--card-bg); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="section-header">
            <h2>Everything You Need to Go Digital</h2>
            <p>Empower your neighborhood business with easy-to-use digital tools designed specifically for local merchants.</p>
        </div>

        <div class="store-grid" style="margin-top: 20px;">
            <!-- Feature 1: Easy Registration -->
            <div class="store-card" style="padding: 40px 30px; text-align: center; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); background: #ffffff;">
                <div style="font-size: 3rem; margin-bottom: 20px;">🏪</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 12px; color: var(--text-main);">Instant Setup</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">Sign up, describe your business, and launch your storefront immediately without any complex web hosting or setup costs.</p>
            </div>

            <!-- Feature 2: QR Code Generation -->
            <div class="store-card" style="padding: 40px 30px; text-align: center; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); background: #ffffff;">
                <div style="font-size: 3rem; margin-bottom: 20px;">🖨️</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 12px; color: var(--text-main);">Printable QR Code</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">Get a unique, high-resolution QR code dynamically linked to your shop. Print it out and display it on your counter or window.</p>
            </div>

            <!-- Feature 3: Real-time Catalog -->
            <div class="store-card" style="padding: 40px 30px; text-align: center; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); background: #ffffff;">
                <div style="font-size: 3rem; margin-bottom: 20px;">📦</div>
                <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin-bottom: 12px; color: var(--text-main);">Catalog Management</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">Easily add products, edit details, set prices, and upload images. Customers see your updated inventory instantly upon scanning.</p>
            </div>
        </div>
    </div>
</section>

<!-- How it works section -->
<section class="section-wrapper">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Three simple steps to bring your local storefront into the digital age.</p>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <ul class="steps-list">
                <li style="margin-bottom: 35px;">
                    <div class="step-num" style="width: 44px; height: 44px; font-size: 1.2rem; background-color: var(--primary-blue-light); color: var(--primary-blue); border-color: rgba(2, 132, 199, 0.2);">1</div>
                    <div class="step-text" style="padding-top: 4px;">
                        <h4 style="font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 6px;">Register Your Store</h4>
                        <p style="font-size: 1rem; color: var(--text-muted);">Create a free merchant account with your shop name, contact email, and a brief description. Your custom QR code is generated instantly.</p>
                    </div>
                </li>
                <li style="margin-bottom: 35px;">
                    <div class="step-num" style="width: 44px; height: 44px; font-size: 1.2rem; background-color: var(--primary-green-light); color: var(--primary-green); border-color: rgba(22, 163, 74, 0.2);">2</div>
                    <div class="step-text" style="padding-top: 4px;">
                        <h4 style="font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 6px;">List Your Products</h4>
                        <p style="font-size: 1rem; color: var(--text-muted);">Access your private Vendor Dashboard to add items. Input names, descriptions, pricing, and upload product images to build a beautiful digital catalog.</p>
                    </div>
                </li>
                <li>
                    <div class="step-num" style="width: 44px; height: 44px; font-size: 1.2rem; background-color: rgba(184, 144, 71, 0.08); color: var(--accent-gold); border-color: rgba(184, 144, 71, 0.2);">3</div>
                    <div class="step-text" style="padding-top: 4px;">
                        <h4 style="font-size: 1.25rem; font-family: var(--font-heading); margin-bottom: 6px;">Display QR &amp; Take Orders</h4>
                        <p style="font-size: 1rem; color: var(--text-muted);">Download and print your store QR code. Place it on your storefront, menu, or delivery bags. Customers scan it with their phone camera to browse and purchase.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="section-wrapper" style="padding-top: 0;">
    <div class="container">
        <div class="cta-banner" style="background: linear-gradient(135deg, var(--text-main) 0%, #0d1a24 100%); padding: 60px 40px; border-radius: var(--border-radius-lg); text-align: center; color: #ffffff;">
            <div style="max-width: 700px; margin: 0 auto;">
                <h2 style="color: #ffffff; font-size: 2.2rem; margin-bottom: 15px; font-family: var(--font-heading);">Ready to digitize your local store?</h2>
                <p style="color: rgba(255, 255, 255, 0.8); font-size: 1.1rem; margin-bottom: 30px;">
                    Join the LocalMart community today. Create your catalog and get your printable shop signage in minutes. Setup is completely free.
                </p>
                <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                    <a href="register.php" class="btn btn-gold" style="padding: 14px 32px; font-size: 1rem;">Get Started Now</a>
                    <a href="login.php" class="btn btn-secondary" style="padding: 14px 32px; font-size: 1rem; border-color: rgba(255,255,255,0.3); color: #ffffff;">Merchant Login</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>
