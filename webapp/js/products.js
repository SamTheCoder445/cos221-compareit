// document.addEventListener('DOMContentLoaded', () => {
//     const API_URL = "../php/api.php";



//     const brandSelect = document.getElementById('brand-filter');
//     const categorySelect = document.getElementById('category');
//     const priceSelect = document.getElementById('price-range');
//     const searchInput = document.querySelector('.search-bar input');
//     const searchButton = document.querySelector('.search-bar button');
//     const productGrid = document.querySelector('.product-grid');

//     const priceRanges = [
//         { label: "All Prices", value: "all" },
//         { label: "0 - 500 ZAR", value: "0-500" },
//         { label: "500 - 1000 ZAR", value: "500-1000" },
//         { label: "1000 - 5000 ZAR", value: "1000-5000" },
//         { label: "5000+ ZAR", value: "5000-Infinity" }
//     ];

//     // Populate price range dropdown
//     priceRanges.forEach(range => {
//         const option = document.createElement('option');
//         option.value = range.value;
//         option.textContent = range.label;
//         priceSelect.appendChild(option);
//     });

//     // Fetch and populate brands
//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetAllBrands' }),
//         headers: { 'Content-Type': 'application/json' }
//     })
//     .then(res => res.json())
//     .then(data => {
//         if (data.status === 'success') {
//             data.data.forEach(brand => {
//                 const option = document.createElement('option');
//                 option.value = brand.brand_id;
//                 option.textContent = brand.name;
//                 brandSelect.appendChild(option);
//             });
//         }
//     });

//     // Fetch and populate categories
//     fetch(API_URL, {
//         method: 'POST',
//         body: JSON.stringify({ type: 'GetAllCategories' }),
//         headers: { 'Content-Type': 'application/json' }
//     })
//     .then(res => res.json())
//     .then(data => {
//         if (data.status === 'success') {
//             data.data.forEach(category => {
//                 const option = document.createElement('option');
//                 option.value = category.category_id;
//                 option.textContent = category.name;
//                 categorySelect.appendChild(option);
//             });
//         }
//     });

//     // Load products with filters
//     function loadProducts() {
//         const brand_id = brandSelect.value;
// const category_id = categorySelect.value;
// const price = priceSelect.value;
// const search = searchInput.value.trim();
// const apiKey = getApiKey();  // ✅ Correct usage here

// let min_price = null;
// let max_price = null;

// if (price !== 'all') {
//     const [min, max] = price.split('-');
//     min_price = parseFloat(min);
//     max_price = max === 'Infinity' ? null : parseFloat(max);
// }

// const requestData = {
//     type: 'GetAllProducts',
//     apikey: apiKey,
//     ...(brand_id !== 'all' && { brand_id }),
//     ...(category_id !== 'all' && { category_id }),
//     ...(min_price !== null && { min_price }),
//     ...(max_price !== null && { max_price }),
//     ...(search && { search })
// };


//         fetch(API_URL, {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/json' },
//             body: JSON.stringify(requestData)
//         })
//         .then(res => res.json())
//         .then(data => {
//             productGrid.innerHTML = '';
//             if (data.status === 'success' && data.data.length > 0) {
//                 data.data.forEach(product => {
//                     const card = document.createElement('div');
//                     card.classList.add('product-card');

//                     card.innerHTML = `
//                         <div class="wishlist-icon">&#9825;</div>
//                         <img src="${product.thumbnail}" alt="${product.title}">
//                         <h3>${product.title}</h3>
//                         <p>Lowest Price: ${product.lowest_price ?? 'N/A'} ZAR ${product.retailer_name ? `@ ${product.retailer_name}` : ''}</p>
//                     `;

//                     const heart = card.querySelector('.wishlist-icon');
//                     heart.addEventListener('click', () => {
//                         heart.classList.toggle('wishlisted');
//                         // TODO: Add/remove wishlist logic
//                     });

//                     productGrid.appendChild(card);
//                 });
//             } else {
//                 productGrid.innerHTML = '<p>No products found.</p>';
//             }
//         });
//     }


