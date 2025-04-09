-- Create the database
CREATE DATABASE product_management;

-- Use the created database
USE product_management;

-- Create the categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

-- Create the brands table
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(255) NOT NULL
);

-- Create the products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category INT,
    details TEXT,
    brand INT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    entry_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    original_file_name VARCHAR(255),
    temp_file_name VARCHAR(255),
    last_stock_update date,
    FOREIGN KEY (category) REFERENCES categories(id),
    FOREIGN KEY (brand) REFERENCES brands(id)
);

-- Create the buyers table
CREATE TABLE buyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    buyer_address TEXT,
    buyer_phone_no VARCHAR(15),
    buyer_email VARCHAR(255)
);

-- Create the bills table
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    bill_no VARCHAR(50) NOT NULL,
    buyer_name INT,
    buyer_address TEXT,
    buyer_phone_no VARCHAR(15),
    buyer_email VARCHAR(255),
    product_name INT,
    category INT,
    details TEXT,
    brand INT,
    QTY INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    pending_amount DECIMAL(10, 2),
    payment_status 	enum('Paid', 'Pending'),
    FOREIGN KEY (buyer_name) REFERENCES buyers(id),
    FOREIGN KEY (product_name) REFERENCES products(id)
);

-- Create the user table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_img VARCHAR(255) NULL
);

-- Create the personal table
CREATE TABLE personal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_no VARCHAR(15) NOT NULL
);

-- Create the banking table
CREATE TABLE banking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(100) NOT NULL,
    A_C_no VARCHAR(20) NOT NULL UNIQUE,
    IFSC_code VARCHAR(11) NOT NULL,
    address VARCHAR(255) NOT NULL
);

-- Create the stock update table 
CREATE TABLE stock_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    updated_by VARCHAR(255) NOT NULL,
    update_date DATE NOT NULL,
    added_stock INT NOT NULL,
    total_stock INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
