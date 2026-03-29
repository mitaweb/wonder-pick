-- Wonder Pickleball - Database Schema v2
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI

CREATE DATABASE IF NOT EXISTS wonder_pickleball CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wonder_pickleball;

-- Bảng khách hàng
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    sessions INT NOT NULL DEFAULT 0,
    max_sessions INT NOT NULL DEFAULT 13,
    pkg VARCHAR(20) NOT NULL DEFAULT '10+3',
    expires_at DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- Bảng check-in
CREATE TABLE IF NOT EXISTS checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    sessions_before INT NOT NULL,
    sessions_after INT NOT NULL,
    checked_in_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    note VARCHAR(255) DEFAULT NULL,
    INDEX idx_phone (phone),
    INDEX idx_date (checked_in_at)
) ENGINE=InnoDB;

-- Bảng lịch sử cộng gói
CREATE TABLE IF NOT EXISTS session_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    pkg VARCHAR(20) NOT NULL,
    sessions_added INT NOT NULL,
    added_by VARCHAR(50) DEFAULT 'admin',
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    note VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- Bảng đơn hàng (dùng cho Sepay payment)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    pkg_type VARCHAR(30) NOT NULL,          -- 'pkg_10', 'pkg_30', 'single', 'kids'
    sessions_to_add INT NOT NULL DEFAULT 0,
    amount DECIMAL(10,2) NOT NULL,
    kids_count INT NOT NULL DEFAULT 0,
    payment_status ENUM('Unpaid','Paid','Cancelled') NOT NULL DEFAULT 'Unpaid',
    order_code VARCHAR(30) NOT NULL UNIQUE, -- WP + id, dùng nhận diện trong nội dung CK
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME DEFAULT NULL,
    INDEX idx_phone (phone),
    INDEX idx_code (order_code),
    INDEX idx_status (payment_status)
) ENGINE=InnoDB;

-- Bảng giao dịch nhận từ Sepay webhook
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sepay_id INT DEFAULT NULL,
    gateway VARCHAR(100),
    transaction_date DATETIME,
    account_number VARCHAR(100),
    amount_in DECIMAL(20,2) DEFAULT 0,
    amount_out DECIMAL(20,2) DEFAULT 0,
    transaction_content TEXT,
    reference_number VARCHAR(255),
    order_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sepay_id (sepay_id),
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- ============================================================
-- Migration v3: Email, mật khẩu, quên mật khẩu, SMTP settings
-- Chạy các lệnh ALTER này nếu đã có DB cũ
-- Hoặc chạy toàn bộ file nếu tạo DB mới
-- ============================================================

-- Thêm email + password vào customers
ALTER TABLE customers
  ADD COLUMN IF NOT EXISTS email VARCHAR(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) DEFAULT NULL,
  ADD UNIQUE INDEX IF NOT EXISTS idx_email (email);

-- Bảng token đặt lại mật khẩu
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- Bảng cấu hình ứng dụng (key-value, lưu SMTP và các setting khác)
CREATE TABLE IF NOT EXISTS app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Giá trị mặc định cho SMTP
INSERT IGNORE INTO app_settings (setting_key, setting_value) VALUES
  ('smtp_host',     'smtp.gmail.com'),
  ('smtp_port',     '587'),
  ('smtp_user',     ''),
  ('smtp_pass',     ''),
  ('smtp_from_name','Wonder Pickleball'),
  ('smtp_enabled',  '0');

-- ============================================================
-- Migration v4: Check-in nhiều người & email thông báo
-- ============================================================

-- Thêm cột people_count vào checkins (số người vào chơi mỗi lần check-in)
ALTER TABLE checkins
  ADD COLUMN IF NOT EXISTS people_count INT NOT NULL DEFAULT 1;
