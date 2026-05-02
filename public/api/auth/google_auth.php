<?php

session_start();

require_once __DIR__ . '/../../../src/config/connection.php';
require '../../../vendor/autoload.php';

use Firebase\JWT\JWT;

$conn = getConnection();

$secret_key = "11111111111111111111111111111111";

$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$picture = $_POST['picture'] ?? null;
$google_id = $_POST['sub'] ?? null;

if (!$email || !$google_id) {
    exit("invalid");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {

    $stmt = $conn->prepare("
        INSERT INTO users (name, email, picture, google_id, auth_provider)
        VALUES (?, ?, ?, ?, 'google')
    ");

    $stmt->execute([$name, $email, $picture, $google_id]);

    $user_id = $conn->lastInsertId();

} else {

    $user_id = $user['id'];
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['user'] = [
    "id" => $user['id'],
    "name" => $user['name'],
    "email" => $user['email'],
    "auth_provider" => "google"
];

$payload = [
    "id" => $user['id'],
    "email" => $user['email'],
    "role" => $user['role'],
    "iat" => time(),
    "exp" => time() + 3600
];

$jwt = JWT::encode($payload, $secret_key, 'HS256');

setcookie("jwt_token", $jwt, time() + 3600, "/", "", false, true);

echo "success";