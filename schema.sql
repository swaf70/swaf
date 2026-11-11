-- Database schema for Eng. Mohammed Al-Sawaf Portfolio & Security Hub
-- MySQL 8.0+

-- Create database
CREATE DATABASE IF NOT EXISTS alsawaf_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alsawaf_db;

-- --------------------------------------------------------
-- Table: users
-- Stores admin authentication data
-- --------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: Admin123! hashed with bcrypt)
-- Username: admin, Email: admin@alsawaf.dev
INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@alsawaf.dev', '$2y$10$0eZ258NuviYTQ3GjMfXQ6.VZYLn9/dPj2aqMozaaGWymUF3f2669K', 'admin');

-- --------------------------------------------------------
-- Table: projects
-- Stores portfolio projects
-- --------------------------------------------------------
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    category ENUM('Web Development', 'Network Architecture', 'Cybersecurity', 'Full-Stack') NOT NULL,
    description TEXT,
    tech_stack VARCHAR(255) NULL, -- comma-separated: PHP, MySQL, Linux, Docker, Python
    image_url VARCHAR(500) NULL, -- path to thumbnail image
    project_url VARCHAR(500) NULL, -- live demo or GitHub URL
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert dummy projects
INSERT INTO projects (title, category, description, tech_stack, image_url, project_url) VALUES
('Secure Web Portal', 'Web Development', 'A full-featured web application with authentication and role-based access control.', 'PHP, MySQL, Linux, Docker, HTML5, CSS3', 'project1.jpg', 'https://github.com/alsawaf/secure-portal'),
('Network Infrastructure Monitor', 'Network Architecture', 'Real-time network monitoring and traffic analysis dashboard for enterprise networks.', 'PHP, MySQL, Python, Grafana, Docker', 'project2.jpg', 'https://github.com/alsawaf/network-monitor'),
('Vulnerability Assessment Tool', 'Cybersecurity', 'Automated security scanning tool for detecting common web vulnerabilities and misconfigurations.', 'Python, Flask, MySQL, OpenCV, Bootstrap', 'project3.jpg', 'https://github.com/alsawaf/vuln-assessment'),
('E-commerce Platform', 'Full-Stack', 'Complete online store with payment gateway integration, admin panel, and inventory management.', 'PHP, MySQL, Stripe API, React, Bootstrap', 'project4.jpg', 'https://github.com/alsawaf/ecommerce-platform');

-- --------------------------------------------------------
-- Table: services
-- Stores services offered by Eng. Mohammed Al-Sawaf
-- --------------------------------------------------------
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    status ENUM('Available', 'Busy', 'Completed') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert dummy services
INSERT INTO services (service_name, description, price, status) VALUES
('Web Application Penetration Testing', 'Comprehensive security assessment and vulnerability identification for web applications', 50000.00, 'Available'),
('Network Infrastructure Setup', 'Design and deployment of secure network infrastructure for businesses in Yemen', 35000.00, 'Available'),
('Full-Stack Web Development', 'Custom web application development from concept to deployment with secure architecture', 45000.00, 'Available'),
('Security Audit & Compliance', 'PCI-DSS, ISO27001, and custom security audits for organizational systems', 60000.00, 'Busy');

-- --------------------------------------------------------
-- Table: messages
-- Stores contact form inquiries
-- --------------------------------------------------------
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_name VARCHAR(100) NOT NULL,
    sender_email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert dummy messages
INSERT INTO messages (sender_name, sender_email, subject, message, is_read) VALUES
('Ahmed Hassan', 'ahmed@techco.com.ye', 'Inquiry about security audit', 'Hello Eng. Mohammed, I would like to request a security audit for my company\'s web applications. Please contact me to discuss details.', 0),
('Fatima Saleh', 'fatima@startup.ye', 'Full-stack development project', 'Hi, I am interested in your full-stack development services for my startup. Waiting for your response.', 1);

-- --------------------------------------------------------
-- View: unread_messages_count
-- Helper view for admin dashboard telemetry
-- --------------------------------------------------------
CREATE VIEW unread_messages_count AS
SELECT COUNT(*) AS total_unread FROM messages WHERE is_read = 0;