<?php
session_start();

require '../../../vendor/autoload.php';
use Firebase\JWT\JWT;

require_once __DIR__ . '/../../../src/config/connection.php';
require_once __DIR__ . '/../../../src/config/app.php';

require_once __DIR__ . '/../../../src/config/env.php';
$secret_key = $_ENV['JWT_SECRET_KEY'];

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users
            WHERE email = ?
            AND auth_provider = 'local'
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: " . BASE_URL . "/auth/auth.php?page=login&error=User not found");
        exit;
    }

    if ($user['account_status'] !== 'active') {
        header("Location: " . BASE_URL . "/auth/auth.php?page=login&error=Account disabled");
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        header("Location: " . BASE_URL . "/auth/auth.php?page=login&error=Invalid credentials");
        exit;
    }

    $update = $conn->prepare("
        UPDATE users
        SET last_login = NOW()
        WHERE id = ?    
    ");

    $update->execute([$user['id']]);

    $payload = [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role'],
        "auth_provider" => $user['auth_provider'],
        "is_verified" => $user['is_verified'],
        "iat" => time(),
        "exp" => time() + 3600
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    setcookie("jwt_token", $jwt, time() + 3600, "/", "", false, true);

    $_SESSION['user'] = [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role']
    ];

    header("Location: " . BASE_URL . "/dashboard/user/user.php");
    exit;
}
?>