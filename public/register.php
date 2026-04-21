<?php
session_start();
require_once __DIR__ . '/../src/config/connection.php';

$conn = getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // find user
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // store session
        $_SESSION['user'] = [
            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email']
        ];

        // 🔥 REDIRECT TO DASHBOARD
        header("Location: dashboard.php");
        exit;

    } else {
        // failed login
        header("Location: auth.php?page=login&error=Invalid credentials");
        exit;
    }
}