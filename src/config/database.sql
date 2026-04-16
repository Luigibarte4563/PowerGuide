CREATE DATABASE oauth_demo;
USE oauth_demo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    google_id VARCHAR(100) UNIQUE,  -- from responsePayload.sub

    name VARCHAR(150),
    email VARCHAR(150) UNIQUE,
    picture TEXT,

    provider VARCHAR(50) DEFAULT 'google',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);