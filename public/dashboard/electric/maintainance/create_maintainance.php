<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Scheduler</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        #map {
            height: 450px;
            margin-top: 10px;
            border-radius: 10px;
        }

        form {
            display: grid;
            gap: 10px;
            max-width: 450px;
        }

        input, textarea, button {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #2ecc71;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #27ae60;
        }

        #radiusValue {
            font-weight: bold;
        }

        .row {
            display: flex;
            gap: 10px;
        }

        .row > div {
            flex: 1;
        }
    </style>
</head>

<body>

<h2>⚡ Create Maintenance Schedule</h2>

<form id="maintenanceForm">

    <input type="text" id="affected_area" placeholder="Affected Area (auto-filled from map)" required>

    <div class="row">
        <div>
            <label>Maintenance Date</label>
            <input type="date" id="maintenance_date" required>
        </div>

        <div>
            <label>Radius (meters)</label>
            <input type="range" id="radius" min="500" max="10000" value="2000">
            <div>Radius: <span id="radiusValue">2000</span> m</div>
        </div>
    </div>

    <div class="row">
        <input type="time" id="start_time" required>
        <input type="time" id="end_time" required>
    </div>

    <textarea id="description" placeholder="Description"></textarea>

    <!-- hidden coords -->
    <input type="hidden" id="latitude">
    <input type="hidden" id="longitude">

    <button type="submit">Create Maintenance</button>
</form>

<p id="status"></p>

<div id="map"></div>

<script>

/* ================= MAP ================= */
let map = L.map('map').setView([16.0431, 120.3330], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: "© OpenStreetMap"
}).addTo(map);

let marker;
let circle;

/* ICON */
const icon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/252/252025.png',
    iconSize: [35, 35],
    iconAnchor: [17, 35]
});

/* ================= UPDATE RADIUS UI ================= */
const radiusInput = document.getElementById("radius");
const radiusValue = document.getElementById("radiusValue");

radiusInput.addEventListener("input", () => {

    radiusValue.innerText = radiusInput.value;

    if(circle){
        circle.setRadius(radiusInput.value);
    }
});

/* ================= MAP CLICK ================= */
map.on("click", async function(e){

    const lat = e.latlng.lat;
    const lng = e.latlng.lng;

    document.getElementById("latitude").value = lat;
    document.getElementById("longitude").value = lng;

    if(marker) map.removeLayer(marker);
    if(circle) map.removeLayer(circle);

    marker = L.marker([lat, lng], { icon }).addTo(map);

    circle = L.circle([lat, lng], {
        radius: radiusInput.value
    }).addTo(map);

    map.setView([lat, lng], 15);

    /* ================= GEOCODING ================= */
    try {
        const res = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
        );

        const data = await res.json();

        document.getElementById("affected_area").value =
            data.display_name || `${lat}, ${lng}`;

    } catch(err){
        document.getElementById("affected_area").value = `${lat}, ${lng}`;
    }
});

/* ================= SUBMIT ================= */
document.getElementById("maintenanceForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
        affected_area: document.getElementById("affected_area").value,
        maintenance_date: document.getElementById("maintenance_date").value,
        start_time: document.getElementById("start_time").value,
        end_time: document.getElementById("end_time").value,
        description: document.getElementById("description").value,
        radius: document.getElementById("radius").value,
        latitude: document.getElementById("latitude").value,
        longitude: document.getElementById("longitude").value
    };

    try {

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/maintainance/create.php",
            {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                credentials: "include",
                body: JSON.stringify(payload)
            }
        );

        const result = await res.json();

        document.getElementById("status").innerText = result.message;

        if(result.success){
            alert(
                `Maintenance created!\nNotifications sent: ${result.notifications_sent}`
            );
        }

    } catch(err){
        console.error(err);
        document.getElementById("status").innerText = "Error creating maintenance";
    }
});

</script>

</body>
</html>