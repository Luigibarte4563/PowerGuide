<?php
session_start();

if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

?>

<h1>Welcome <?= $user['name']; ?></h1>
<p> Email: <?= $user['email']; ?>?></p>
<img src="<?= $user['picture']; ?>" width="100px" alt="">
<br><br>
<a href="logout.php">logout</a>