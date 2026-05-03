<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Reports</title>

<style>
body{
    font-family: Arial;
    padding:20px;
}

/* REPORT CARDS */
.card{
    border:1px solid #ccc;
    padding:12px;
    margin-bottom:10px;
    border-radius:8px;
}

/* MODAL */
#formOverlay{
    display:none;
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:#000000aa;
}

#formBox{
    background:#fff;
    width:450px;
    margin:5% auto;
    padding:20px;
    border-radius:10px;
}

/* INPUTS */
input, select, textarea{
    width:100%;
    padding:8px;
    margin-top:8px;
}

/* BUTTONS */
button{
    width:100%;
    padding:10px;
    margin-top:10px;
    cursor:pointer;
}
</style>
</head>

<body>

<h2>My Outage Reports</h2>

<!-- =========================================
     REPORT LIST
========================================= -->
<div id="list"></div>

<!-- =========================================
     EDIT MODAL
========================================= -->
<div id="formOverlay">

    <div id="formBox">

        <h3>Edit Report</h3>

        <!-- REQUIRED FIELDS -->
        <input type="hidden" id="id">

        <input type="text" id="location_name" placeholder="Location Name">

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
            <option value="moderate">Moderate</option>
            <option value="critical">Critical</option>
        </select>

        <textarea id="description" placeholder="Description"></textarea>

        <input type="number" id="affected_houses" placeholder="Affected Houses">

        <select id="status">
            <option value="unverified">Unverified</option>
            <option value="under_review">Under Review</option>
            <option value="verified">Verified</option>
            <option value="resolved">Resolved</option>
            <option value="fake_report">Fake Report</option>
        </select>

        <select id="is_active">
            <option value="yes">Yes</option>
            <option value="no">No</option>
            <option value="unknown">Unknown</option>
        </select>

        <select id="hazard_type">
            <option value="none">None</option>
            <option value="smoke">Smoke</option>
            <option value="sparks">Sparks</option>
            <option value="fire">Fire</option>
            <option value="fallen_wire">Fallen Wire</option>
            <option value="explosion_sound">Explosion Sound</option>
        </select>

        <!-- GPS -->
        <input type="hidden" id="latitude">
        <input type="hidden" id="longitude">

        <!-- ACTION BUTTONS -->
        <button type="button" onclick="useCurrentLocation()">
            Use Current Location
        </button>

        <button type="button" onclick="updateReport()">
            Update Report
        </button>

        <button type="button" onclick="closeForm()">
            Close
        </button>

    </div>

</div>

</body>
</html>
<script>

/* =========================================
   TRACK ORIGINAL LOCATION
========================================= */

let originalLocation = "";
let locationChanged = false;

/* =========================================
   LOAD REPORTS (FAST RENDER)
========================================= */

async function loadReports(){

    const list = document.getElementById("list");
    list.innerHTML = "<p>Loading...</p>";

    try {

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);

        const res = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/get_my_report.php",
            {
                method: "GET",
                credentials: "include",
                signal: controller.signal
            }
        );

        clearTimeout(timeout);

        const result = await res.json();

        if(!result.success){
            list.innerHTML = "<p>Failed to load reports</p>";
            return;
        }

        if(!result.data.length){
            list.innerHTML = "<p>No reports found</p>";
            return;
        }

        /* FAST DOM RENDER */
        list.innerHTML = result.data.map(r => {

            const safeData = JSON.stringify(r)
                .replace(/"/g, "&quot;");

            return `
                <div class="card">
                    <h3>${r.location_name}</h3>
                    <p>${r.description}</p>
                    <small>
                        ${r.category} | ${r.severity} | ${r.status}
                    </small>
                    <br>
                    <button onclick='editReport(${safeData})'>
                        Edit
                    </button>
                </div>
            `;

        }).join("");

    } catch(error){

        console.error(error);

        list.innerHTML = error.name === "AbortError"
            ? "<p>Request timed out</p>"
            : "<p>Server error</p>";
    }
}


