<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo"/>
</p>

<h1 align="center">🦅 Rajawali Stok — POS & Inventory Management System</h1>

<p align="center">
  A desktop point-of-sale and inventory management system built for <strong>Toko Rajawali</strong>, a multi-category retail store in Tondano, North Sulawesi, selling computers &amp; electronics, stationery (ATK), cakes &amp; pastries, snacks, beverages, and packaging supplies.
  <br/>
  <em>Packaged as a native Windows desktop application using NativePHP + Electron.</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?logo=laravel" alt="Laravel"/>
  <img src="https://img.shields.io/badge/PHP-8.3-blue?logo=php" alt="PHP"/>
  <img src="https://img.shields.io/badge/Alpine.js-3-8BC0D0?logo=alpinedotjs" alt="Alpine.js"/>
  <img src="https://img.shields.io/badge/Tailwind%20CSS-4-38BDF8?logo=tailwindcss" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/NativePHP-Desktop-6C47FF" alt="NativePHP"/>
  <img src="https://img.shields.io/badge/Electron-Windows%20Build-2B2E3A?logo=electron" alt="Electron"/>
  <img src="https://img.shields.io/badge/SQLite-Local%20DB-003B57?logo=sqlite" alt="SQLite"/>
</p>

---

## 📖 About This Project

**Rajawali Stok** is a point-of-sale (POS) and inventory management system built for **Toko Rajawali**, a retail store in Tondano, North Sulawesi that sells a wide mix of products — from computer accessories and electronics to stationery, cakes & pastries, snacks, drinks, and packaging materials.

The system was built to replace manual, paper-based stock tracking and cash-register sales with a single, streamlined application that keeps stock counts, sales, and reporting all in one place. It runs as a native desktop app (via NativePHP + Electron) so it can be installed and used directly on the store's computer without needing a separate server, PHP, or browser setup.

The system supports three distinct roles:

- **Admin** — full access to POS, inventory, and reporting.
- **Owner** — access to dashboards, reports, and business oversight.
- **Dapur (Kitchen)** — scoped access limited to bakery-related categories (finished cakes/pastries and raw baking ingredients).

---

## ✨ Features

### 🛒 Point of Sale (Kasir)
- Fast, click-to-add product grid organized by category tabs (ATK, Electronics, Cake & Pastry, Snacks, Beverages, Packaging)
- Live product search
- Real-time cart with quantity controls and running total
- Multiple payment methods: Cash (with change calculation), QRIS, and Bank Transfer
- Stock-aware checkout — prevents overselling and flags low/out-of-stock items
- Thermal-style (80mm) receipt printing, rendered as a print-ready HTML document for full compatibility with the desktop app's print preview

### 📦 Inventory Management
- Stock-in (barang masuk) and stock-out (barang keluar) tracking
- Product catalog management across multiple categories
- Low-stock warnings directly in the POS and catalog views
- Role-scoped category access (e.g. kitchen staff only see bakery-related stock)

### 📊 Dashboard & Reporting
- Real-time dashboard defaulting to today's activity, with historical filters
- Excel export with multiple color-coded sheets (via PhpSpreadsheet)
- PDF report generation (via DomPDF)

### 🔐 Security & Reliability
- Role-based access control with Laravel Gates
- CSRF protection, mass-assignment protection, and hardened middleware
- Eager-loaded queries to avoid N+1 performance issues
- Foreign-key indexed migrations for data integrity

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel (PHP) |
| Frontend | Blade Templating, Alpine.js, Tailwind CSS |
| UI Feedback | SweetAlert2, Chart.js |
| Desktop Runtime | NativePHP + Electron |
| Local Database (Desktop) | SQLite |
| Reporting | PhpSpreadsheet (Excel), DomPDF (PDF) |

---

## 🚀 Installation & Setup

### Prerequisites
- PHP >= 8.3
- Composer
- Node.js & NPM
- (For desktop builds) Windows with Developer Mode enabled

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/mystrygrdn/tokorajawali-pos-inventory.git
   cd tokorajawali-pos-inventory
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up the database**
   ```bash
   php artisan migrate --seed
   ```

5. **Run in the browser (recommended for development)**
   ```bash
   npm run dev
   php artisan serve
   ```

6. **Run as a native desktop app**
   ```bash
   php artisan native:install
   php artisan native:serve
   ```

7. **Build a distributable Windows installer**
   ```bash
   php artisan native:build win
   ```
   The finished installer will be generated inside `nativephp/electron/dist/`.

---

## 📄 License

This project is built for internal use by Toko Rajawali. Please contact the repository owner before reusing or redistributing.
