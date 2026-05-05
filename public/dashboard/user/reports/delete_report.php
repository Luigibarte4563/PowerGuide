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

.card{
    border:1px solid #ccc;
    padding:12px;
    margin-bottom:10px;
    border-radius:8px;
}

button{
    width:100%;
    padding:10px;
    margin-top:10px;
    cursor:pointer;
    background:#e74c3c;
    color:#fff;
    border:none;
    border-radius:5px;
}

button:hover{
    background:#c0392b;
}
</style>
</head>

<body>

<h2>My Outage Reports</h2>

<div id="list"></div>

<script>

/* =========================================
   LOAD REPORTS
========================================= */
async function loadReports(){

    const list = document.getElementById("list");
    list.innerHTML = "<p>Loading...</p>";

    try {

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/outage_report/get_my_report.php",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const result = await res.json();

        if(!result.success){
            list.innerHTML = "<p>Failed to load reports</p>";
            return;
        }

        if(!result.data.length){
            list.innerHTML = "<p>No reports found</p>";
            return;
        }

        list.innerHTML = result.data.map(r => `
            <div class="card">

                <h3>${r.location_name}</h3>
                <p>${r.description}</p>

                <small>
                    ${r.category} | ${r.severity} | ${r.status}
                </small>

                <button onclick="deleteReport(${r.id})">
                    Delete Report
                </button>

            </div>
        `).join("");

    } catch(err){
        console.error(err);
        list.innerHTML = "<p>Server error</p>";
    }
}


/* =========================================
   DELETE REPORT (CALL PHP CURL BACKEND)
========================================= */
async function deleteReport(id){

    if(!confirm("Are you sure you want to delete this report?")){
        return;
    }

    try {

        const res = await fetch(
    "http://localhost/crowdsourcedapi/api/outage_report/delete.php",
    {
        method: "POST",
        credentials: "include",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ id: id })
    }
);

        const result = await res.json();

        console.log(result);

        alert(result.message ?? "No response message");

        if(result.success){
            loadReports();
        }

    } catch(err){
        console.error(err);
        alert("Delete failed");
    }
}


/* INIT */
loadReports();

</script>

</body>
</html>