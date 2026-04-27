# Lab 02 - Concert Ticket Booking System

This project is a web-based Concert Ticket Booking application developed as part of the **Web Programming with PHP** course. It implements a custom Model-View-Controller (MVC) architecture, utilizes Composer for dependency management, and handles environment variables securely.

---

## 🚀 Project Overview

The application simulates a ticketing system where users can view upcoming concerts, check seating availability, and book tickets. The backend is built entirely in raw PHP, utilizing a Front Controller pattern to route requests and manage data flow efficiently.

### ✨ Key Features
* **MVC Architecture:** Clean separation of concerns between logic (`src/Controllers`), data (`src/Data`), and presentation (`views`).
* **Environment Configuration:** Uses `vlucas/phpdotenv` to manage configuration securely without hardcoding sensitive data.
* **REST-like API & Routing:** Handles form submissions and API requests via dedicated controllers (`BookingController`).
* **Logging System:** Captures application events and errors safely into the `storage/logs/` directory.
* **Automated API Testing:** Includes a `test_api.sh` script to quickly verify API endpoint responses via the command line.

---

## 📂 Directory Structure Explained

Based on the implemented structure, here is how the application is organized:

* **`public/`**: Contains the entry point of the application (`index.php`). All requests are funneled through this Front Controller.
* **`src/`**: The core application logic.
  * **`Controllers/`**: Handles the business logic for pages and actions (`HomeController`, `ConcertController`, `BookingController`).
  * **`Data/`**: Contains `concerts.php`, acting as our mock database (array-based storage).
  * **`Support/`**: Utility classes (`Env.php` for loading `.env` files, `Response.php` for standardizing HTTP responses).
* **`views/`**: The frontend PHP templates (Home, Concert lists, Booking modals, and Success pages).
* **`config/`**: Contains configuration files like `app.php`.
* **`storage/logs/`**: Directory dedicated to storing application and error logs securely.
* **`vendor/`**: Composer dependencies (e.g., `phpdotenv`, Symfony polyfills).

---

## 🛠 Setup & Installation

Follow these steps to run the Concert Ticket Booking System on your local machine:

1. **Clone the repository and navigate to Lab 02:**
   ```bash
   cd path/to/Lab02
   ```

2. **Install Dependencies:**
   Ensure you have [Composer](https://getcomposer.org/) installed, then run:
   ```bash
   composer install
   ```

3. **Environment Setup:**
   * Create a `.env` file in the root of the `Lab02` directory.
   * Add necessary environment variables (e.g., APP_ENV, BASE_URL) as defined in your configuration.

4. **Start the PHP Development Server:**
   Since the entry point is in the `public` folder, you need to point the server there:
   ```bash
   php -S localhost:8000 -t public
   ```

5. **Access the Application:**
   Open your web browser and navigate to: `http://localhost:8000`

---

## 🧪 Testing

To quickly test the booking API endpoints without using the frontend form, you can execute the provided shell script:

```bash
chmod +x test_api.sh
./test_api.sh
```

---
*Developed for Lab 02 - PHP Programming Course*