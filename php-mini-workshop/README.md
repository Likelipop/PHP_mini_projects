# 📝 Mini Stationery Store

A lightweight, responsive web application for managing and browsing stationery products. This project demonstrates a functional e-commerce storefront with dynamic filtering and a streamlined checkout process.

This project is one of mini projects series in PHP course in VNU-HCMUS.

---

## 🚀 Features

* **Dynamic Product Catalog**: Displays a curated list of stationery items (pens, notebooks, etc.) with real-time details.
* **Smart Filtering**: 
    * Filter by **Category** (e.g., Writing, Paper Products).
    * Filter by **Stock Availability** to show only items currently in store.
* **Responsive UI**: Built with **Bootstrap 5**, ensuring a seamless experience across desktop, tablet, and mobile devices.
* **Shopping Cart Logic**: Integrated "Add to Cart" functionality that captures customer order details via a secure form.
* **Clean Backend**: Structured using PHP for efficient data handling and logic separation.

---

## 🛠️ Tech Stack

* **Frontend**: HTML5, CSS3, **Bootstrap 5**
* **Backend**: **PHP**
* **Data Management**: PHP-based arrays/data structures (scalable for SQL integration)
* **Tools**: developed on VSCode, intergrated with version control (Git and github)

---

## 📂 Project Structure

```text
mini-stationery-store/
│
├── index.php          # Main landing page
├── products.php       # Product catalog with filtering logic
├── css/               # Custom stylesheets
├── includes/          # Reusable components (Header, Footer, Data)
└── README.md          # Project documentation
```

---

## ⚙️ Installation & Setup

1. **Navigate to the workshop directory**:
   ```bash
   cd php-mini-workshop
   ```

2. **Install Composer dependencies**:
   ```bash
   composer install
   ```

3. **Configure the environment **:
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```

4. **Start the PHP development server**:
   Set the server's document root to the `public/` directory:
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Access the application**:
   Open your web browser and go to: `http://localhost:8000`

---

## 📸 Preview

| Feature | Description |
| :--- | :--- |
| **Product List** | View 6+ premium stationery items with titles, brands, and prices. |
| **Filters** | Toggle categories to find exactly what you need. |
| **Order Form** | Simplified input for customer information upon adding items. |

---

## 🏗️ Future Roadmap

- [ ] Transition from PHP arrays to a **PostgreSQL/MySQL** database.
- [ ] Implement a full User Authentication system.
- [ ] Add an Admin Dashboard to manage inventory and view orders.
- [ ] Integrate a payment gateway.

