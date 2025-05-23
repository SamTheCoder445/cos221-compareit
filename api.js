
console.log("Hi there");
const API_KEY = "5d732ddb5bc491f7e408b494cd90fbb8";
const studentnum = "u23533413";
const BASE_URL = "https://wheatley.cs.up.ac.za/api/";

//

let products = []; // array to hold producyts JSON
let exchangerates = [];
let brands = [];
let departments = [];
let countries = [];
let currentCurrency = "ZAR";

function showLoading() {
  if (loader) loader.style.display = "block";
}
function hideLoading() {
  if (loader) loader.style.display = "none";
}
//JSON OBJECT
const productsAPI = {
  studentnum: "u23533413",
  apikey: "5d732ddb5bc491f7e408b494cd90fbb8",
  type: "GetAllProducts",
  sort: "title",
  order: "ASC",
  return: ["brand", "title", "image_url", "department", "final_price"],
  limit: 100,
  search: {
    department: "Electronics",
  },
};
const currencyAPI = {
  studentnum: "u23533413",
  apikey: "5d732ddb5bc491f7e408b494cd90fbb8",
  type: "GetCurrencyList",
};

//code to fetch daat
function loadProducts() {
  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", "https://wheatley.cs.up.ac.za/api/", true);
  xhttp.setRequestHeader("Content-Type", "application/json");

  // Updated payload with Electronics filter
  const payload = {
    ...productsAPI, // Spread existing properties
    filters: { department: "Electronics" }, // Force electronics filter
  };

  xhttp.onreadystatechange = function () {
    if (xhttp.readyState === 4 && xhttp.status === 200) {
      products = JSON.parse(xhttp.responseText).data;
      displayProducts(products); // Display only electronics
    }
  };

  xhttp.send(JSON.stringify(payload));
}
loadProducts();

function displayProducts(products) {
  console.log("hi");

  const container = document.getElementById("products-gallery-grid");
  container.innerHTML = "";
  if (products.length === 0) {
    const noResultsMessage = document.createElement("p");
    noResultsMessage.textContent = "No products found.";
    container.appendChild(noResultsMessage);
    return;
  }

  products.forEach((product) => {
    const productCard = document.createElement("div");
    productCard.classList.add("product-card");

    //create iimafge
    const img = document.createElement("img");
    img.src = product.image_url;
    img.alt = product.title;

    //title\
    const title = document.createElement("h5");
    title.textContent = product.title;

    //price
    const price = document.createElement("p");
    console.log("PRICE BEFORE CONVERT", product.final_price, currentCurrency);

    const convertedPrice = convertPrice(product.final_price, currentCurrency);
    console.log("PRICE AFTER CONVERT", convertedPrice, currentCurrency);
    price.textContent = `Price: ${currentCurrency} ${convertedPrice}`;

    //append
    productCard.appendChild(img);
    productCard.appendChild(title);
    productCard.appendChild(price);

    //append to container
    container.appendChild(productCard);
    console.log("done");
  });
}

function loadExchangeRates() {
  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", BASE_URL, true);
  xhttp.setRequestHeader("Content-Type", "application/json");

  xhttp.onreadystatechange = function () {
    if (xhttp.readyState === 4 && xhttp.status === 200) {
      console.log("success dude");
      const response = JSON.parse(xhttp.responseText);
      exchangerates = response.data;
      //populateCurrencyDropdown(); //populate dropdown function
      console.log("ECHANGE RATES INITAILLY HERE");
      console.log(exchangerates);
      populateCurrencyDropdown();
      deals();
      loadProducts();
      //applyCurrencyConversion();
    }
  };
  xhttp.send(JSON.stringify(currencyAPI));
}
function convertPrice(price, currency) {
  if (!exchangerates[currency]) {
    console.log("EXCHANGE RATE IS ACTUALLY EMPTY");
    return price;
    //console.log("EXCHANGE RATE IS ACTUALLY EMPTY");
    // return original if no exchange rate found
  }
  console.log("EXCHANGE RATE IS NOT ACTUALLY EMPTY", currentCurrency);
  //nsole.log(price * exchangerates[currency].toFixed(2));
  return price * exchangerates[currency].toFixed(2);
}
function searchProducts() {
  const searchVal = document
    .getElementById("search-bar")
    .value.trim()
    .toLowerCase();
  searchVal.innerHTML = "";

  if (searchVal.value === "") {
    console.log("attemot");
    displayProducts(products);
    return;
  } else {
    console.log(products.data);
    const filteredproducts = products.filter((product) =>
      product.title.toLowerCase().includes(searchVal)
    );
    //dipslay filtered
    displayProducts(filteredproducts);
  }
}
document.getElementById("search-bar").addEventListener("input", searchProducts);

