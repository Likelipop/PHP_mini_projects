# PHP Programming Course - Lab Assignments

This repository contains the lab assignments and mini-projects completed for the **Web Programming with PHP** course at VNU-HCMUS.

## 👤 Student Information
* **Full Name:** Trần Tiến Đạt
* **Student ID:** 22110039
* **University:** University of Science, VNU-HCM (HCMUS)
* **Instructor:** Mr. Trần Anh Tuấn

---

## 📂 Repository Structure
The repository is organized by individual lab assignments. Each folder contains the source code, assets, and accompanying reports for that specific project.

```text
.
├── php_mini_library/     # Lab01
│   ├── src/              # PHP source code
│   ├── assets/           # CSS, Bootstrap, Images
│   └── README.md         # Detailed info for Lab 01
├── php_mini_concert/     # Lab02
│   ├── src/              # PHP source code
│   ├── assets/           # CSS, Bootstrap, Images
│   ├── api/              # Simulated API endpoints
│   └── README.md         # Detailed info for Lab 02
├── Lab03/                # (Upcoming...)
└── .env.example          # Example environment configuration file
```

---

## 🚀 Projects Overview

### Lab 01: Mini Stationery Store
A lightweight, responsive web application for managing and browsing stationery products. This project demonstrates a functional e-commerce storefront with dynamic filtering and a streamlined checkout process.

**Key Features:**
* **Dynamic Product Catalog:** Displays a curated list of stationery items (pens, notebooks, etc.) with real-time details.
* **Smart Filtering:** * Filter by **Category** (e.g., Writing, Paper Products).
    * Filter by **Stock Availability** to show only items currently in store.
* **Responsive UI:** Built with **Bootstrap 5**, ensuring a seamless experience across desktop, tablet, and mobile devices.
* **Shopping Cart Logic:** Integrated "Add to Cart" functionality that captures customer order details via a secure form.
* **Clean Backend:** Structured using PHP for efficient data handling and logic separation.

### Lab 02: Concert Ticket Booking System
A mini-project simulating a ticket booking flow for musical events and concerts.

**Key Features:**
* **Event & Seating Display:** Lists available events alongside interactive seating zone layouts.
* **Booking Processing:** Handles ticket reservations via POST requests with comprehensive form data handling.
* **Responsive UI:** Styled with Bootstrap and custom CSS for a visually appealing user experience.
* **Data Validation & API Design:** * Strict validation for user inputs (Email formatting, valid ticket quantities, preventing duplicate submissions).
    * Standardizes HTTP status codes (404, 422, 500) for robust API communication.

---

## 🛠 How to Run

To run these projects locally, you can use local server environments like XAMPP, Laragon, or Docker.

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/php-course-labs.git
    ```
2.  **Navigate to a specific lab directory:**
    ```bash
    cd Lab01
    ```
3.  **Environment Setup:**
    * Copy the `.env.example` file and rename it to `.env` (if applicable).
    * Adjust the configuration parameters as needed.
4.  **Start the Server:**
    * Move or symlink the project folder into your `htdocs` (XAMPP) or `www` (Laragon) directory.
    * Open your browser and navigate to `http://localhost/php-course-labs/Lab01`.

---

## 📝 Notes
These assignments are developed for educational purposes to practice core PHP principles, Clean Code architecture, and practical web development concepts.

---
*© 2026 - Trần Tiến Đạt*