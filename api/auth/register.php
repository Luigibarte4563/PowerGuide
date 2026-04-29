<?php

session_start();

require_once __DIR__ . '/../../src/config/connection.php';

$conn = getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];

    // hash password
    $password = password_hash(
        $_POST['password'],
        PASSWORD_BCRYPT
    );

    // check if email exists
    $check = "SELECT id FROM users WHERE email = ?";

    $stmt = $conn->prepare($check);
    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        header("Location: auth.php?page=register&error=Email already exists");
        exit;
    }

    // insert user
    $sql = "INSERT INTO users
            (name, email, password, auth_provider)
            VALUES (?, ?, ?, 'local')";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $name,
        $email,
        $password
    ]);

    // get inserted user id
    $userId = $conn->lastInsertId();

    // create session immediately
    $_SESSION['user'] = [

        "id" => $userId,
        "name" => $name,
        "email" => $email,
        "auth_provider" => "local"

    ];

    // redirect directly to dashboard
    header("Location: ../../public/dashboard.php");
    exit;
}
?>