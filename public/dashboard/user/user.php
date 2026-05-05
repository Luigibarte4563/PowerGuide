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

  <style>
    body {
      font-family: Arial;
    }

    .box {
      padding: 15px;
      border: 1px solid #ccc;
      margin-top: 10px;
      width: 400px;
    }

    button {
      padding: 8px 12px;
      cursor: pointer;
    }

    #response {
      margin-top: 10px;
      font-weight: bold;
    }
  </style>

</head>

<body>

<nav>
    <a href="user.php">Home</a> |
    <a href="create_report.php">Create Report</a> |
    <a href="update_report.php">Update Report</a>
</nav>

<h1>Welcome <?= htmlspecialchars($user['name']) ?></h1>

<?php
$defaultPicture = "https://scontent.fbag1-2.fna.fbcdn.net/v/t1.15752-9/667329625_832141525960325_566936363299643684_n.jpg";

$picture = !empty($user['picture'])
    ? $user['picture']
    : $defaultPicture;
?>

<img src="<?= htmlspecialchars($picture) ?>" width="120" height="120" style="border-radius:50%;">

<br><br>

<a href="<?= BASE_URL ?>/logout.php">Logout</a>

<hr>

<!-- =========================================
     PROFILE UPDATE
========================================= -->
<h2>Edit Profile</h2>

<form action="<?= BASE_URL ?>/api/user/update_profile.php"
      method="POST"
      enctype="multipart/form-data">

    <input type="text" name="name"
           value="<?= htmlspecialchars($user['name']) ?>"
           required><br><br>

    <input type="email" name="email"
           value="<?= htmlspecialchars($user['email']) ?>"
           required><br><br>

    <input type="file" name="picture" accept="image/*"><br><br>

    <button type="submit">Update Profile</button>

</form>

<hr>

<!-- =========================================
     LOCATION INPUT
========================================= -->
<div class="box">

  <h2>📍 Set Your Location</h2>

  <input type="text" id="location_name"
         placeholder="e.g. Bonuan Gueset, Dagupan City"
         style="width:100%; padding:8px;">

  <br><br>

  <button onclick="updateLocation()">Save Location</button>

  <div id="response"></div>

</div>

<!-- =========================================
     DISPLAY LOCATION
========================================= -->
<div class="box">

  <h3>📍 Your Current Location</h3>

  <p id="current_location">Loading...</p>
  <p id="current_barangay"></p>

</div>

<script>

/* =========================================
   LOAD SAVED LOCATION
========================================= */
async function loadLocation() {

    try {
        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/user_location/get.php"
        );

        const data = await res.json();

        if (data.success && data.data) {

            document.getElementById("current_location").innerText =
                "📍 " + data.data.location_name;

            document.getElementById("current_barangay").innerText =
                "🏘️ Barangay: " + (data.data.barangay ?? "Unknown");

            document.getElementById("location_name").value =
                data.data.location_name;

        } else {
            document.getElementById("current_location").innerText =
                "No location saved yet.";

            document.getElementById("current_barangay").innerText = "";
        }

    } catch (err) {
        console.error(err);
        document.getElementById("current_location").innerText =
            "Failed to load location.";
    }
}

/* =========================================
   SAVE LOCATION
========================================= */
async function updateLocation() {

    const location = document.getElementById("location_name").value;
    const responseBox = document.getElementById("response");

    if (!location) {
        responseBox.style.color = "red";
        responseBox.innerText = "Please enter a location.";
        return;
    }

    try {
        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/user_location/location.php",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    location_name: location
                })
            }
        );

        const data = await res.json();

        if (data.success) {

            responseBox.style.color = "green";
            responseBox.innerText =
                "Location saved! Barangay: " +
                (data.data?.barangay ?? "Unknown");

            // refresh display
            loadLocation();

        } else {
            responseBox.style.color = "red";
            responseBox.innerText = data.message;
        }

    } catch (error) {
        responseBox.style.color = "red";
        responseBox.innerText = "Server error. Try again.";
    }
}

/* INIT */
loadLocation();

</script>

</body>
</html>