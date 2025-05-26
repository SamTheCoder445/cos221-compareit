import { updateChart, pricesChart, wishlistsChart } from "./chart.js";

document.addEventListener('DOMContentLoaded', function(){
    let url = "http://localhost:8000/php/api.php";
    
    makeApiCall(url, {type: "GetDashboardData"});
    makeApiCall(url, {type: "GetDashboardGraphData"});

    function changeTopDashboardData(viewData){
        let ids = ["products", "reviews", "users", "retailers"];
        for(let id of ids){
            document.getElementById(id + "-count").textContent = viewData[id]["count"];
            document.getElementById(id+ "-growth").textContent = viewData[id]["growth"].toFixed(0) + "% in the last 30 days";
            if(viewData[id]["growth"] < 0){
                document.getElementById(id + "-growth").style.color = "red";
            }else if(viewData[id]["growth"] > 0){
                document.getElementById(id + "-growth").style.color = "lime";
            }
        }
    }
    function changeDashboardGraphData(viewData){
        updateChart(pricesChart, viewData['prices']['labels'], viewData['prices']['data_points'], "Average Price");
        updateChart(wishlistsChart, viewData['wishlists']['labels'], viewData['wishlists']['data_points'], "Average Wishlists Saves");
    }

    function makeApiCall(destUrl, req){
    fetch(destUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(req)
    })
    .then(response => response.json())
    .then(data => {
        switch (req.type) {
            case "GetDashboardData":
                changeTopDashboardData(data.data);
                break;
            case "GetDashboardGraphData":
                changeDashboardGraphData(data.data);
                break;
            default:
                break;
        }
    })
    .catch(error =>{
        console.log("Error: ", error);
    })
}
})

