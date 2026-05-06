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
<title>User Dashboard</title>

<style>
    body { font-family: Arial; }

    nav {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        background: #f4f4f4;
        align-items: center;
    }

    .notif-wrapper {
        position: relative;
        display: inline-block;
    }

    #notifPanel {
        display: none;
        position: absolute;
        right: 0;
        top: 40px;
        width: 350px;
        max-height: 400px;
        overflow-y: auto;
        background: white;
        border: 1px solid #ccc;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        z-index: 999;
    }

    .notif-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .notif-item.unread {
        background: #eaf4ff;
    }

    #notifCount {
        position: absolute;
        top: -5px;
        right: -5px;
        background: red;
        color: white;
        border-radius: 50%;
        font-size: 12px;
        padding: 2px 6px;
        display: none;
    }

    .box {
        padding: 15px;
        border: 1px solid #ddd;
        margin-top: 10px;
        width: 420px;
    }

    button {
        padding: 8px 12px;
        cursor: pointer;
        margin-top: 5px;
    }
</style>

</head>

<body>

<!-- NAV -->
<nav>

    <div>
        <a href="user.php">Home</a> |
        <a href="reports/create_report.php">Create Report</a> |
        <a href="reports/update_report.php">Update Report</a>
    </div>

    <div class="notif-wrapper">
        <button onclick="toggleNotifications()">🔔 Notifications</button>
        <span id="notifCount"></span>
        <div id="notifPanel"></div>
    </div>

</nav>

<!-- USER -->
<h1>Welcome <?= htmlspecialchars($user['name']) ?></h1>

<?php
$defaultPicture = "https://scontent.fbag1-2.fna.fbcdn.net/v/t1.15752-9/667329625_832141525960325_566936363299643684_n.jpg";
$picture = !empty($user['picture']) ? $user['picture'] : $defaultPicture;
?>

<img src="<?= htmlspecialchars($picture) ?>" width="120" height="120" style="border-radius:50%;">

<br><br>

<a href="<?= BASE_URL ?>/logout.php">Logout</a>

<hr>

<!-- PROFILE -->
<h2>Edit Profile</h2>

<form action="<?= BASE_URL ?>/api/user/update_profile.php"
      method="POST"
      enctype="multipart/form-data">

    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>
    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
    <input type="file" name="picture"><br><br>

    <button type="submit">Update Profile</button>
</form>

<hr>

<!-- LOCATION -->
<div class="box">

    <h2>📍 Set Your Location</h2>

    <input type="text" id="location_name"
           placeholder="Enter location"
           style="width:100%; padding:8px;">

    <button onclick="updateLocation()">Save Location</button>

    <button onclick="useCurrentLocation()"
            style="background:#007bff;color:white;">
        📍 Use My Current Location
    </button>

    <div id="response"></div>

</div>

<div class="box">

    <h3>📍 Current Location</h3>

    <p id="current_location">Loading...</p>
    <p id="current_coords"></p>

</div>

<script>

let notifications = [];

/* ================= NOTIFICATIONS ================= */
async function loadNotifications() {

    try {

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/notifications/get_notifications.php",
            { credentials: "include" }
        );

        const data = await res.json();
        if (!data.success) return;

        notifications = data.data;
        renderNotifications();

    } catch (err) {
        console.error(err);
    }
}

function renderNotifications() {

    const panel = document.getElementById("notifPanel");
    const badge = document.getElementById("notifCount");

    const unread = notifications.filter(n => n.is_read == 0);

    badge.style.display = unread.length ? "inline-block" : "none";
    badge.innerText = unread.length;

    panel.innerHTML = notifications.length
        ? notifications.map(n => `
            <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}"
                 onclick="markAsRead(${n.id})">
                <b>${n.title}</b><br>
                <small>${n.message}</small>
            </div>
        `).join("")
        : "<div style='padding:10px'>No notifications</div>";
}

function toggleNotifications() {
    const panel = document.getElementById("notifPanel");
    panel.style.display = panel.style.display === "block" ? "none" : "block";
    loadNotifications();
}

async function markAsRead(id) {

    await fetch(
        "http://localhost/crowdsourcedapi/api/notifications/mark_as_read.php",
        {
            method: "POST",
            credentials: "include",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({id})
        }
    );

    notifications = notifications.map(n =>
        n.id === id ? {...n, is_read: 1} : n
    );

    renderNotifications();
}

/* ================= LOAD LOCATION ================= */
async function loadLocation() {

    try {

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/user_location/get.php",
            { credentials: "include" }
        );

        const data = await res.json();

        if (!data.success) {
            document.getElementById("current_location").innerText =
                data.message;
            return;
        }

        document.getElementById("current_location").innerText =
            "📍 " + (data.data.location_name || "No location");

        document.getElementById("current_coords").innerText =
            `📌 Lat: ${data.data.latitude ?? "-"} | Lng: ${data.data.longitude ?? "-"}`;

        document.getElementById("location_name").value =
            data.data.location_name || "";

    } catch (err) {
        console.error(err);
    }
}

/* ================= MANUAL LOCATION ================= */
async function updateLocation() {

    const location = document.getElementById("location_name").value;

    if (!location) return alert("Enter location");

    const res = await fetch(
        "http://localhost/crowdsourcedapi/api/user_location/location.php",
        {
            method: "POST",
            credentials: "include",
            headers: {"Content-Type":"application/json"},
            body: JSON.stringify({
                location_name: location,
                from_gps: false
            })
        }
    );

    const data = await res.json();

    if (data.success) loadLocation();
    else alert(data.message);
}

/* ================= GPS LOCATION ================= */
function useCurrentLocation() {

    if (!navigator.geolocation) {
        alert("Geolocation not supported");
        return;
    }

    navigator.geolocation.getCurrentPosition(async (pos) => {

        const latitude = pos.coords.latitude;
        const longitude = pos.coords.longitude;

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/user_location/location.php",
            {
                method: "POST",
                credentials: "include",
                headers: {"Content-Type":"application/json"},
                body: JSON.stringify({
                    location_name: "My Current Location",
                    latitude,
                    longitude,
                    from_gps: true
                })
            }
        );

        const data = await res.json();

        if (data.success) {
            alert("GPS location saved");
            loadLocation();
        } else {
            alert(data.message);
        }

    }, () => {
        alert("Please allow location access");
    });
}

/* ================= INIT ================= */
loadLocation();
loadNotifications();

setInterval(loadNotifications, 15000);

</script>

</body>
</html>