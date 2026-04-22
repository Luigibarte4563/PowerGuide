<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
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
<?php

$defaultPicture = "https://media.newyorker.com/photos/59095bb86552fa0be682d9d0/master/pass/Monkey-Selfie.jpg";

$picture = !empty($user['picture'])
    ? $user['picture']
    : $defaultPicture;

?>

<img 
    src="<?= htmlspecialchars($picture) ?>" 
    width="100" 
    alt="Profile Picture"
>
<a href="logout.php">logout</a>

<h2>Edit Profile</h2>
<form action="update_profile.php" method="POST">
  <input type="text" name="name" value="<?= htmlspecialchars($user['name'])?>" require><br><br>

  <input type="email" name="email" value="<?= htmlspecialchars($user['email'])?>" require><br><br>

  <button type="submit">Update Profile</button>

</form>
</body>
</html>
<script>
    navigator.geolocation.getCurrentPosition(
  (position) => {
    console.log(position.coords.latitude);
    console.log(position.coords.longitude);
  },
  (error) => {
    console.log("Permission denied or error");
  }
);
</script>