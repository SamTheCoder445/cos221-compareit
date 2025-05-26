// const API_URL = "../php/api.php";

// function getApiKey() {
//     const auth = JSON.parse(sessionStorage.getItem('auth'));
//     if (auth && auth.api_key) {
//         return auth.api_key;
//     }
//     return 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756'; // Guest key
// }

// document.addEventListener("DOMContentLoaded", () => {
//     const productId = new URLSearchParams(window.location.search).get('product_id');
//     if (!productId) return alert("Product ID missing");

//     const titleEl = document.getElementById("product-title");
//     const ratingEl = document.getElementById("product-rating");
//     const brandStockEl = document.getElementById("product-brand-stock");
//     const detailsEl = document.getElementById("product-details");
//     const priceTbody = document.querySelector("#price-table tbody");
//     const reviewContent = document.getElementById("review-content");

//     // ----- 1. Product Details -----
//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetAllProducts', apikey: getApiKey() })
//     })
//     .then(res => res.json())
//     .then(data => {
//         const product = data.products.find(p => p.product_id == productId);
//         if (!product) return;

//         titleEl.textContent = product.product_name;
//         brandStockEl.textContent = `by ${product.brand} [${product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock'}]`;

//         detailsEl.innerHTML = `
//             <tr><td>Category:</td><td>${product.category}</td></tr>
//             <tr><td>Brand:</td><td>${product.brand}</td></tr>
//             <tr><td>SKU:</td><td>${product.sku}</td></tr>
//             <tr><td>Warranty:</td><td>${product.warranty}</td></tr>
//             <tr><td>Shipping:</td><td>${product.shipping_details}</td></tr>
//             <tr><td>Min Qty:</td><td>${product.minimum_quantity}</td></tr>
//             <tr><td>Return Policy:</td><td>${product.return_policy}</td></tr>
//             <tr><td>Weight:</td><td>${product.weight}kg</td></tr>
//             <tr><td>Dimensions:</td><td>${product.dimensions}</td></tr>
//         `;
//     });

//     // ----- 2. Product Images -----
//     let images = [], imgIndex = 0;
//     const imgEl = document.getElementById("carousel-img");
//     document.getElementById("prev-img").onclick = () => showImage(imgIndex - 1);
//     document.getElementById("next-img").onclick = () => showImage(imgIndex + 1);

//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetProductImages', product_id: productId, apikey: getApiKey() })
//     })
//     .then(res => res.json())
//     .then(data => {
//         images = data.images || [];
//         showImage(0);
//     });

//     function showImage(i) {
//         if (images.length === 0) return;
//         imgIndex = (i + images.length) % images.length;
//         imgEl.src = images[imgIndex].image_url;
//     }

//     // ----- 3. Prices -----
//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetAllPrices', product_id: productId, apikey: getApiKey() })
//     })
//     .then(res => res.json())
//     .then(data => {
//         priceTbody.innerHTML = data.prices.map(price => `
//             <tr>
//                 <td>${price.retailer_name}</td>
//                 <td>R${parseFloat(price.price).toFixed(2)}</td>
//                 <td><a href="${price.website}" target="_blank">Buy Now</a></td>
//             </tr>
//         `).join('');
//     });

//     // ----- 4. Reviews -----
//     let reviews = [], reviewIndex = 0;
//     document.getElementById("prev-review").onclick = () => showReview(reviewIndex - 1);
//     document.getElementById("next-review").onclick = () => showReview(reviewIndex + 1);

//     function showReview(i) {
//         if (reviews.length === 0) {
//             reviewContent.innerHTML = "No reviews yet.";
//             return;
//         }
//         reviewIndex = (i + reviews.length) % reviews.length;
//         const r = reviews[reviewIndex];
//         reviewContent.innerHTML = `⭐ ${r.review_rating} – "${r.comment}" – ${r.reviewer_name} (${r.review_date})`;
//     }

//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetAllReviews', product_id: productId, apikey: getApiKey() })
//     })
//     .then(res => res.json())
//     .then(data => {
//         reviews = data.reviews || [];
//         ratingEl.innerHTML = `★★★★☆ (${parseFloat(data.average_rating).toFixed(1)})`;
//         showReview(0);
//     });

//     // ----- 5. Submit Review -----
//     document.getElementById("submit-review").onclick = () => {
//         const rating = document.getElementById("review-rating").value;
//         const comment = document.getElementById("review-comment").value;

//         if (!rating || !comment.trim()) {
//             return alert("Please select a rating and write a comment.");
//         }

//         fetch(API_URL, {
//             method: 'POST',
//             body: JSON.stringify({
//                 type: 'AddReview',
//                 product_id: productId,
//                 review_rating: parseInt(rating),
//                 comment,
//                 apikey: getApiKey()
//             })
//         })
//         .then(res => res.json())
//         .then(data => {
//             if (data.status === 'success') {
//                 alert('Review added!');
//                 location.reload();
//             } else {
//                 alert(data.message || 'Failed to add review.');
//             }
//         });
//     };
// });

const API_URL = "../php/api.php";

function getApiKey() {
    const auth = JSON.parse(sessionStorage.getItem('auth'));
    if (auth && auth.api_key) {
        return auth.api_key;
    }
    return 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756'; // Guest key
}

