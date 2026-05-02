<?php

session_start();

require_once __DIR__ . '/../../../src/middleware/requireAuth.php';
require_once __DIR__ . '/../../../src/config/app.php';

$user = requireAuth();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>

<body>

  <h1>Welcome <?= htmlspecialchars($user['name']) ?></h1>

  <?php

    $defaultPicture = "https://scontent.fbag1-2.fna.fbcdn.net/v/t1.15752-9/667329625_832141525960325_566936363299643684_n.jpg";

    $picture = !empty($user['picture'])
        ? $user['picture']
        : $defaultPicture;

  ?>

  <!-- PROFILE IMAGE -->
  <img src="<?= htmlspecialchars($picture) ?>" width="120" height="120" style="border-radius:50%;">

  <br><br>

  <a href="<?= BASE_URL ?>/logout.php">Logout</a>

  <h2>Edit Profile</h2>

  <!-- 🔥 UPDATED FORM (NOW SUPPORTS IMAGE UPLOAD) -->
  <form action="<?= BASE_URL ?>/api/user/update_profile.php"
        method="POST"
        enctype="multipart/form-data">

    <input type="text" name="name"
           value="<?= htmlspecialchars($user['name']) ?>"
           required><br><br>

    <input type="email" name="email"
           value="<?= htmlspecialchars($user['email']) ?>"
           required><br><br>

    <!-- PROFILE IMAGE UPLOAD -->
    <input type="file" name="picture" accept="image/*"><br><br>

    <button type="submit">Update Profile</button>

  </form>

  <h2>Change Password</h2>

<?php if ($user['auth_provider'] !== 'google'): ?>

    <h2>Change Password</h2>

    <form action="<?= BASE_URL ?>/api/user/update_password.php" method="POST">

        <input type="password"
               name="current_password"
               placeholder="Current Password"
               required><br><br>

        <input type="password"
               name="new_password"
               placeholder="New Password"
               required><br><br>

        <input type="password"
               name="confirm_password"
               placeholder="Confirm New Password"
               required><br><br>

        <button type="submit">Update Password</button>

    </form>

<?php else: ?>

    <h2>Security</h2>
    <p style="color:gray;">
        You are logged in using Google. Password management is handled by Google.
    </p>

<?php endif; ?>

  <script>
    navigator.geolocation.getCurrentPosition(
      (position) => {

        console.log("Latitude:", position.coords.latitude);
        console.log("Longitude:", position.coords.longitude);

      },
      (error) => {
        console.log("Permission denied or error");
      }
    );
  </script>

</body>
</html>