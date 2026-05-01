<form action="get_coordinates.php" method="POST">    
    location: <input type="text" name="location">
    <button type="submit">Submit</button>
</form>

<?php
require_once __DIR__ . '/../../../src/config/env.php';


$location = $_POST['location'];

$apiKey = $_ENV['GEOAPIFY_GEOCODING_API_KEY'];
$url = "https://api.geoapify.com/v1/geocode/search?text=$location&apiKey=$apiKey";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode([
        "error" => "API not responding: " . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$httpCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo json_encode([
        "error" => "API returned status code: " . $httpCode
    ]);
    exit;
}

$data = json_decode($response, true);

echo $data['features'][0]['geometry']['coordinates'][0] . "<br>";
echo $data['features'][0]['geometry']['coordinates'][1];


// header("Content-Type: application/json");
// echo json_encode($data, JSON_PRETTY_PRINT);
?> 