/* =========================================
   OPEN EDIT FORM (SAFE BINDING)
========================================= */

function editReport(r){

    const set = (id, value) => {
        const el = document.getElementById(id);
        if(el) el.value = value ?? "";
    };

    set("id", r.id);
    set("location_name", r.location_name);
    set("category", r.category);
    set("severity", r.severity);
    set("description", r.description);
    set("affected_houses", r.affected_houses);
    set("status", r.status);
    set("is_active", r.is_active || "unknown");
    set("hazard_type", r.hazard_type || "none");
    set("latitude", r.latitude);
    set("longitude", r.longitude);

    originalLocation = r.location_name || "";
    locationChanged = false;

    document.getElementById("formOverlay").style.display = "block";
}


/* =========================================
   LOCATION CHANGE DETECTION (FIXED)
========================================= */

document.addEventListener("DOMContentLoaded", () => {

    const input = document.getElementById("location_name");

    if(input){

        input.addEventListener("input", () => {

            locationChanged =
                input.value.trim() !== originalLocation;
        });
    }
});


/* =========================================
   FAST GEOLOCATION (NON-BLOCKING UX)
========================================= */

async function useCurrentLocation(){

    navigator.geolocation.getCurrentPosition(

        async (pos) => {

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;

            locationChanged = true;

            const field = document.getElementById("location_name");

            /* SHOW COORDS INSTANTLY */
            field.value = `${lat}, ${lng}`;

            /* SKIP if user already manually edited AFTER original */
            if(field.value.trim() !== "" && field.value !== originalLocation){
                return;
            }

            /* BACKGROUND GEOCODING (NON-BLOCKING) */
            try {

                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 3000);

                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1`,
                    {
                        signal: controller.signal,
                        headers: { "Accept-Language": "en" }
                    }
                );

                clearTimeout(timeout);

                const data = await res.json();
                const a = data.address || {};

                const shortLocation = [
                    a.road,
                    a.suburb,
                    a.city || a.town || a.village,
                    a.state
                ].filter(Boolean).join(", ");

                field.value = shortLocation || data.display_name || `${lat}, ${lng}`;

            } catch(error){
                console.error(error);
            }
        },

        (error) => {
            console.error(error);
            alert("Failed to get location");
        },

        {
            enableHighAccuracy: false,
            timeout: 5000,
            maximumAge: 60000
        }
    );
}


/* =========================================
   CLOSE MODAL
========================================= */

function closeForm(){
    document.getElementById("formOverlay").style.display = "none";
}


/* =========================================
   UPDATE REPORT (OPTIMIZED PAYLOAD)
========================================= */

async function updateReport(){

    const payload = {
        id: document.getElementById("id").value,
        category: document.getElementById("category").value,
        severity: document.getElementById("severity").value,
        description: document.getElementById("description").value,
        affected_houses: document.getElementById("affected_houses").value,
        status: document.getElementById("status").value,
        is_active: document.getElementById("is_active").value,
        hazard_type: document.getElementById("hazard_type").value
    };

    /* SEND LOCATION ONLY IF CHANGED */
    if(locationChanged){
        payload.location_name = document.getElementById("location_name").value;
        payload.latitude = document.getElementById("latitude").value || null;
        payload.longitude = document.getElementById("longitude").value || null;
    }

    try {

        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 8000);

        const res = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/update.php",
            {
                method: "POST",
                credentials: "include",
                signal: controller.signal,
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            }
        );

        clearTimeout(timeout);

        const result = await res.json();

        alert(result.message);

        if(result.success){
            closeForm();
            loadReports();
        }

    } catch(error){

        console.error(error);

        alert(error.name === "AbortError"
            ? "Request timed out"
            : "Update failed"
        );
    }
}


/* =========================================
   INIT
========================================= */

loadReports();

</script>