function applyFilters() {
  const currencychoiice = document.getElementById("currency-selector");
  currentCurrency = currencychoiice.value;
  const sortOption = document.getElementById("sort-dropdown").value;
  let sortField = "final_price"; // Sorting based on price
  let sortOrder = "ASC"; // Default to ascending order

  if (sortOption === "price-high-low") {
    sortOrder = "DESC";
  }
  //for filter options
  const minPrice = document.getElementById("min-price").value;
  const maxPrice = document.getElementById("max-price").value;
  const category = document.getElementById("category-dropdown").value;
  const country = document.getElementById("country-dropdown").value;
  const brand = document.getElementById("brand-dropdown").value;
  //

  const updatedProductsAPI = {
    studentnum: studentnum,
    apikey: API_KEY,
    type: "GetAllProducts",
    sort: sortField,
    order: sortOrder,
    return: ["brand", "title", "image_url", "department", "final_price"],
  };
  const filters = {
    studentnum: studentnum,
    apikey: API_KEY,
    type: "GetAllProducts",
    sort: sortField,
    order: sortOrder,
    return: ["brand", "title", "image_url", "department", "final_price"],
    filters: {},
  };
  if (minPrice && maxPrice) {
    filters.filters.final_price = {
      $gte: parseFloat(minPrice),
      $lte: parseFloat(maxPrice),
    };
  }
  if (category) {
    filters.filters.category = category;
  }
  if (country) {
    filters.filters.country_of_origin = country;
  }
  if (brand) {
    filters.filters.brand = brand;
  }

  //showLoading();

  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", BASE_URL, true);
  xhttp.setRequestHeader("Content-Type", "application/json");

  xhttp.onreadystatechange = function () {
    if (xhttp.readyState === 4) {
      //hideLoading();
      if (xhttp.status === 200) {
        console.log("Sorted products fetched successfully!");
        products = JSON.parse(xhttp.responseText).data;
        displayProducts(products);
      } else {
        console.error("Error fetching sorted products:", xhttp.status);
      }
    }
  };

  xhttp.send(JSON.stringify(updatedProductsAPI));

  loadExchangeRates();
}
function populateCurrencyDropdown() {
  const currencychoice = document.getElementById("currency-selector");
  //
  currencychoice.innerHTML = ""; //clear
  //initially ZAR
  if (currentCurrency === "") {
    currentCurrency = "ZAR";
  }
  //
  for (const currency in exchangerates) {
    const option = document.createElement("option");
    //option.value = currentCurrency
    option.value = currency;
    option.textContent = currency;

    if (currency === currentCurrency) {
      option.selected = true;
      //   currentCurrency = "ZAR";
    }
    currencychoice.appendChild(option);
  }

  currencychoice.addEventListener("change", applyFilters);
  //currentCurrency = currencychoice.value;
  //return currency;
}
// 2. Listen for changes to the currency dropdown
document
  .getElementById("currency-selector")
  .addEventListener("change", function (event) {
    currentCurrency = event.target.value; // Update the selected currency
    console.log("Selected currency:", currentCurrency);
    // You can store the selected currency in localStorage to persist it across page reloads
    localStorage.setItem("selectedCurrency", currentCurrency);

    // Optionally, you can trigger an update to other parts of your app where currency is needed
    updateCurrencyDependentUI();
  });
function loadCurrencyFromLocalStorage() {
  const storedCurrency = localStorage.getItem("selectedCurrency");
  if (storedCurrency) {
    currentCurrency = storedCurrency; // Use the stored currency if it exists
  }
  populateCurrencyDropdown();
}
function updateCurrencyDependentUI() {
  console.log("Updating UI based on currency:", currentCurrency);
  // Any UI updates that depend on the selected currency can go here
}

// Initialize the page by loading the currency and setting up listeners
function init() {
  loadCurrencyFromLocalStorage();
  fetchFilterOptions();
}

// Run the init function on page load
window.onload = init;
// Helper function to populate dropdowns dynamically
function populateDropdown(dropdownId, options) {
  const dropdown = document.getElementById(dropdownId);
  dropdown.innerHTML = '<option value="">All</option>'; // Default option

  options.forEach((option) => {
    const optionElement = document.createElement("option");
    optionElement.value = option;
    optionElement.textContent = option;
    dropdown.appendChild(optionElement);
  });
}
//filteroptions
function fetchFilterOptions() {
  const filtersRequest = {
    studentnum: studentnum,
    apikey: API_KEY,
    type: "GetAllProducts",
    return: ["brand", "department", "country_of_origin"],
  };

  //showLoading();

  const xhttp = new XMLHttpRequest();
  xhttp.open("POST", BASE_URL, true);
  xhttp.setRequestHeader("Content-Type", "application/json");

  xhttp.onreadystatechange = function () {
    if (xhttp.readyState === 4) {
      //hideLoading();
      if (xhttp.status === 200) {
        console.log("Filter options fetched successfully!");
        const response = JSON.parse(xhttp.responseText);
        brands = response.brand;
        departments = response.departments;
        countries = response.country_of_origin;
        console.log(brands);

        populateDropdown("category-dropdown", departments);
        populateDropdown("brand-dropdown", brands);
        populateDropdown("country-dropdown", countries);
      } else {
        console.error("Error fetching filter options:", xhttp.status);
      }
    }
  };

  xhttp.send(JSON.stringify(productsAPI));
}

loadExchangeRates();
//deals.html
function deals() {
  const dealsContainer = document.getElementById("deals-container");

  // Mock product data (Replace with API call if available)

  // Function to calculate discount percentage
  function calculateDiscount(initialPrice, finalPrice) {
    return Math.round(((initialPrice - finalPrice) / initialPrice) * 100);
  }

  // Filter and display products with at least a 10% discount
  products.forEach((product) => {
    const discount = calculateDiscount(
      product.initialPrice,
      product.finalPrice
    );

    if (discount >= 10) {
      const productHTML = `
                <div class="deals_item">
                    <a href="view.html"><img src="${product.img}" alt="${product.name}"></a>
                    <p>${product.name} <del> R${product.initialPrice}</del> 
                        <strong> R${product.finalPrice} (${discount}% OFF!) </strong>
                    </p>
                    <p><i class="fa-solid fa-cart-shopping"></i> Add to cart</p>
                    <p><i class="fa-solid fa-heart"></i> Add to wishlist</p>
                </div>
            `;
      dealsContainer.innerHTML += productHTML;
    }
  });
}
