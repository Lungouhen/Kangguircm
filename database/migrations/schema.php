<?php

declare(strict_types=1);

return [
    'users' => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role_id INT,
            email_verified_at TIMESTAMP NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'roles' => "
        CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            permissions JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'cms_posts' => "
        CREATE TABLE IF NOT EXISTS cms_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT,
            excerpt TEXT,
            featured_image VARCHAR(500),
            author_id INT NOT NULL,
            category_id INT,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_published_at (published_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'cms_pages' => "
        CREATE TABLE IF NOT EXISTS cms_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT,
            parent_id INT NULL,
            template VARCHAR(100) DEFAULT 'default',
            status ENUM('draft', 'published') DEFAULT 'draft',
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES cms_pages(id) ON DELETE SET NULL,
            INDEX idx_slug (slug),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'cms_media' => "
        CREATE TABLE IF NOT EXISTS cms_media (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            size INT NOT NULL,
            path VARCHAR(500) NOT NULL,
            alt_text VARCHAR(255),
            uploaded_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_mime_type (mime_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'cms_categories' => "
        CREATE TABLE IF NOT EXISTS cms_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            parent_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent_id) REFERENCES cms_categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_subscribers' => "
        CREATE TABLE IF NOT EXISTS email_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            name VARCHAR(255),
            status ENUM('active', 'unsubscribed', 'bounced') DEFAULT 'active',
            subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at TIMESTAMP NULL,
            confirmed_at TIMESTAMP NULL,
            confirmation_token VARCHAR(100) NULL,
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_lists' => "
        CREATE TABLE IF NOT EXISTS email_lists (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_list_subscribers' => "
        CREATE TABLE IF NOT EXISTS email_list_subscribers (
            list_id INT NOT NULL,
            subscriber_id INT NOT NULL,
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (list_id, subscriber_id),
            FOREIGN KEY (list_id) REFERENCES email_lists(id) ON DELETE CASCADE,
            FOREIGN KEY (subscriber_id) REFERENCES email_subscribers(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_campaigns' => "
        CREATE TABLE IF NOT EXISTS email_campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            template LONGTEXT NOT NULL,
            status ENUM('draft', 'scheduled', 'sending', 'sent') DEFAULT 'draft',
            scheduled_at TIMESTAMP NULL,
            sent_at TIMESTAMP NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_status (status),
            INDEX idx_scheduled_at (scheduled_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_campaign_lists' => "
        CREATE TABLE IF NOT EXISTS email_campaign_lists (
            campaign_id INT NOT NULL,
            list_id INT NOT NULL,
            PRIMARY KEY (campaign_id, list_id),
            FOREIGN KEY (campaign_id) REFERENCES email_campaigns(id) ON DELETE CASCADE,
            FOREIGN KEY (list_id) REFERENCES email_lists(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'email_templates' => "
        CREATE TABLE IF NOT EXISTS email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            created_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'hrm_employees' => "
        CREATE TABLE IF NOT EXISTS hrm_employees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            employee_code VARCHAR(50) NOT NULL UNIQUE,
            department VARCHAR(100),
            designation VARCHAR(100),
            date_of_joining DATE NOT NULL,
            date_of_birth DATE,
            phone VARCHAR(20),
            address TEXT,
            emergency_contact TEXT,
            salary DECIMAL(10, 2) NOT NULL,
            bank_details JSON,
            status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_status (status),
            INDEX idx_department (department)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'hrm_attendance' => "
        CREATE TABLE IF NOT EXISTS hrm_attendance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            date DATE NOT NULL,
            clock_in TIME,
            clock_out TIME,
            status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
            UNIQUE KEY unique_attendance (employee_id, date),
            INDEX idx_date (date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'hrm_leaves' => "
        CREATE TABLE IF NOT EXISTS hrm_leaves (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            leave_type ENUM('sick', 'casual', 'earned', 'maternity', 'paternity') NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            days_count INT NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_status (status),
            INDEX idx_dates (start_date, end_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'hrm_payroll' => "
        CREATE TABLE IF NOT EXISTS hrm_payroll (
            id INT AUTO_INCREMENT PRIMARY KEY,
            employee_id INT NOT NULL,
            month INT NOT NULL,
            year INT NOT NULL,
            basic_salary DECIMAL(10, 2) NOT NULL,
            allowances DECIMAL(10, 2) DEFAULT 0,
            deductions DECIMAL(10, 2) DEFAULT 0,
            net_salary DECIMAL(10, 2) NOT NULL,
            payment_status ENUM('pending', 'processed', 'paid') DEFAULT 'pending',
            payment_date DATE NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (employee_id) REFERENCES hrm_employees(id) ON DELETE CASCADE,
            UNIQUE KEY unique_payroll (employee_id, month, year),
            INDEX idx_period (year, month)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];
