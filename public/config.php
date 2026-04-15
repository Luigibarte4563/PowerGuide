<?php
session_start();

if(!$conn = new mysqli("localhost", "root", "", "oauth_demo")) {
    echo 'Unable to connect to the database!';
    die;
}

define('GOOGLE_CLIENT_ID', '693655111723-8of9p12p0kk1ka153f2n3mffa0pu3ens.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/PowerGuard/google-callback.php');
?>