<?php

session_start();

require_once __DIR__ . '/../../../src/config/connection.php';
require_once __DIR__ . '/../../../src/config/app.php';
require '../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/config/env.php';

$secret_key = $_ENV['JWT_SECRET_KEY'];

use Firebase\JWT\JWT;

$conn = getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $passwordRaw = $_POST['password'];

    if (strlen($passwordRaw) < 6) {

        $_SESSION['register_error'] = "Password must be at least 6 characters.";

        header("Location: " . BASE_URL . "/auth/auth.php?page=register");
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        $_SESSION['register_error'] = "Email already exists.";

        header("Location: " . BASE_URL . "/auth/auth.php?page=register");
        exit;
    }

    $password = password_hash($passwordRaw, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, auth_provider, created_at)
        VALUES (?, ?, ?, 'local', NOW())
    ");

    $stmt->execute([$name, $email, $password]);

    $userId = $conn->lastInsertId();

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    $payload = [
        "id" => $user['id'],
        "email" => $user['email'],
        "role" => $user['role'],
        "iat" => time(),
        "exp" => time() + 3600
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    setcookie("jwt_token", $jwt, time() + 3600, "/", "", false, true);

    $_SESSION['user'] = [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "auth_provider" => "local"
    ];

    header("Location: " . BASE_URL . "/dashboard/user/user.php");
    exit;
}
?>