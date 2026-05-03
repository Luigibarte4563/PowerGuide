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
   REQUIRED
========================================= */
$id = $data["id"] ?? null;

if (!$id) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Report ID is required"
    ]);

    exit;
}

/* =========================================
   BUILD UPDATE DATA
========================================= */
$payload = [

    "id" => $id,

    "location_name" => $data["location_name"] ?? null,

    "category" => $data["category"] ?? null,

    "severity" => $data["severity"] ?? null,

    "description" => $data["description"] ?? null,

    "affected_houses" => $data["affected_houses"] ?? null,

    "status" => $data["status"] ?? null,

    /* FIXED: add verification_status */
    "verification_status" => $data["verification_status"] ?? null,

    /* IMPORTANT */
    "user_id" => $user_id

];

/* =========================================
   REMOVE NULL VALUES
========================================= */
$payload = array_filter($payload, function ($value) {
    return $value !== null;
});

/* =========================================
   API ENDPOINT
========================================= */
$url = "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/update.php";

/* =========================================
   CURL REQUEST
========================================= */
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

/* SEND SESSION COOKIE */
curl_setopt(
    $ch,
    CURLOPT_COOKIE,
    session_name() . '=' . session_id()
);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($payload)
);

/* =========================================
   EXECUTE
========================================= */
$response = curl_exec($ch);

if (curl_errno($ch)) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => curl_error($ch)
    ]);

    curl_close($ch);

    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* =========================================
   RETURN API RESPONSE
========================================= */
http_response_code($http_code);

echo $response;

?>