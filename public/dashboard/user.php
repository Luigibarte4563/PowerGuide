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
  $defaultPicture = "https://scontent.fbag1-2.fna.fbcdn.net/v/t1.15752-9/667329625_832141525960325_566936363299643684_n.jpg?_nc_cat=107&ccb=1-7&_nc_sid=9f807c&_nc_eui2=AeGVNNH2c8u51K63IMi0MSu2RzItqHBeNwBHMi2ocF43ABaDAgCZKeueIHBf0_X8eIXh4cOLIJq0NZeAK1v-ui0F&_nc_ohc=wKZjArz4WLsQ7kNvwF7_Xjy&_nc_oc=AdpO5bF0ZbJTP0mMdhRsIcNe9vvMkZ-eAviQkALDobFFkS1Ug66cGZF5i_c5mcQAyMI&_nc_zt=23&_nc_ht=scontent.fbag1-2.fna&oh=03_Q7cD5AHYLWeWKmt8bns7JfPWi5j1GmGMFUJk3R4gd4fKJKrFSQ&oe=6A125669";

  $picture = !empty($user['picture'])
    ? $user['picture']
    : $defaultPicture;
  ?>

  <img src="<?= htmlspecialchars($picture) ?>" width="100" alt="Profile Picture">
  <a href="../logout.php">logout</a>

  <h2>Edit Profile</h2>
  <form action="../api/auth/udpate_profile.php" method="POST">
    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" require><br><br>

    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" require><br><br>

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