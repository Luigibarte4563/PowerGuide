<?php

header("Content-Type: application/json");

session_start();

/* =========================================
   CHECK SESSION
========================================= */

$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

/* =========================================
   CALL INTERNAL API (get.php)
========================================= */

$url = "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/get.php?user_id=" . urlencode($user_id);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

/* =========================================
   OUTPUT API RESPONSE
========================================= */

echo $response;