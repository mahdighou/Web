-- Create Database
CREATE DATABASE IF NOT EXISTS restaurant_db;
USE restaurant_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 0) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    comment TEXT NOT NULL,
    rate INT NOT NULL DEFAULT 5,
    parent_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages (Support Chat) Table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations Table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guest_count INT NOT NULL,
    status ENUM('pending', 'approved', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data for Products
INSERT INTO products (name, price, description, image, stock) VALUES
('پیتزا مخصوص', 185000, 'پیتزا با ترکیبات ویژه سرآشپز شامل ژامبون درجه یک و پنیر فراوان', '1.jpg', 20),
('برگر ذغالی', 145000, 'برگر ۱۸۰ گرمی گوشت تازه گوسفندی پخته شده روی ذغال', '2.jpg', 15),
('پاستا چیکن آلفردو', 160000, 'پاستا پنه با سس مخصوص خامه و قارچ و فیله مرغ', '3.jpg', 12),
('جوجه کباب زعفرانی', 195000, '۳۰۰ گرم جوجه کباب سینه بدون استخوان با برنج ایرانی', '4.jpg', 25),
('سالاد مخصوص فصل', 65000, 'ترکیبی از سبزیجات تازه روز به همراه سس مخصوص', '5.jpg', 30),
('لازانیا گوشت', 170000, 'لایه های پاستا، گوشت چرخ کرده، قارچ و پنیر پیتزا', '6.jpg', 10);

-- Default Admin User (Password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('مدیر اصلی', 'admin@example.com', '$2y$10$mC2V6Ukyz6pX7jB8V1.vDeG/E5v1B9mN0u9E/Y2L3H2k4g5f6h7i8', 'admin');
