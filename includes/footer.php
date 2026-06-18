    </main>
    
    <footer class="main-footer">
        <div class="container footer-grid">
            <!-- Col 1: Brand -->
            <div class="footer-col">
                <h3 style="font-family: var(--font-heading); color: #ffffff; font-size: 1.5rem; margin-bottom: 15px;">LocalMart</h3>
                <p>Empowering neighborhood stores by bringing digital convenience directly to local doorsteps. Scan. Explore. Shop local.</p>
                <p style="font-size: 0.85rem; color: var(--accent-gold); font-weight: 500;">
                    Your Local Stores. One Scan Away.
                </p>
            </div>
            
            <!-- Col 2: Navigation Links -->
            <div class="footer-col">
                <h3>Explore</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Browse Stores</a></li>
                    <li><a href="admin.php">Developer Dashboard</a></li>
                </ul>
            </div>
            
            <!-- Col 3: Portal Access -->
            <div class="footer-col">
                <h3>Merchant Portal</h3>
                <ul class="footer-links">
                    <?php if (isset($_SESSION['vendor_id'])): ?>
                        <li><a href="dashboard.php">Vendor Panel</a></li>
                        <li><a href="logout.php">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Vendor Sign In</a></li>
                        <li><a href="register.php">Register Your Shop</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="container footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> LocalMart. All rights reserved. Built for local communities.</p>
        </div>
    </footer>
</body>
</html>
