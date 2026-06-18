# LocalMart - Digital Storefront & QR Code Catalog

LocalMart is a premium, lightweight, and modern digital catalog solution designed for neighborhood businesses and local merchants. Vendors can instantly register their shops, upload logos, customize theme aesthetics, and add inventory items. The system automatically generates a unique storefront QR code that customers can scan to browse the merchant's live price list and specifications on their mobile devices.

Additionally, LocalMart exposes CORS-enabled, mobile-friendly JSON API endpoints that allow direct integration with external platforms (such as Flutter or React Native mobile applications).

---

## 🚀 Key Features

* **Merchant Registration & Login**: Impressive register screen with drag-and-drop logo upload, live image previews, password strength meters, and store category selections.
* **Branding & Theme Customization**: Vendors can configure custom accent colors, background themes (Cozy Cream, Clean Light, Sleek Charcoal, and Glassmorphism), and typography styles dynamically.
* **Inventory Catalog Management**: CRUD controls for listing products. Includes pricing, descriptions, product photos, and detailed specifications:
  - **Availability** (*In Stock, Out of Stock, Pre-order*)
  - **Weight / Quantity** (e.g. *500g, 1 kg, 6 pcs*)
  - **Product Type** (Veg, Non-Veg, Organic, Packaged, Homemade, Other)
  - **Shelf Life** (e.g. *3 Days, 6 Months*)
  - **Grade** (Premium, Grade A, Standard)
  - **Price Unit** (per kg, per piece, per packet, per litre, per dozen, per gram)
* **Printable QR Signage Poster**: An elegant, gold-bordered storefront poster containing the shop name, uploaded logo, and scanner QR code.
* **Public Customer Storefront**: Clean, mobile-friendly public catalog page where customers can search and browse products with price-unit labels (e.g., `₹120 / kg` or `₹50 / piece`).
* **Flutter-Ready REST APIs**: CORS-enabled endpoints supporting query parameters for instant mobile app rendering.

---

## 🛠️ Technology Stack

* **Backend**: PHP (PDO)
* **Database**: MySQL (MariaDB)
* **Frontend**: HTML5, Vanilla CSS3 (custom CSS variables), Vanilla JavaScript
* **API Serialization**: JSON

---

## 📂 Project Directory Structure

```text
localmart/
├── api/
│   ├── products.php      # Exposes product catalog (supports vendor_id/store_id/shop_id filtering)
│   ├── store.php         # Exposes single store details and its products by QR token or ID
│   └── stores.php        # Exposes all registered stores
├── assets/
│   ├── css/
│   │   └── style.css     # Core premium stylesheet and theme design tokens
│   ├── images/           # Emojis, graphics, and uploads folder
│   │   └── uploads/      # Merchant logos and product photo uploads
│   └── js/               # Helper JavaScript logic
├── includes/
│   ├── db_config.php     # Auto-creates database, tables, and applies schema migrations
│   ├── header.php        # Global page header and dynamic styling engine
│   └── footer.php        # Global page footer
├── admin.php             # Developer dashboard
├── dashboard.php         # Merchant management panel (Branding, inventory CRUD)
├── index.php             # LocalMart landing page
├── login.php             # Merchant login portal
├── register.php          # Merchant registration page (with file upload & strength indicators)
├── store.php             # Public-facing storefront (customer view)
└── README.md             # Project documentation
```

---

## 📋 Database Installation

LocalMart is built to be self-installing:
1. Open your MySQL server (e.g., via XAMPP Control Panel).
2. Open `includes/db_config.php` and configure your database host, user, password, and database name (defaults to `localhost`, `root`, blank password, and database `localmart`).
3. Simply load the website in your browser (e.g., `http://localhost/localmart/`). The script will automatically create the database `localmart` and set up the `vendors`, `items`, and `customers` tables, running migrations dynamically.

---

## 🔌 API Endpoints for Mobile Apps (Flutter/React Native)

All APIs return CORS headers (`Access-Control-Allow-Origin: *`) and respond in JSON format.

### 1. Fetch All Registered Shops
* **Endpoint**: `GET /api/stores.php`
* **Response Payload**:
  ```json
  {
    "status": true,
    "stores": [
      {
        "id": 1,
        "shop_name": "Greenland Grocery Store",
        "owner_name": "Rakesh Sharma",
        "email": "owner@store.com",
        "shop_description": "Fresh organic fruits & vegetables.",
        "address": "12, Green Avenue, Bhavnagar, Gujarat",
        "store_type": "Grocery",
        "contact_number": "9876543210",
        "qr_code_token": "shop_68d0e5124b89",
        "logo_url": "http://localhost/localmart/assets/images/uploads/logo_1.png",
        "theme_color": "#16A34A",
        "theme_bg": "clean",
        "font_style": "inter"
      }
    ]
  }
  ```

### 2. Fetch Single Store & Products Catalog (via QR Code or ID)
* **Endpoint**: `GET /api/store.php`
* **Query Parameters**:
  - `?code=<QR_CODE_TOKEN>` (e.g., extracted from scanned URL: `shop_68d0e5124b89`) OR
  - `?id=<STORE_ID>` (e.g., `1`)
* **Response Payload**:
  ```json
  {
    "status": true,
    "store": {
      "id": 1,
      "shop_name": "Greenland Grocery Store",
      ...
    },
    "products": [
      {
        "id": 12,
        "vendor_id": 1,
        "name": "Fresh Capsicum",
        "description": "Crisp green bell pepper.",
        "price": "108.00",
        "price_unit": "kg",
        "image_url": "http://localhost/localmart/assets/images/uploads/item_17817.png",
        "availability": "In Stock",
        "weight_qty": "6kg",
        "product_type": "Veg",
        "shelf_life": "2 days",
        "grade": "Grade A"
      }
    ]
  }
  ```

### 3. Fetch Products List (Filter on Server)
* **Endpoint**: `GET /api/products.php`
* **Query Parameters** (Optional):
  - `?vendor_id=<ID>` OR `?store_id=<ID>` OR `?shop_id=<ID>`
* **Description**: Returns products. If a store/vendor parameter is supplied, it filters and returns only products belonging to that shop.

---

## ⚙️ Development Tunneling (Localtunnel)

If you are running the API on your local host (XAMPP) and need to connect it to a mobile device over the internet:
1. Start your local tunnel (e.g., `lt --port 80 --subdomain localmart`).
2. Localtunnel inserts a reminder screen on first visit. To bypass this screen programmatically in your Flutter app, add this header to your HTTP requests:
   - **Header Key**: `Bypass-Tunnel-Reminder`
   - **Header Value**: `true`