//     function getApiKey() {
//     const auth = JSON.parse(sessionStorage.getItem('auth'));
//     if (auth && auth.api_key) {
//         return auth.api_key;
//     }
//     // Fallback guest key if user is not logged in
//     return 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756'; // <-- use your actual guest API key here
// }


//     // Event listeners
//     brandSelect.addEventListener('change', loadProducts);
//     categorySelect.addEventListener('change', loadProducts);
//     priceSelect.addEventListener('change', loadProducts);
//     searchButton.addEventListener('click', loadProducts);

//     // Initial load
//     loadProducts();
// });


document.addEventListener('DOMContentLoaded', () => {
    const API_URL = "../php/api.php";
let currentPage = 1;
const productsPerPage = 50;

    const brandSelect = document.getElementById('brand-filter');
    const categorySelect = document.getElementById('category');
    const priceSelect = document.getElementById('price-range');
    const sortSelect = document.getElementById('sort-by'); // <-- Get sort select element
    const searchInput = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');
    const productGrid = document.querySelector('.product-grid');
    const paginationContainer = document.querySelector('.pagination-controls');


    const priceRanges = [
        { label: "All Prices", value: "all" },
        { label: "0 - 500 ZAR", value: "0-500" },
        { label: "500 - 1000 ZAR", value: "500-1000" },
        { label: "1000 - 5000 ZAR", value: "1000-5000" },
        { label: "5000+ ZAR", value: "5000-Infinity" }
    ];

    // Populate price range dropdown
    priceRanges.forEach(range => {
        const option = document.createElement('option');
        option.value = range.value;
        option.textContent = range.label;
        priceSelect.appendChild(option);
    });

    // Fetch and populate brands
    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({ type: 'GetAllBrands' }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            data.data.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand.brand_id;
                option.textContent = brand.name;
                brandSelect.appendChild(option);
            });
        }
    });

    // Fetch and populate categories
    fetch(API_URL, {
        method: 'POST',
        body: JSON.stringify({ type: 'GetAllCategories' }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }
    });

    // Load products with filters + sorting
    // function loadProducts() {
    //     const brand_id = brandSelect.value;
    //     const category_id = categorySelect.value;
    //     const price = priceSelect.value;
    //     const sort = sortSelect.value;
    //     const search = searchInput.value.trim();
    //     const apiKey = getApiKey();

    //     let min_price = null;
    //     let max_price = null;

    //     if (price !== 'all') {
    //         const [min, max] = price.split('-');
    //         min_price = parseFloat(min);
    //         max_price = max === 'Infinity' ? null : parseFloat(max);
    //     }

    //     const requestData = {
    //         type: 'GetAllProducts',
    //         apikey: apiKey,
    //         ...(brand_id !== 'all' && { brand_id }),
    //         ...(category_id !== 'all' && { category_id }),
    //         ...(min_price !== null && { min_price }),
    //         ...(max_price !== null && { max_price }),
    //         ...(search && { search })
    //     };

    //     fetch(API_URL, {
    //         method: 'POST',
    //         headers: { 'Content-Type': 'application/json' },
    //         body: JSON.stringify(requestData)
    //     })
    //     .then(res => res.json())
    //     .then(data => {
    //         productGrid.innerHTML = '';
    //         if (data.status === 'success' && data.data.length > 0) {
    //             let products = [...data.data]; // Clone array before sorting

    //             // Sort based on selected option
    //             if (sort === 'price_asc') {
    //                 products.sort((a, b) => parseFloat(a.lowest_price || 0) - parseFloat(b.lowest_price || 0));
    //             } else if (sort === 'price_desc') {
    //                 products.sort((a, b) => parseFloat(b.lowest_price || 0) - parseFloat(a.lowest_price || 0));
    //             }

    //             products.forEach(product => {
    //                 const card = document.createElement('div');
    //                 card.classList.add('product-card');

    //                 card.innerHTML = `
    //                     <div class="wishlist-icon">&#9825;</div>
    //                     <img src="${product.thumbnail}" alt="${product.title}">
    //                     <h3>${product.title}</h3>
    //                     <p>Lowest Price: ${product.lowest_price ?? 'N/A'} ZAR ${product.retailer_name ? `@ ${product.retailer_name}` : ''}</p>
    //                 `;

    //                 const heart = card.querySelector('.wishlist-icon');
    //                 heart.addEventListener('click', () => {
    //                     heart.classList.toggle('wishlisted');
    //                     // TODO: Add/remove wishlist logic
    //                 });

    //                 productGrid.appendChild(card);
    //             });
    //         } else {
    //             productGrid.innerHTML = '<p>No products found.</p>';
    //         }
    //     });
    // }
