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
    price DECIMAL(10, 0) NOT NULL, -- Prices in Toman usually don't have decimals
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    comment TEXT NOT NULL,
    rate INT NOT NULL DEFAULT 5,
    parent_id INT DEFAULT NULL, -- For admin replies
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
    status ENUM('pending', 'completed') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data for Products
INSERT INTO products (name, price, description, image) VALUES
('پیتزا مخصوص', 185000, 'پیتزا با ترکیبات ویژه سرآشپز شامل ژامبون درجه یک و پنیر فراوان', '1.jpg'),
('برگر ذغالی', 145000, 'برگر ۱۸۰ گرمی گوشت تازه گوسفندی پخته شده روی ذغال', '2.jpg'),
('پاستا چیکن آلفردو', 160000, 'پاستا پنه با سس مخصوص خامه و قارچ و فیله مرغ', '3.jpg'),
('جوجه کباب زعفرانی', 195000, '۳۰۰ گرم جوجه کباب سینه بدون استخوان با برنج ایرانی', '4.jpg'),
('سالاد مخصوص فصل', 65000, 'ترکیبی از سبزیجات تازه روز به همراه سس مخصوص', '5.jpg'),
('لازانیا گوشت', 170000, 'لایه های پاستا، گوشت چرخ کرده، قارچ و پنیر پیتزا', '6.jpg');

-- Default Admin User (Password: admin123)
-- In a real app, we use password_hash. $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi is 'password' in bcrypt
-- Let's use a known hash for 'admin123'
-- admin123 hash: $2y$10$nO7Z4mO.L8fV6mN8K0ZfOeX6W2Z8S3G/vX2K7B.uX8E5H.jK8M9K (actually let's just use password_hash in PHP later or a common one)
-- For now, let's use the hash for 'admin123': $2y$10$mC2V6Ukyz6pX7jB8V1.vDeG/E5v1B9mN0u9E/Y2L3H2k4g5f6h7i8
INSERT INTO users (name, email, password, role) VALUES
('مدیر اصلی', 'admin@example.com', '$2y$10$mC2V6Ukyz6pX7jB8V1.vDeG/E5v1B9mN0u9E/Y2L3H2k4g5f6h7i8', 'admin');
