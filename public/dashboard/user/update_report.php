<?php
session_start();
$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    echo "<h3>Unauthorized. Please login first.</h3>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Outage Reports</title>

    <style>
        body { font-family: Arial; padding: 20px; }

        #formOverlay {
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:#000000aa;
        }

        #formOverlay > div {
            background:#fff;
            width:400px;
            margin:5% auto;
            padding:20px;
            border-radius:10px;
        }

        input, select, textarea {
            width:100%;
            padding:8px;
            margin-top:8px;
        }

        button {
            margin-top:10px;
            padding:10px;
            width:100%;
            cursor:pointer;
        }

        .card {
            border:1px solid #ccc;
            padding:10px;
            margin-bottom:10px;
            border-radius:8px;
        }
    </style>
</head>

<body>

<h2>My Outage Reports</h2>

<div id="list"></div>

<!-- =========================
     UPDATE MODAL
========================= -->
<div id="formOverlay">
    <div>

        <h3>Update Report</h3>

        <input type="hidden" id="id">

        <input type="text" id="location_name" placeholder="Location">

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

        <textarea id="description"></textarea>

        <input type="number" id="affected_houses" min="1">

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

        <button onclick="updateReport()">Save Changes</button>
        <button onclick="closeForm()">Cancel</button>

    </div>
</div>

<script>

/* ==============================
   LOAD ONLY MY REPORTS
============================== */
async function loadReports(){

    try {

        const res = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/get_my_report.php",
            {
                method: "GET",
                credentials: "include" // 🔥 SESSION REQUIRED
            }
        );

        const result = await res.json();

        const list = document.getElementById("list");

        if (!result.success) {
            list.innerHTML = "No reports found or unauthorized.";
            return;
        }

        list.innerHTML = "";

        result.data.forEach(r => {

            list.innerHTML += `
                <div class="card">
                    <h3>${r.location_name}</h3>
                    <p>${r.description}</p>

                    <small>
                        ${r.category} | ${r.severity} | ${r.status}
                    </small>

                    <br><br>

                    <button onclick='editReport(${JSON.stringify(r).replace(/'/g, "&apos;")})'>
                        Edit
                    </button>
                </div>
            `;
        });

    } catch (error) {
        console.error(error);
        document.getElementById("list").innerHTML = "Server error.";
    }
}

/* ==============================
   OPEN EDIT FORM
============================== */
function editReport(r){

    document.getElementById("formOverlay").style.display = "block";

    document.getElementById("id").value = r.id;
    document.getElementById("location_name").value = r.location_name;
    document.getElementById("category").value = r.category;
    document.getElementById("severity").value = r.severity;
    document.getElementById("description").value = r.description;
    document.getElementById("affected_houses").value = r.affected_houses;
    document.getElementById("status").value = r.status;
    document.getElementById("is_active").value = r.is_active;
    document.getElementById("hazard_type").value = r.hazard_type;
}

/* ==============================
   CLOSE FORM
============================== */
function closeForm(){
    document.getElementById("formOverlay").style.display = "none";
}

/* ==============================
   UPDATE REPORT
============================== */
async function updateReport(){

    const payload = {
        id: document.getElementById("id").value,
        location_name: document.getElementById("location_name").value,
        category: document.getElementById("category").value,
        severity: document.getElementById("severity").value,
        description: document.getElementById("description").value,
        affected_houses: document.getElementById("affected_houses").value,
        status: document.getElementById("status").value,
        is_active: document.getElementById("is_active").value,
        hazard_type: document.getElementById("hazard_type").value
    };

    try {

        const res = await fetch(
            "http://localhost/crowdsourced-outage-reporting-api/api/outage_report/update_report.php",
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

        if (result.success) {
            closeForm();
            loadReports();
        }

    } catch (error) {
        console.error(error);
        alert("Update failed");
    }
}

/* INIT */
loadReports();

</script>

</body>
</html>