function loadProducts() {
    const brand_id = brandSelect.value;
    const category_id = categorySelect.value;
    const price = priceSelect.value;
    const sort = sortSelect.value;
    const search = searchInput.value.trim();
    const apiKey = getApiKey();

    let min_price = null;
    let max_price = null;

    if (price !== 'all') {
        const [min, max] = price.split('-');
        min_price = parseFloat(min);
        max_price = max === 'Infinity' ? null : parseFloat(max);
    }

    const requestData = {
        type: 'GetAllProducts',
        apikey: apiKey,
        limit: productsPerPage,
        offset: (currentPage - 1) * productsPerPage,
        ...(brand_id !== 'all' && { brand_id }),
        ...(category_id !== 'all' && { category_id }),
        ...(min_price !== null && { min_price }),
        ...(max_price !== null && { max_price }),
        ...(search && { search })
    };

    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
    })
    .then(res => res.json())
    .then(data => {
        productGrid.innerHTML = '';
        paginationContainer.innerHTML = ''; // Clear previous pagination buttons

        if (data.status === 'success' && data.data.length > 0) {
            let products = [...data.data];

            if (sort === 'price_asc') {
                products.sort((a, b) => parseFloat(a.lowest_price || 0) - parseFloat(b.lowest_price || 0));
            } else if (sort === 'price_desc') {
                products.sort((a, b) => parseFloat(b.lowest_price || 0) - parseFloat(a.lowest_price || 0));
            }

            products.forEach(product => {
                const card = document.createElement('div');
                card.classList.add('product-card');

                card.innerHTML = `
                    <div class="wishlist-icon">&#9825;</div>
                      <a href="view.php?id=${product.product_id}">
                    <img src="${product.thumbnail}" alt="${product.title}">
                    <h3>${product.title}</h3>
                    <p>Lowest Price: ${product.lowest_price ?? 'N/A'} ZAR ${product.retailer_name ? `@ ${product.retailer_name}` : ''}</p>
                `;

                const heart = card.querySelector('.wishlist-icon');
                heart.addEventListener('click', () => {
                    heart.classList.toggle('wishlisted');
                });

                productGrid.appendChild(card);
            });

            // Add pagination buttons
            const totalPages = Math.ceil(data.total / productsPerPage);

            if (currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.textContent = 'Previous';
                prevBtn.addEventListener('click', () => {
                    currentPage--;
                    loadProducts();
                });
                paginationContainer.appendChild(prevBtn);
            }

            if (currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Next';
                nextBtn.addEventListener('click', () => {
                    currentPage++;
                    loadProducts();
                });
                paginationContainer.appendChild(nextBtn);
            }
        } else {
            productGrid.innerHTML = '<p>No products found.</p>';
        }
    });
}

    function getApiKey() {
        const auth = JSON.parse(sessionStorage.getItem('auth'));
        if (auth && auth.api_key) {
            return auth.api_key;
        }
        // Fallback guest key
        return 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756';
    }

    // Event listeners
    // brandSelect.addEventListener('change', loadProducts);
    // categorySelect.addEventListener('change', loadProducts);
    // priceSelect.addEventListener('change', loadProducts);
    // sortSelect.addEventListener('change', loadProducts); // <-- added for sorting
    // searchButton.addEventListener('click', loadProducts);

    brandSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
categorySelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
priceSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
sortSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
searchButton.addEventListener('click', () => { currentPage = 1; loadProducts(); });

    // Initial load
    loadProducts();
});
