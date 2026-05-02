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

  <!-- =========================================
       PASSWORD
  ========================================= -->
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

  <!-- =========================================
       🚨 OUTAGE REPORT FORM (NEW)
  ========================================= -->
  <h2>Create Outage Report</h2>

  <form id="outageForm">

      <input type="text" id="location_name" placeholder="Location Name" required><br><br>

      <select id="category">
          <option value="power_outage">Power Outage</option>
          <option value="low_voltage">Low Voltage</option>
          <option value="power_fluctuation">Power Fluctuation</option>
          <option value="transformer_explosion">Transformer Explosion</option>
          <option value="fallen_power_line">Fallen Power Line</option>
          <option value="electrical_fire">Electrical Fire</option>
          <option value="scheduled_maintenance">Maintenance</option>
          <option value="unknown_issue">Unknown</option>
      </select><br><br>

      <select id="severity">
          <option value="minor">Minor</option>
          <option value="moderate">Moderate</option>
          <option value="critical">Critical</option>
      </select><br><br>

      <textarea id="description" placeholder="Describe the issue..." required></textarea><br><br>

      <button type="submit">Submit Report</button>

  </form>

  <p id="response"></p>

  <!-- =========================================
       JS FETCH TO YOUR API
  ========================================= -->
  <script>
    document.getElementById("outageForm").addEventListener("submit", async function(e) {
        e.preventDefault();

        const data = {
            user_id: <?= $user['id'] ?>, // from session
            location_name: document.getElementById("location_name").value,
            category: document.getElementById("category").value,
            severity: document.getElementById("severity").value,
            description: document.getElementById("description").value,
            image_proof: null
        };

        const response = await fetch("http://localhost/crowdsourced-outage-reporting-api/api/outage_report/create.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        document.getElementById("response").innerText = result.message;
    });
  </script>

  <!-- =========================================
       GEOLOCATION (optional)
  ========================================= -->
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

<!DOCTYPE html>
<html>
<head>
    <title>Outage Map</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        #map {
            height: 90vh;
            width: 100%;
        }
    </style>
</head>

<body>

<h2>Power Outage Reports Map</h2>

<div id="map"></div>

<script>

/* =========================================
   CUSTOM ICON
========================================= */
const outageIcon = L.icon({
    iconUrl: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRV50z4vUtm0jKephlKryT2BI6H6YnQi1NbA&s',
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -35]
});

/* =========================================
   DAGUPAN ONLY MAP VIEW
========================================= */
let map = L.map('map').setView([16.0431, 120.3330], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: 'OpenStreetMap'
}).addTo(map);

/* =========================================
   LOAD REPORTS FROM API
========================================= */
async function loadReports() {

    try {
        const response = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/get.php"
        );

        const result = await response.json();

        if (!result.success) {
            alert(result.message || "Failed to load reports");
            return;
        }

        const reports = result.data;

        reports.forEach(report => {

            if (report.latitude && report.longitude) {

                const marker = L.marker(
                    [report.latitude, report.longitude],
                    { icon: outageIcon }
                ).addTo(map);

                /* =========================================
                   POPUP (CLICK)
                ========================================= */
                marker.bindPopup(`
                    <b>${report.location_name}</b><br>
                    <b>Category:</b> ${report.category}<br>
                    <b>Severity:</b> ${report.severity}<br>
                    <b>Status:</b> ${report.status}<br>
                    <hr>
                    ${report.description}
                `);

                /* =========================================
                   TOOLTIP (HOVER)
                ========================================= */
                marker.bindTooltip(
                    `<b>${report.location_name}</b><br>
                    ${report.description}`,
                    {
                        direction: "top",
                        offset: [0, -20],
                        opacity: 0.9,
                        sticky: true
                    }
                );
            }
        });

    } catch (error) {
        console.error("Error loading reports:", error);
        alert("Failed to load map data.");
    }
}

loadReports();

</script>

</body>
</html>

</body>
</html>