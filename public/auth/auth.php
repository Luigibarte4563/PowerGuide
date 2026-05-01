<?php
session_start();

$page = $_GET['page'] ?? 'login'; // login or register
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
</head>
<body>

<div class="box">

<?php if ($page === 'login'): ?>

    <!-- LOGIN FORM -->
    <h2>Login</h2>

    <form action="../api/auth/login.php" method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>

        <button type="submit">Login</button>
    </form>

    <a href="auth.php?page=register">No account? Register here</a>

<?php else: ?>

    <!-- REGISTER FORM -->
    <h2>Register</h2>

    <form action="../api/auth/register.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>

        <button type="submit">Register</button>
    </form>

    <a href="auth.php?page=login">Already have an account? Login</a>

<?php endif; ?>

</div>

</body>
</html>