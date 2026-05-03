<!DOCTYPE html>
<html>
<head>
    <title>PowerGuide Outage System</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

<?php
session_start();
require_once __DIR__ . '/../../../src/middleware/requireAuth.php';
$user = requireAuth();
?>

<h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>

<form id="outageForm">

    <input type="text" id="location_name" placeholder="Location Name" required>

    <select id="category">
        <option value="power_outage">Power Outage</option>
        <option value="low_voltage">Low Voltage</option>
        <option value="power_fluctuation">Power Fluctuation</option>
        <option value="transformer_explosion">Transformer Explosion</option>
        <option value="fallen_power_line">Fallen Power Line</option>
        <option value="electrical_fire">Electrical Fire</option>
        <option value="scheduled_maintenance">Maintenance</option>
        <option value="unknown_issue">Unknown</option>
    </select>

    <select id="severity">
        <option value="minor">Minor</option>
        <option value="moderate" selected>Moderate</option>
        <option value="critical">Critical</option>
    </select>

    <input type="number" id="affected_houses" value="1" min="1">

    <select id="hazard_type">
        <option value="none">None</option>
        <option value="fire">Fire Risk</option>
        <option value="smoke">Smoke</option>
        <option value="sparks">Sparks</option>
        <option value="fallen_wire">Fallen Wire</option>
        <option value="explosion_sound">Explosion Sound</option>
    </select>

    <textarea id="description" placeholder="Description" required></textarea>

    <input type="hidden" id="latitude">
    <input type="hidden" id="longitude">

    <button type="button" onclick="useCurrentLocation()">Use My Location</button>
    <button type="submit">Submit Report</button>

</form>

<p id="response"></p>

<div id="map" style="height:400px;"></div>

<script>

/* ==============================
   MAP INIT
============================== */
let map = L.map('map').setView([16.0431, 120.3330], 13);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

/* FIX: icon must exist BEFORE using markers */
const outageIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/355/355980.png',
    iconSize: [35, 35],
    iconAnchor: [17, 35]
});

/* FIX: layer group must be declared early */
let reportLayer = L.layerGroup().addTo(map);
let marker;

/* ==============================
   GEOLOCATION
============================== */
function useCurrentLocation(){
    navigator.geolocation.getCurrentPosition(async (pos) => {

        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;

        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lng;

        if(marker) map.removeLayer(marker);

        marker = L.marker([lat, lng], { icon: outageIcon })
            .addTo(map)
            .bindPopup("Your Location")
            .openPopup();

        map.setView([lat, lng], 16);

        try {
            const res = await fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
            );

            const data = await res.json();
            document.getElementById("location_name").value = data.display_name;

        } catch (e) {
            console.error("Geocoding failed", e);
        }

    });
}

/* ==============================
   CREATE REPORT
============================== */
document.getElementById("outageForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const payload = {
        location_name: document.getElementById("location_name").value,
        category: document.getElementById("category").value,
        severity: document.getElementById("severity").value,
        description: document.getElementById("description").value,
        affected_houses: document.getElementById("affected_houses").value,
        hazard_type: document.getElementById("hazard_type").value,
        started_at: null,
        image_proof: null,
        user_id: <?= $user['id'] ?> // IMPORTANT FIX
    };

    try {
        const res = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/create.php",
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

        document.getElementById("response").innerText = result.message;

        if(result.success){
            loadReports();
        }

    } catch (err) {
        console.error(err);
        document.getElementById("response").innerText = "Failed to submit report";
    }
});

/* ==============================
   LOAD MAP REPORTS (FIXED)
============================== */
async function loadReports(){

    try{

        const response = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/get.php"
        );

        const result = await response.json();

        if(!result.success){
            console.error("API Error:", result);
            return;
        }

        reportLayer.clearLayers();

        result.data.forEach(report => {

            if(report.latitude && report.longitude){

                const m = L.marker(
                    [report.latitude, report.longitude],
                    { icon: outageIcon }
                );

                m.bindPopup(`
                    <div style="width:250px;">

                        <h3>${report.location_name}</h3>

                        <hr>

                        <b>Category:</b> ${report.category}<br>
                        <b>Severity:</b> ${report.severity}<br>
                        <b>Status:</b> ${report.status}<br>
                        <b>Affected Houses:</b> ${report.affected_houses}<br>
                        <b>Active:</b> ${report.is_active}<br>
                        <b>Hazard:</b> ${report.hazard_type}<br>
                        <b>Started:</b> ${report.started_at ?? "Unknown"}<br>

                        <hr>

                        <b>Description:</b><br>
                        ${report.description}

                    </div>
                `);

                m.bindTooltip(
                    `<b>${report.location_name}</b><br>${report.description}`,
                    {
                        direction: "top",
                        offset: [0, -20],
                        opacity: 0.9,
                        sticky: true
                    }
                );

                reportLayer.addLayer(m);
            }

        });

    } catch(error){
        console.error("Load reports error:", error);
    }
}

loadReports();
setInterval(loadReports, 10000);

</script>

</body>
</html>