<?php

require_once __DIR__ . '/../../../src/config/env.php';

$url = "http://localhost/crowdsourced-outage-reporting-api/api/geo/get_coordinates.php";

/* =========================================
   DYNAMIC INPUT (from form / POST / frontend)
========================================= */
$location_name = $_POST['location_name'] ?? null;

/* =========================================
   VALIDATION
========================================= */
if (!$location_name) {
    echo json_encode([
        "success" => false,
        "message" => "location_name is required"
    ]);
    exit;
}

/* =========================================
   BUILD DATA
========================================= */
$data = [
    "location_name" => $location_name
];

/* =========================================
   CURL REQUEST
========================================= */
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode([
        "success" => false,
        "message" => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

/* =========================================
   OUTPUT RESPONSE
========================================= */
echo $response;