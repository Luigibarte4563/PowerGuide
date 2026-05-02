<?php

require_once __DIR__ . '/../config/connection.php';
require_once __DIR__ . '/../config/app.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function requireAuth() {
    $conn = getConnection();

    $secret_key = "11111111111111111111111111111111";

    $user = null;

    if (isset($_COOKIE['jwt_token'])) {

        try {

            $decoded = JWT::decode(
                $_COOKIE['jwt_token'],
                new Key($secret_key, 'HS256')
            );

            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$decoded->id]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $user = null;
        }
    }

    if (!$user && isset($_SESSION['user'])) {

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user']['id']]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$user) {

        header("Location: " . BASE_URL . "/auth/auth.php?page=login");
        exit;
    }

    return $user;
}