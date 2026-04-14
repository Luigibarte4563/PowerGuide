<?php
session_start();

if(!isset($_SESSION['user'])) {
    header("Location: log.php");
    exit;
}

$user = $_SESSION['user'];

?>

<h1>Welcome <?= $user['name']; ?></h1>