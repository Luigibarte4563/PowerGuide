<?php
session_start();
require_once _DIR_ . '/../src/config/connection.php';

if(!isset($_SESSION['user'])) {
    header("Location: logout.php");
    exit();
}

$conn = getConnection();

$name = $_POST['name'];
$email = $_POST['email'];
$google_id = $_SESSION['user']['google_id'];

$sql = "UPDATE users 
        SET name = ?, email = ? 
        WHERE google_id = ?";

$stmt = $conn->prepare($sql);
$stmt->execute([$name, $email, $google_id]);

$_SESSION['user']['name'] = $name;
$_SESSION['user']['email'] = $email;

header("Location: dashboard.php");
exit();
?>