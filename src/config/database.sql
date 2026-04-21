CREATE DATABASE oauth_google;

USE DATABASE oauth_google;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,

    password VARCHAR(255) DEFAULT NULL,     -- for REGISTER users only
    google_id VARCHAR(100) UNIQUE DEFAULT NULL, -- for GOOGLE users only
    picture TEXT DEFAULT NULL,

    auth_provider ENUM('local','google') NOT NULL DEFAULT 'local',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);