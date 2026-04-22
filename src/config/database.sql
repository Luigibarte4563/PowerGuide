CREATE DATABASE oauth_google;

USE oauth_google;

CREATE TABLE users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    google_id VARCHAR(100) UNIQUE,

    name VARCHAR(150),

    email VARCHAR(150) UNIQUE,

    password VARCHAR(255) NULL,

    picture TEXT,

    auth_provider ENUM('local','google') 
    NOT NULL DEFAULT 'local',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
    DEFAULT CURRENT_TIMESTAMP 
    ON UPDATE CURRENT_TIMESTAMP
);