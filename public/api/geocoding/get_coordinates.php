<?php

header("Content-Type: application/json");

require_once __DIR__ . '/../../../src/config/env.php';

$location = $_POST['location'] ?? '';

if (!$location) {

    echo json_encode([
        "error" => "Location is required"
    ]);

    exit;
}

$apiKey = $_ENV['GEOAPIFY_GEOCODING_API_KEY'];

$url =
    "https://api.geoapify.com/v1/geocode/search?text="
    . urlencode($location)
    . "&apiKey="
    . $apiKey;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if ($response === false) {

    echo json_encode([
        "error" => curl_error($ch)
    ]);

    curl_close($ch);

    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (empty($data['features'])) {

    echo json_encode([
        "error" => "Location not found"
    ]);

    exit;
}

echo json_encode([

    "latitude" =>
        $data['features'][0]['geometry']['coordinates'][1],

    "longitude" =>
        $data['features'][0]['geometry']['coordinates'][0]
]);