document.addEventListener("DOMContentLoaded", () => {
    const productId = new URLSearchParams(window.location.search).get('product_id');
    if (!productId) return alert("Product ID missing");

    const titleEl = document.getElementById("product-title");
    const ratingEl = document.getElementById("product-rating");
    const brandStockEl = document.getElementById("product-brand-stock");
    const detailsEl = document.getElementById("product-details");
    const priceTbody = document.querySelector("#price-table tbody");
    const reviewContent = document.getElementById("review-content");
    const descriptionEl = document.getElementById("product-description");

    // ----- 1. Product Details -----
    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
            type: 'GetAllProducts',
            apikey: getApiKey(),
            product_id: productId
        })
    })
    .then(res => res.json())
    .then(data => {
        const product = data.product;
        if (!product) return alert("Product not found");

        titleEl.textContent = product.title;
        descriptionEl.textContent = product.description;
        

        brandStockEl.textContent = `by ${product.brand} [${product.availability_status}]`;

        detailsEl.innerHTML = `
            <tr><td>Category:</td><td>${product.category}</td></tr>
            <tr><td>Brand:</td><td>${product.brand}</td></tr>
            <tr><td>Availability:</td><td>${product.availability_status}</td></tr>
            <tr><td>Lowest Price:</td><td>R${parseFloat(product.lowest_price).toFixed(2)} @ ${product.retailer_name}</td></tr>
        `;
    });

    // ----- 2. Product Images -----
    let images = [], imgIndex = 0;
    const imgEl = document.getElementById("carousel-img");
    document.getElementById("prev-img").onclick = () => showImage(imgIndex - 1);
    document.getElementById("next-img").onclick = () => showImage(imgIndex + 1);

    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
            type: 'GetProductImages',
            product_id: productId
        })
    })
    .then(res => res.json())
    .then(data => {
        images = data.images || [];
        showImage(0);
    });

    function showImage(i) {
        if (images.length === 0) return;
        imgIndex = (i + images.length) % images.length;
        imgEl.src = images[imgIndex];
    }

    // ----- 3. Prices -----
    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
            type: 'GetAllPrices',
            product_id: productId,
            apikey: getApiKey()
        })
    })
    .then(res => res.json())
    .then(data => {
        priceTbody.innerHTML = data.prices.map(price => `
            <tr>
                <td>${price.retailer_name}</td>
                <td>R${parseFloat(price.price).toFixed(2)}</td>
                <td><a href="${price.website}" target="_blank">Buy Now</a></td>
            </tr>
        `).join('');
    });

    // ----- 4. Reviews -----
    let reviews = [], reviewIndex = 0;
    document.getElementById("prev-review").onclick = () => showReview(reviewIndex - 1);
    document.getElementById("next-review").onclick = () => showReview(reviewIndex + 1);

    function showReview(i) {
        if (reviews.length === 0) {
            reviewContent.innerHTML = "No reviews yet.";
            return;
        }
        reviewIndex = (i + reviews.length) % reviews.length;
        const r = reviews[reviewIndex];
        reviewContent.innerHTML = `⭐ ${r.review_rating} – "${r.comment}" – ${r.reviewer_name} (${r.review_date})`;
    }

    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
            type: 'GetAllReviews',
            product_id: productId,
            apikey: getApiKey()
        })
    })
    .then(res => res.json())
    .then(data => {
        reviews = data.reviews || [];
        ratingEl.innerHTML = `★★★★☆ (${parseFloat(data.average_rating).toFixed(1)})`;
        showReview(0);
    });

    // ----- 5. Submit Review -----
    // document.getElementById("submit-review").onclick = () => {
    //     const auth = JSON.parse(sessionStorage.getItem('auth'));
    //     const rating = document.getElementById("review-rating").value;
    //     const comment = document.getElementById("review-comment").value;


    //      if (!auth || !auth.api_key || auth.role !== "Customer") {
    //     return alert("Only registered customers can submit reviews.");
    // }
    //     if (!rating || !comment.trim()) {
    //         return alert("Please select a rating and write a comment.");
    //     }

    //     fetch(API_URL, {
    //         method: 'POST',
    //         body: JSON.stringify({
    //             type: 'AddReview',
    //             product_id: productId,
    //             review_rating: parseInt(rating),
    //             comment,
    //             apikey: getApiKey()
    //         })
    //     })
    //     .then(res => res.json())
    //     .then(data => {
    //         if (data.status === 'success') {
    //             alert('Review added!');
    //             location.reload();
    //         } else {
    //             alert(data.message || 'Failed to add review.');
    //         }
    //     });
  
    // };




// ----- 5. Submit Review -----
document.getElementById("submit-review").onclick = () => {
    const auth = JSON.parse(sessionStorage.getItem('auth'));
    const guestKey = 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756';

    const rating = document.getElementById("review-rating").value;
    const comment = document.getElementById("review-comment").value;

    const apikey = auth?.api_key || guestKey;

    // Block the guest user from submitting reviews
    if (apikey === guestKey) {
        return alert("Guests are not allowed to submit reviews. Please log in.");
    }

    if (!rating || !comment.trim()) {
        return alert("Please select a rating and write a comment.");
    }

    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({
            type: 'AddReview',
            product_id: productId,
            review_rating: parseInt(rating),
            comment,
            apikey
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Review added!');
            location.reload();
        } else {
            alert(data.message || 'Failed to add review.');
        }
    });
};










});
