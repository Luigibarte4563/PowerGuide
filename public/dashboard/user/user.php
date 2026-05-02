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
<!DOCTYPE html>
<html>
<head>

    <title>Power Outage Reports Map</title>

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>

        body{
            font-family: Arial;
            margin:0;
            padding:20px;
        }

        form{
            margin-bottom:20px;
            padding:20px;
            border:1px solid #ccc;
            border-radius:10px;
        }

        input,
        select,
        textarea,
        button{
            width:100%;
            padding:10px;
            margin-top:10px;
        }

        #map{
            height:80vh;
            width:100%;
            border-radius:10px;
        }

    </style>

</head>

<body>

<h1>Power Outage Reporting System</h1>

<!-- =========================================
     CREATE REPORT FORM
========================================= -->
<form action="/../../api/crowdsourced/create_report.php" method="POST">

    <h2>Create Outage Report</h2>

    <input
        type="text"
        name="location_name"
        placeholder="Location Name"
        required
    >

    <select name="category">

        <option value="power_outage">
            Power Outage
        </option>

        <option value="low_voltage">
            Low Voltage
        </option>

        <option value="power_fluctuation">
            Power Fluctuation
        </option>

    </select>

    <select name="severity">

        <option value="minor">Minor</option>
        <option value="moderate">Moderate</option>
        <option value="critical">Critical</option>

    </select>

    <textarea
        name="description"
        placeholder="Describe the outage..."
        required
    ></textarea>

    <button type="submit">
        Submit Report
    </button>

</form>

<!-- =========================================
     MAP
========================================= -->
<h2>Outage Reports Map</h2>

<div id="map"></div>

<script>

/* =========================================
   CUSTOM ICON
========================================= */
const outageIcon = L.icon({

    iconUrl:
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRV50z4vUtm0jKephlKryT2BI6H6YnQi1NbA&s',

    iconSize: [40, 40],

    iconAnchor: [20, 40],

    popupAnchor: [0, -35]

});

/* =========================================
   MAP VIEW
========================================= */
let map = L.map('map').setView(
    [16.0431, 120.3330],
    13
);

/* =========================================
   MAP TILE
========================================= */
L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        attribution: 'OpenStreetMap'
    }
).addTo(map);

/* =========================================
   LOAD REPORTS
========================================= */
async function loadReports(){

    try{

        const response = await fetch(
            "http://localhost/powerguide/public/api/crowdsourced/get_reports.php"
        );

        const result = await response.json();

        if(!result.success){
            alert("Failed to load reports");
            return;
        }

        const reports = result.data;

        reports.forEach(report => {

            if(report.latitude && report.longitude){

                const marker = L.marker(
                    [
                        report.latitude,
                        report.longitude
                    ],
                    {
                        icon: outageIcon
                    }
                ).addTo(map);

                /* =========================================
                   POPUP
                ========================================== */
                marker.bindPopup(`

                    <div style="width:250px;">

                        <h3>${report.location_name}</h3>

                        <hr>

                        <b>Category:</b>
                        ${report.category}<br>

                        <b>Severity:</b>
                        ${report.severity}<br>

                        <b>Status:</b>
                        ${report.status}<br>

                        <b>Affected Houses:</b>
                        ${report.affected_houses}<br>

                        <b>Active Outage:</b>
                        ${report.is_active}<br>

                        <b>Hazard:</b>
                        ${report.hazard_type}<br>

                        <b>Started:</b>
                        ${report.started_at ?? "Unknown"}<br>

                        <hr>

                        <b>Description:</b><br>

                        ${report.description}

                    </div>

                `);

                /* =========================================
                   HOVER TOOLTIP
                ========================================== */
                marker.bindTooltip(
                    `
                    <b>${report.location_name}</b><br>
                    ${report.description}
                    `,
                    {
                        direction: "top",
                        offset: [0, -20],
                        opacity: 0.9,
                        sticky: true
                    }
                );
            }

        });

    }catch(error){

        console.error(error);

        alert("Failed to load map data");

    }

}

loadReports();

</script>
</body>
</html>


<!-- udpate -->

<!DOCTYPE html>
<html>

