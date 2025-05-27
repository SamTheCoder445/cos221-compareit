document.addEventListener('DOMContentLoaded', function(){
    let searchBtn = this.getElementById("product-search-btn");
    searchBtn.addEventListener('click', function(e){
        e.preventDefault();
        let url = "http://localhost:8000/php/api.php";
        let productId =  document.getElementById('productIdSearch').innerText;

        makeApiCall(url, {type: "GetProductByID", "product_id": productId});
    })

    function showProductById(viewData){
        document.getElementById("productName").textContent = viewData['title'];
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
        if(req.type === "GetProductById"){
            showProductById(data.data);
        }
    })
    .catch(error =>{
        console.log("Error: ", error);
    })
    }
});