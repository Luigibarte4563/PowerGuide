<?php

session_start();

require_once __DIR__ . '/../src/config/connection.php';

if (!isset($_SESSION['user'])) {

    header("Location: logout.php");
    exit();
}

$conn = getConnection();

$name = trim($_POST['name']);
$email = trim($_POST['email']);

$user_id = $_SESSION['user']['id'];

$sql = "UPDATE users
        SET name = ?, email = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $name,
    $email,
    $user_id
]);

// update session
$_SESSION['user']['name'] = $name;
$_SESSION['user']['email'] = $email;

header("Location: dashboard.php");
exit();

?>