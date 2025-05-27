


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


    // Initial load
// loadUserWishlist();
// setTimeout(loadProducts, 100); // wait briefly to sync wishlist before loading UI

loadUserWishlist().then(loadProducts);


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

    
    function showLoader() {
    document.getElementById('loader').style.display = 'flex';
}

function hideLoader() {
    document.getElementById('loader').style.display = 'none';
}


function loadProducts() {

        showLoader(); // <-- Show loader when starting fetch

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

    const isWishlisted = checkIfWishlisted(product.product_id); // New helper

    card.innerHTML = `
        <a href="view.php?product_id=${product.product_id}">
            <img src="${product.thumbnail}" alt="${product.title}">
            <h3 class="product-title">${product.title}</h3>
        </a>
        <p>Lowest Price: ${product.lowest_price ?? 'N/A'} ZAR ${product.retailer_name ? `@ ${product.retailer_name}` : ''}</p>
        <button class="wishlist-btn ${isWishlisted ? 'added' : ''}" data-product-id="${product.product_id}">
            ${isWishlisted ? '❤️ Wishlisted' : '🧡 Wishlist'}
        </button>
    `;

    productGrid.appendChild(card);

    const wishlistBtn = card.querySelector('.wishlist-btn');



wishlistBtn.addEventListener('click', () => {
    const productId = wishlistBtn.dataset.productId;
    const apiKey = getApiKey();

    if (isGuest(apiKey)) {
        alert("Please log in to use the wishlist.");
        return;
    }

    if (isAdmin()) {
        alert("Admins are not allowed to use the wishlist.");
        return;
    }

    if (wishlistBtn.classList.contains('added')) {
        removeFromWishlist(productId);
        wishlistBtn.classList.remove('added');
        wishlistBtn.textContent = '🧡 Wishlist';
    } else {
        addToWishlist(productId);
        wishlistBtn.classList.add('added');
        wishlistBtn.textContent = '❤️ Wishlisted';
    }
});






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

hideLoader(); // <-- Hide loader after response is handled



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



function getAuth() {
    return JSON.parse(sessionStorage.getItem('auth'));
}







function addToWishlist(productId) {
    const apiKey = getApiKey();

    if (isGuest(apiKey)) {
        alert("Please log in to add items to your wishlist.");
        return;
    }

    if (isAdmin()) {
        alert("Admins are not allowed to use the wishlist.");
        return;
    }

    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'AddToWishlist', product_id: productId, apikey: apiKey })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
            if (!wishlist.includes(productId)) {
                wishlist.push(productId);
                localStorage.setItem('wishlist', JSON.stringify(wishlist));
            }
        } else {
            alert(data.message || "Failed to add to wishlist.");
        }
    });
}



function removeFromWishlist(productId) {
    const apiKey = getApiKey();

    if (isGuest(apiKey) || isAdmin()) {
        return; // Guests and admins shouldn't be able to remove
    }

    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'RemoveFromWishlist', product_id: productId, apikey: apiKey })
    });

    let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    wishlist = wishlist.filter(id => id != productId);
    localStorage.setItem('wishlist', JSON.stringify(wishlist));
}


function checkIfWishlisted(productId) {
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    return wishlist.includes(productId);
}

function isGuest(apiKey) {
    return apiKey === 'b14561c4fc744210fe86c1eb1ab4a0663640ff12f75e6b7dfca9b4a37eca4756';
}


function isAdmin() {
    const auth = getAuth();
    return auth && auth.user_type === 'Admin';
}






function loadUserWishlist() {
    const apiKey = getApiKey();

    if (isGuest(apiKey) || isAdmin()) {
        localStorage.removeItem('wishlist'); // No local wishlist for guests/admins
        return Promise.resolve(); // ✅ Return a resolved promise
    }

    return fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ type: 'GetWishlist', apikey: apiKey })
    })
    .then(res => res.json())
    
.then(data => {
    const wishlistArray = data.data?.wishlist;
    if (data.status === 'success' && Array.isArray(wishlistArray)) {
        const wishlistIds = wishlistArray.map(item => item.product_id);
        localStorage.setItem('wishlist', JSON.stringify(wishlistIds));
    } else {
        console.warn("Unexpected wishlist response:", data);
    }
});


    
    
}


    
    brandSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
categorySelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
priceSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
sortSelect.addEventListener('change', () => { currentPage = 1; loadProducts(); });
searchButton.addEventListener('click', () => { currentPage = 1; loadProducts(); });

    // Initial load
    //loadProducts();
});