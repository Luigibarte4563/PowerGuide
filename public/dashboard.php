<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

echo $user['picture'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

<h1>Welcome <?php echo $user['name']; ?></h1>
<img src="<?php echo htmlspecialchars($user['picture']); ?>" width="100" alt="Profile Picture">
<a href="logout.php">logout</a>
</body>
</html>