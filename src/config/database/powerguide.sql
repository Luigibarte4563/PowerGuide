CREATE DATABASE powerguide;
USE powerguide;

-- =====================================
-- USERS
-- Supports:
-- - Local Login
-- - Google Login
-- - JWT Authentication
-- - Roles
-- =====================================
CREATE TABLE users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    google_id VARCHAR(100) UNIQUE NULL,

    name VARCHAR(150) NOT NULL,

    email VARCHAR(150) UNIQUE NOT NULL,

    password VARCHAR(255) NULL,

    picture TEXT NULL,

    auth_provider ENUM(
        'local',
        'google'
    ) NOT NULL DEFAULT 'local',

    role ENUM(
        'user',
        'electric_company',
        'admin'
    ) DEFAULT 'user',

    account_status ENUM(
        'active',
        'suspended',
        'banned'
    ) DEFAULT 'active',

    is_verified BOOLEAN DEFAULT FALSE,

    refresh_token TEXT NULL,

    last_login TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP
);



-- =====================================
-- ELECTRIC COMPANIES
-- Verified companies only
-- =====================================
CREATE TABLE electric_companies (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNIQUE,

    company_name VARCHAR(255) NOT NULL,

    company_email VARCHAR(150),

    contact_number VARCHAR(50),

    address TEXT,

    verification_status ENUM(
        'pending',
        'verified',
        'rejected'
    ) DEFAULT 'pending',

    verified_by_admin_id INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

    FOREIGN KEY (verified_by_admin_id)
    REFERENCES users(id)
    ON DELETE SET NULL
);



-- =====================================
-- COMPANY APPLICATIONS
-- User applies to become
-- an electric company
-- =====================================
CREATE TABLE company_applications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT,

    company_name VARCHAR(255),

    business_document TEXT,

    application_status ENUM(
        'pending',
        'approved',
        'rejected'
    ) DEFAULT 'pending',

    admin_note TEXT NULL,

    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    reviewed_at TIMESTAMP NULL,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);



-- =====================================
-- OUTAGE REPORTS
-- =====================================
CREATE TABLE outage_reports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    location_name VARCHAR(255) NOT NULL,

    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),

    category ENUM(
        'power_outage',
        'low_voltage',
        'power_fluctuation',
        'transformer_explosion',
        'fallen_power_line',
        'electrical_fire',
        'scheduled_maintenance',
        'unknown_issue'
    ) DEFAULT 'power_outage',

    severity ENUM(
        'minor',
        'moderate',
        'critical'
    ) DEFAULT 'moderate',

    description TEXT,

    image_proof TEXT NULL,

    status ENUM(
        'unverified',
        'under_review',
        'verified',
        'resolved',
        'fake_report'
    ) DEFAULT 'unverified',

    verified_by INT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    -- RELATION: reporter
    CONSTRAINT fk_outage_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    -- RELATION: verifier (admin/moderator)
    CONSTRAINT fk_outage_verified_by
        FOREIGN KEY (verified_by)
        REFERENCES users(id)
        ON DELETE SET NULL
);

-- =====================================
-- POWER STATIONS
-- =====================================
CREATE TABLE power_stations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    station_name VARCHAR(255) NOT NULL,

    location_name VARCHAR(255) NOT NULL,

    latitude DECIMAL(10,8),

    longitude DECIMAL(11,8),

    station_type ENUM(
        'power_station',
        'solar_station',
        'charging_station',
        'generator_station'
    ) NOT NULL,

    access_type ENUM('free','paid') DEFAULT 'free',

    status ENUM(
        'active',
        'inactive',
        'maintenance'
    ) DEFAULT 'active',

    description TEXT,

    image TEXT NULL,

    created_by INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);


-- =====================================
-- REPORT CONFIRMATIONS
-- Other users can confirm
-- outage reports
-- =====================================
CREATE TABLE report_confirmations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    report_id INT,

    user_id INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(report_id, user_id),

    FOREIGN KEY (report_id)
    REFERENCES outage_reports(id)
    ON DELETE CASCADE,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);



-- =====================================
-- MAINTENANCE SCHEDULES
-- Posted by verified
-- electric companies
-- =====================================
CREATE TABLE maintenance_schedules (

    id INT AUTO_INCREMENT PRIMARY KEY,

    electric_company_id INT,

    affected_area VARCHAR(255) NOT NULL,

    latitude DECIMAL(10,8),

    longitude DECIMAL(11,8),

    maintenance_date DATE NOT NULL,

    start_time TIME NOT NULL,

    end_time TIME NOT NULL,

    description TEXT,

    estimated_restoration_time DATETIME NULL,

    status ENUM(
        'upcoming',
        'ongoing',
        'completed',
        'cancelled'
    ) DEFAULT 'upcoming',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (electric_company_id)
    REFERENCES electric_companies(id)
    ON DELETE CASCADE
);



-- =====================================
-- ADMIN LOGS
-- Track admin actions
-- =====================================
CREATE TABLE admin_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    admin_id INT,

    action_type VARCHAR(255),

    action_details TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);