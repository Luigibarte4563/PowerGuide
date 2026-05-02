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
   INPUT JSON
========================================= */
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON"
    ]);
    exit;
}

/* =========================================
   ADD SESSION USER ID
========================================= */
$data["user_id"] = $user_id;

/* =========================================
   CALL API
========================================= */
$url = "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/update.php";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

/* IMPORTANT: send session cookie */
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* =========================================
   OUTPUT
========================================= */
echo $response;