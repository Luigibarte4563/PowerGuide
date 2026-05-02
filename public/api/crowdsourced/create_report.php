<?php
require_once __DIR__ . '/../../../src/config/env.php';

$url = "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/create.php";

$token = "YOUR_JWT_TOKEN_HERE";

/* =========================================
   DYNAMIC INPUT (example from form or POST)
========================================= */
$location_name = $_POST['location_name'] ?? null;
$category      = $_POST['category'] ?? null;
$severity      = $_POST['severity'] ?? null;
$description   = $_POST['description'] ?? null;

/* =========================================
   BUILD DATA ARRAY DYNAMICALLY
========================================= */
$data = [
    "location_name" => $location_name,
    "category" => $category,
    "severity" => $severity,
    "description" => $description,
    "image_proof" => null
];

/* =========================================
   CURL REQUEST
========================================= */
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $token
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

curl_close($ch);

echo $response;