<head>

    <title>My Reports</title>

    <style>

        body{
            font-family: Arial;
            padding:20px;
        }

        .card{
            border:1px solid #ccc;
            padding:15px;
            margin-bottom:10px;
            border-radius:10px;
        }

        .btn{
            padding:8px 12px;
            margin-top:8px;
            cursor:pointer;
        }

        #formBox{
            display:none;
            margin-top:20px;
            padding:15px;
            border:2px solid #000;
            border-radius:10px;
        }

        input,
        select,
        textarea{
            width:100%;
            padding:8px;
            margin-top:8px;
        }

    </style>

</head>

<body>

<h2>My Outage Reports</h2>

<div id="list"></div>

<!-- UPDATE FORM -->
<div id="formBox">

    <h3>Update Report</h3>

    <input type="hidden" id="id">

    <input type="text" id="location_name" placeholder="Location">

    <select id="category">
        <option value="power_outage">Power Outage</option>
        <option value="low_voltage">Low Voltage</option>
        <option value="power_fluctuation">Power Fluctuation</option>
    </select>

    <select id="severity">
        <option value="minor">Minor</option>
        <option value="moderate">Moderate</option>
        <option value="critical">Critical</option>
    </select>

    <textarea id="description" placeholder="Description"></textarea>

    <input type="number" id="affected_houses" placeholder="Affected Houses">

    <select id="status">
        <option value="unverified">Unverified</option>
        <option value="verified">Verified</option>
        <option value="resolved">Resolved</option>
    </select>

    <button class="btn" onclick="updateReport()">
        Save Changes
    </button>

    <button class="btn" onclick="closeForm()">
        Cancel
    </button>

</div>

<script>

/* =========================================
   LOAD ONLY USER REPORTS
========================================= */

async function loadReports(){

    try {

        const res = await fetch(
            "http://localhost/powerguide/public/api/crowdsourced/get_my_reports.php",
            {
                credentials: "include"
            }
        );

        const data = await res.json();

        const list = document.getElementById("list");

        if(!data.success){
            list.innerHTML = "Not logged in or no reports found.";
            return;
        }

        list.innerHTML = "";

        data.data.forEach(r => {

            list.innerHTML += `
                <div class="card">

                    <b>${r.location_name}</b><br>
                    ${r.description}<br><br>

                    Status: ${r.status}<br>
                    Affected: ${r.affected_houses}<br>

                    <button class="btn" onclick='editReport(${JSON.stringify(r).replace(/"/g, "&quot;")})'>
                        Edit
                    </button>

                </div>
            `;
        });

    } catch (error) {
        console.error(error);
        document.getElementById("list").innerHTML = "Failed to load reports.";
    }
}


/* =========================================
   OPEN EDIT FORM
========================================= */

function editReport(r){

    document.getElementById("formBox").style.display = "block";

    document.getElementById("id").value = r.id;
    document.getElementById("location_name").value = r.location_name;
    document.getElementById("category").value = r.category;
    document.getElementById("severity").value = r.severity;
    document.getElementById("description").value = r.description;
    document.getElementById("affected_houses").value = r.affected_houses;
    document.getElementById("status").value = r.status;
}


/* =========================================
   CLOSE FORM
========================================= */

function closeForm(){
    document.getElementById("formBox").style.display = "none";
}


/* =========================================
   UPDATE REPORT (FIXED API CALL)
========================================= */

async function updateReport(){

    try {

        const payload = {

            id: document.getElementById("id").value,
            location_name: document.getElementById("location_name").value,
            category: document.getElementById("category").value,
            severity: document.getElementById("severity").value,
            description: document.getElementById("description").value,
            affected_houses: document.getElementById("affected_houses").value,
            status: document.getElementById("status").value

        };

        const res = await fetch(
            "http://localhost/powerguide/public/api/crowdsourced/update_report.php",
            {
                method: "POST",
                credentials: "include",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            }
        );

        const result = await res.json();

        alert(result.message);

        if(result.success){
            closeForm();
            loadReports();
        }

    } catch (error) {
        console.error(error);
        alert("Update failed.");
    }
}


/* =========================================
   INIT
========================================= */

loadReports();

</script>
</body>
</html>