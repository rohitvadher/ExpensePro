CREATE DATABASE IF NOT EXISTS expensepro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE expensepro;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    currency VARCHAR(10) DEFAULT 'INR',
    monthly_budget DECIMAL(12,2) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_remember_token (remember_token)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_type (user_id, type),
    INDEX idx_created_at (created_at),
    UNIQUE KEY uq_user_name_type (user_id, name, type)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT DEFAULT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_user_date (user_id, date),
    INDEX idx_user_type_date (user_id, type, date),
    INDEX idx_user_category (user_id, category_id),
    INDEX idx_date (date)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    period ENUM('weekly', 'monthly', 'yearly') DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_period (user_id, start_date, end_date),
    INDEX idx_user_period_category (user_id, period, category_id),
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE quick_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    category_id INT DEFAULT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_usage (user_id, usage_count DESC),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE csv_imports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    row_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    errors_log JSON DEFAULT NULL,
    status ENUM('completed', 'partial', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_imports (user_id, created_at DESC)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_ip_time (email, ip, attempted_at),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_key VARCHAR(191) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key_time (rate_key, attempted_at),
    INDEX idx_attempted_at (attempted_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('over_budget', 'info', 'login') NOT NULL DEFAULT 'info',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read, created_at DESC),
    INDEX idx_user_type_title_created (user_id, type, title, created_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'avatar');
SET @s := IF(@c > 0, 'ALTER TABLE users DROP COLUMN avatar', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'image');
SET @s := IF(@c > 0, 'ALTER TABLE categories DROP COLUMN image', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'location_lat');
SET @s := IF(@c > 0, 'ALTER TABLE transactions DROP COLUMN location_lat', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'location_lng');
SET @s := IF(@c > 0, 'ALTER TABLE transactions DROP COLUMN location_lng', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND COLUMN_NAME = 'location_name');
SET @s := IF(@c > 0, 'ALTER TABLE transactions DROP COLUMN location_name', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


CREATE TABLE IF NOT EXISTS budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL,
    period ENUM('weekly', 'monthly', 'yearly') DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_period (user_id, start_date, end_date),
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quick_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    category_id INT DEFAULT NULL,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(12,2) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_usage (user_id, usage_count DESC),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS csv_imports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    row_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    errors_log JSON DEFAULT NULL,
    status ENUM('completed', 'partial', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_imports (user_id, created_at DESC)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('over_budget', 'info', 'login') NOT NULL DEFAULT 'info',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read, created_at DESC)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_ip_time (email, ip, attempted_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'budgets' AND COLUMN_NAME = 'category_id' AND IS_NULLABLE = 'NO');
SET @s := IF(@c > 0, 'ALTER TABLE budgets MODIFY COLUMN category_id INT DEFAULT NULL', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rate_key VARCHAR(191) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key_time (rate_key, attempted_at)
) ENGINE=InnoDB COLLATE=utf8mb4_unicode_ci;


SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND INDEX_NAME = 'idx_user_type_date');
SET @s := IF(@c = 0, 'ALTER TABLE transactions ADD INDEX idx_user_type_date (user_id, type, date)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;


SET @c := (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transactions' AND INDEX_NAME = 'idx_date');
SET @s := IF(@c = 0, 'ALTER TABLE transactions ADD INDEX idx_date (date)', 'DO 0');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
