<?php

session_start();

require_once __DIR__ . '/../src/config/connection.php';

$conn = getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // find local user
    $sql = "SELECT * FROM users
            WHERE email = ?
            AND auth_provider = 'local'";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // verify password
    if ($user && password_verify($password, $user['password'])) {

        // create session
        $_SESSION['user'] = [

            "id" => $user['id'],
            "name" => $user['name'],
            "email" => $user['email'],
            "auth_provider" => $user['auth_provider']

        ];

        // ✅ redirect after login success
        header("Location: dashboard.php");
        exit;

    } else {

        // login failed
        header("Location: auth.php?page=login&error=Invalid credentials");
        exit;
    }
}
?>