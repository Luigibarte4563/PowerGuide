<?php

session_start();

$_SESSION = [];
session_unset();
session_destroy();

setcookie("jwt_token", "", time() - 3600, "/", "", false, true);

header("Location: index.php");
exit;

?>