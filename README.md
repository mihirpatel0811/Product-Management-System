# Product Management System (PMS)

## 🎯 Overview

The **Product Management System (PMS)** is a comprehensive, web-based application designed to centralize and streamline business operations related to product inventory, categories, brands, buyers, and billing. Developed to enhance operational efficiency and improve the management of commercial data, the system provides a secure, user-friendly platform for tracking all product-related activities from entry to sale.

---

## ✨ Key Features

The PMS is structured around several interconnected modules to provide complete control over product lifecycle management:

### 1. Product & Inventory Control
* **Product Management (CRUD):** Full functionality to **View, Add, Edit, and Delete** product records.
* **Detailed Product Information:** Records include: `product_name`, `category`, `brand`, `price`, `stock`, `details`, and `entry_date`.
* **Stock Tracking:** Maintains accurate, real-time levels of product inventory.
* **Secure File Uploads:** Supports uploading files or images (`.jpg`, `.pdf`, etc.) for products, ensuring file name conflict resolution by storing both the `Orignal_file_name` and a unique `temp_file_name`.

### 2. Core Data Management
* **Category Management:** Features to efficiently manage (`Add, Modify, Delete`) a list of product categories (e.g., Mobile, Laptop, Tablet, Watch).
* **Brand Management:** Functionality to manage different product brands (e.g., Samsung, boAt, Titan, Lenovo).
* **Buyer Management:** Tracks all buyer information, including `Buyer Name`, `Address`, `Phone No.`, and `Email ID`.

### 3. Billing and Transactions
* **Bill Recording:** Records detailed billing information including `date`, `bill_no`, `buyer_name`, `buyer_address`, product details, and transaction specifics.
* **Automated Transactions:** Designed to simplify the billing process by managing product quantities and recording sales automatically.

### 4. User and Security
* **User Authentication:** Secure **Registration** and **Login** system.
* **User Profile:** Allows users to view and manage their profile, including **Username** and **Email ID**, change passwords, and upload a display picture.
* **Secure Logout:** Essential functionality for securely terminating user sessions.
* **User-Specific Data Segregation:** The system is designed to show data based on the logged-in user (e.g., "admin" sees only admin data, "admin2" sees only admin2 data).

---

## 🛠 Technology Stack

The Product Management System is built using a modern and reliable web development stack:

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend** | **PHP** | Server-side logic, handling requests, and processing data. |
| **Database** | **MySQL (SQL)** | Robust relational database for secure and efficient data storage. |
| **Frontend** | **HTML, CSS, JavaScript** | Intuitive and responsive user interface and client-side interactivity. |

---

## 📂 Database Structure

The system operates on a main relational database, typically named `product_management` or `pms-system`, which consists of the following key tables:

| Table Name | Purpose | Key Fields |
| :--- | :--- | :--- |
| **products** | Detailed product information and inventory. | `id`, `product_name`, `category`, `brand`, `price`, `stock`, `entry_date`, `Orignal_file_name`, `temp_file_name` |
| **categories** | Maintains the list of available product categories. | `id`, `category_name` |
| **brands** | Stores information about associated brands. | `id`, `brand_name` |
| **buyers** | Records buyer contact and address details. | `id`, `buyer_name`, `buyer_address`, `phone_no`, `email_id` |
| **bills** | Records transactional and billing information. | `id`, `date`, `bill_no`, `buyer_name`, `buyer_address`, *product details* |
| **users** | Stores credentials for user authentication. | `username`, `email id`, `password` |
| **personal** | (Auxiliary) Stores personal user information. | *User-related personal fields* |
| **banking** | (Auxiliary) Stores user banking details for transaction purposes. | *User-related banking fields* |

### Security Measures in Database Design
* **Password Hashing:** Passwords for the `users` table are securely **hashed (e.g., using bcrypt)** and never stored as plain text.
* **Input Validation:** User inputs are **sanitized and validated** to prevent common web security threats like SQL injection and XSS attacks.
* **Referential Integrity:** Foreign key relationships between tables ensure data consistency.

---

## 📊 Design and Diagrams

The project documentation includes a thorough analysis and design, detailed through various diagrams:

* **Database Diagram**
* **Flowchart Diagram**
* **Sequence Diagram**
* **Data Flow Diagram (DFD)**
* **Activity Diagram**
* **Use Case Diagram**
* **Class Diagram**

This structured approach ensures the system is maintainable, scalable, and built on sound architectural principles.
