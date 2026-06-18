<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " | LocalMart" : "LocalMart | Your Local Stores. One Scan Away."; ?></title>
    <!-- CSS Stylings -->
    <link rel="stylesheet" href="assets/css/style.css">
    <?php
    if (isset($custom_styles_vendor)) {
        $theme_color = $custom_styles_vendor['theme_color'] ?? '#0284C7';
        $theme_bg = $custom_styles_vendor['theme_bg'] ?? 'cozy';
        $font_style = $custom_styles_vendor['font_style'] ?? 'outfit';
        
        // Dynamic CSS variables overrides
        ?>
        <style>
        :root {
            <?php if (!empty($theme_color)): ?>
            --primary-blue: <?php echo htmlspecialchars($theme_color); ?>;
            --primary-blue-hover: <?php echo htmlspecialchars($theme_color); ?>dd;
            --primary-blue-light: <?php echo htmlspecialchars($theme_color); ?>14;
            --accent-gold: <?php echo htmlspecialchars($theme_color); ?>;
            <?php endif; ?>
            
            <?php if ($theme_bg === 'dark'): ?>
            --bg-color: #0F172A;
            --card-bg: #1E293B;
            --text-main: #F8FAFC;
            --text-muted: #94A3B8;
            --border-color: #334155;
            --border-color-light: #1E293B;
            <?php elseif ($theme_bg === 'clean'): ?>
            --bg-color: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --border-color-light: #F1F5F9;
            <?php elseif ($theme_bg === 'glass'): ?>
            --bg-color: #CBD5E1;
            --card-bg: rgba(255, 255, 255, 0.75);
            --text-main: #1E293B;
            --text-muted: #475569;
            --border-color: rgba(255, 255, 255, 0.5);
            --border-color-light: rgba(255, 255, 255, 0.3);
            <?php endif; ?>

            <?php if ($font_style === 'inter'): ?>
            --font-heading: 'Inter', sans-serif;
            --font-body: 'Roboto', sans-serif;
            <?php elseif ($font_style === 'lora'): ?>
            --font-heading: 'Lora', serif;
            --font-body: 'Merriweather', serif;
            <?php endif; ?>
        }
        
        <?php if ($theme_bg === 'dark'): ?>
        body {
            background-image: radial-gradient(#334155 0.5px, transparent 0.5px), radial-gradient(#334155 0.5px, #0F172A 0.5px) !important;
            color: #F8FAFC !important;
        }
        .main-header {
            background: rgba(15, 23, 42, 0.85) !important;
            border-color: #334155 !important;
        }
        .nav-link {
            color: #F8FAFC !important;
        }
        .nav-link:hover {
            color: var(--primary-blue) !important;
        }
        input, textarea, select {
            background: #1E293B !important;
            color: #F8FAFC !important;
            border-color: #334155 !important;
        }
        .custom-table th {
            background-color: #0F172A !important;
            color: #F8FAFC !important;
            border-color: #334155 !important;
        }
        .custom-table td {
            color: #F8FAFC !important;
            border-color: #334155 !important;
        }
        .custom-table tr:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .modal-card {
            background-color: #1E293B !important;
            border-color: #334155 !important;
            color: #F8FAFC !important;
        }
        .empty-state {
            background-color: #1E293B !important;
            border-color: #334155 !important;
        }
        .dashboard-sidebar-card, .dashboard-main-area, .items-list-container, .qr-code-display-card {
            border-color: #334155 !important;
        }
        .store-hero-header {
            border-bottom: 1px solid #334155 !important;
        }
        <?php elseif ($theme_bg === 'glass'): ?>
        body {
            background-image: linear-gradient(135deg, #E2E8F0 0%, #CBD5E1 100%) !important;
        }
        .main-header {
            background: rgba(226, 232, 240, 0.8) !important;
        }
        .dashboard-sidebar-card, .dashboard-main-area, .items-list-container, .qr-code-display-card, .product-card, .modal-card, .empty-state {
            background: rgba(255, 255, 255, 0.45) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05) !important;
        }
        <?php elseif ($theme_bg === 'clean'): ?>
        body {
            background-image: none !important;
            background-color: #F8FAFC !important;
        }
        .main-header {
            background: rgba(248, 250, 252, 0.85) !important;
        }
        <?php endif; ?>
        </style>

        <?php if ($font_style === 'inter'): ?>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        <?php elseif ($font_style === 'lora'): ?>
        <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;0,700;1,400&family=Merriweather:wght@300;400;700&display=swap" rel="stylesheet">
        <?php endif; ?>
    <?php
    }
    ?>
</head>
<body>
    <header class="main-header">
        <div class="container nav-wrapper">
            <!-- Brand Logo -->
            <a href="index.php" class="logo-link" style="display: flex; align-items: center; gap: 10px;">
                <img src="assets/images/logo.png" alt="LocalMart Logo" style="height: 52px; width: auto; object-fit: contain; border-radius: 4px;">
            </a>
            
            <!-- Navigation Links -->
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <?php if (isset($_SESSION['vendor_id'])): ?>
                        <li><a href="dashboard.php" class="nav-link">Vendor Dashboard</a></li>
                        <li>
                            <a href="store.php?code=<?php echo htmlspecialchars($_SESSION['vendor_token']); ?>" class="nav-link" style="color: var(--primary-green);">
                                View Shop
                            </a>
                        </li>
                        <li><a href="logout.php" class="btn btn-secondary">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn btn-primary">Vendor Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
