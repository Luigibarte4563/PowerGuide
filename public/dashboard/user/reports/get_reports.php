<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Outage Reports</title>

<style>
body{
    font-family: Arial;
    padding: 20px;
    background: #f4f4f4;
}

/* CARD */
.card{
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* HEADER */
h2{
    margin-bottom: 15px;
}

/* PAGINATION */
.pagination{
    display: flex;
    gap: 5px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.page-btn{
    padding: 8px 12px;
    border: none;
    background: #ddd;
    cursor: pointer;
    border-radius: 5px;
}

.page-btn.active{
    background: #333;
    color: #fff;
}

.page-btn:hover{
    background: #999;
    color: #fff;
}

/* LOADING */
#loading{
    padding: 10px;
}
</style>
</head>

<body>

<h2>Outage Reports</h2>

<div id="list"></div>

<div class="pagination" id="pagination"></div>

<script>

let allData = [];
let currentPage = 1;
const perPage = 5;

/* =========================================
   FETCH DATA
========================================= */
async function loadData(){

    const list = document.getElementById("list");
    list.innerHTML = "<p id='loading'>Loading...</p>";

    try {

        const res = await fetch(
            "http://localhost/crowdsourcedapi/api/outage_report/get.php",
            {
                method: "GET",
                credentials: "include"
            }
        );

        const result = await res.json();

        if(!result || result.success === false){
            list.innerHTML = "<p>No data found</p>";
            return;
        }

        allData = Array.isArray(result.data) ? result.data : result;

        renderPage(currentPage);
        renderPagination();

    } catch(err){
        console.error(err);
        list.innerHTML = "<p>Error loading data</p>";
    }
}

/* =========================================
   RENDER PAGE
========================================= */
function renderPage(page){

    const list = document.getElementById("list");
    list.innerHTML = "";

    const start = (page - 1) * perPage;
    const end = start + perPage;

    const pageData = allData.slice(start, end);

    if(pageData.length === 0){
        list.innerHTML = "<p>No records</p>";
        return;
    }

    list.innerHTML = pageData.map(item => `
        <div class="card">
            <h3>${item.location_name}</h3>
            <p>${item.description || "No description"}</p>
            <small>
                Category: ${item.category} |
                Severity: ${item.severity} |
                Status: ${item.status}
            </small>
        </div>
    `).join("");
}

/* =========================================
   RENDER PAGINATION
========================================= */
function renderPagination(){

    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";

    const totalPages = Math.ceil(allData.length / perPage);

    for(let i = 1; i <= totalPages; i++){

        const btn = document.createElement("button");
        btn.innerText = i;
        btn.className = "page-btn";

        if(i === currentPage){
            btn.classList.add("active");
        }

        btn.onclick = () => {
            currentPage = i;
            renderPage(currentPage);
            renderPagination();
        };

        pagination.appendChild(btn);
    }
}

/* =========================================
   INIT
========================================= */
loadData();

</script>

</body>
</html>