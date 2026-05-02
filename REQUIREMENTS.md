 PowerGuide System

PowerGuide is a PHP-based web application that includes:

User Authentication (JWT + Session fallback)
Google OAuth Login support
Dashboard system
Crowdsourced outage reporting API integration
Geocoding API support
MySQL database backend

It also connects to a separate microservice:

 Crowdsourced Outage Reporting API
https://github.com/Luigibarte4563/CrowdsourcedAPI

 Requirements
 Server Requirements
PHP 8.0+
MySQL / MariaDB
Apache (XAMPP recommended)
Composer
 PHP Dependencies

Installed via Composer:

composer require firebase/php-jwt
composer require vlucas/phpdotenv

Used libraries:

 Firebase JWT → Authentication system
 vlucas/phpdotenv → Environment variables support
 Project Setup
1. Clone PowerGuide
git clone https://github.com/Luigibarte4563/PowerGuide.git

Move to XAMPP:

C:\xampp\htdocs\PowerGuide
2. Clone Crowdsourced API (Separate Backend)
git clone https://github.com/Luigibarte4563/CrowdsourcedAPI.git

Move to:

C:\xampp\htdocs\crowdsourced-outage-reporting-api
3. Database Setup

Create database:

CREATE DATABASE powerguide;

Import your tables (users, outage_reports, etc.)

4. Configure Environment (.env)

Create file:

/PowerGuide/.env

Example:

DB_HOST=localhost
DB_NAME=powerguide
DB_USER=root
DB_PASS=

JWT_SECRET=11111111111111111111111111111111
APP_URL=http://localhost/PowerGuide/public
5. Apache Base URL Setup

Access project via:

http://localhost/PowerGuide/public

OR if using IP:

http://192.168.x.x/PowerGuide/public

(Ensure Apache allows LAN access)

 Authentication System

PowerGuide supports:

1. Session Login
$_SESSION['user']
2. JWT Login

Stored in cookie:

$_COOKIE['jwt_token']

Validation handled in:

/src/middleware/requireAuth.php
 API Structure
 PowerGuide API
/public/api/

Modules:

auth/
geocoding/
crowdsourced/
 Crowdsourced API (Separate Service)
/crowdsourced-outage-reporting-api/api/

Example endpoints:

GET  /get_reports.php
POST /create_report.php
📡 Example Usage
Create Report (POST)
POST http://localhost/crowdsourced-outage-reporting-api/api/create_report.php

Body (JSON):

{
  "user_id": 1,
  "location_name": "Zaragoza",
  "latitude": 15.486,
  "longitude": 120.789,
  "category": "power_outage",
  "severity": "moderate",
  "description": "No electricity in the area"
}
 Recommended Folder Structure
PowerGuide
PowerGuide/
│
├── public/
│   ├── api/
│   ├── auth/
│   ├── dashboard/
│
├── src/
│   ├── config/
│   ├── middleware/
│
├── vendor/
└── .env
Crowdsourced API
crowdsourced-outage-reporting-api/
│
├── api/
│   ├── create_report.php
│   ├── get_reports.php
│
├── config/
├── database/
└── index.php
Common Issues
Redirect broken

Use:

header("Location: /PowerGuide/public/dashboard/user.php");

NOT:

public/dashboard/public/api/...
BASE_URL already defined

Only define once in:

config/app.php OR .env loader
PDO undefined

Make sure:

require_once connection.php;
$conn = getConnection();
Run Project
Start XAMPP:
Apache
MySQL
Open browser:
http://localhost/PowerGuide/public
