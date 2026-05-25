CREATE DATABASE IF NOT EXISTS softskill_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE softskill_lab;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS diary_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  text TEXT NOT NULL,
  rating TINYINT DEFAULT 0,
  tags VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS peer_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reviewer_id INT NOT NULL,
  reviewed_name VARCHAR(120) NOT NULL,
  project_name VARCHAR(180),
  teamwork TINYINT NOT NULL,
  communication TINYINT NOT NULL,
  creativity TINYINT NOT NULL,
  time_management TINYINT NOT NULL,
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo users. Passwords: admin123 және user123
INSERT INTO users (full_name, username, email, password, role) VALUES
('Администратор', 'admin', 'admin@softskill.local', '$2y$12$Vm.0OH7RuavS6nNqvNRRe.EcFQbIIIQKYQidpu/odwXCrYkLo/xDu', 'admin'),
('Demo User', 'user', 'user@softskill.local', '$2y$12$w9jK71e7NSdcgw3qga23i.QAoe9si7Dnkq72uEWmgLZ8IJl425yEW', 'user')
ON DUPLICATE KEY UPDATE
  full_name = VALUES(full_name),
  email = VALUES(email),
  password = VALUES(password),
  role = VALUES(role);

-- Dashboard modules and tasks
CREATE TABLE IF NOT EXISTS modules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT,
  icon VARCHAR(20) DEFAULT '◈',
  color VARCHAR(30) DEFAULT 'accent',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  module_id INT NULL,
  title VARCHAR(220) NOT NULL,
  description TEXT,
  due_date DATE NULL,
  status ENUM('pending','active','done') NOT NULL DEFAULT 'pending',
  priority ENUM('low','normal','high') NOT NULL DEFAULT 'normal',
  assigned_to INT NULL,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
