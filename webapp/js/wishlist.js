document.addEventListener('DOMContentLoaded', () => {
  const wishlistContainer = document.getElementById('wishlistItems');
  const auth = getAuth();

  if (!auth || auth.user_type !== 'Customer') {
    wishlistContainer.innerHTML = `<p>Your wishlist is empty or you're not logged in as a customer.</p>`;
    return;
  }

  fetchWishlist(auth.api_key);

  function getAuth() {
    try {
      const stored = sessionStorage.getItem('auth');
      return stored ? JSON.parse(stored) : null;
    } catch (e) {
      return null;
    }
  }

  async function fetchWishlist(apiKey) {
    try {
      const res = await fetch('../php/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type: 'GetWishlist',
          apikey: apiKey
        })
      });

      const response = await res.json();

      if (response.status === 'success' && Array.isArray(response.data.wishlist)) {
        const items = response.data.wishlist;

        if (items.length === 0) {
          wishlistContainer.innerHTML = `<p>Your wishlist is currently empty.</p>`;
        } else {
          items.forEach(renderWishlistItem);
        }
      } else {
        wishlistContainer.innerHTML = `<p>Failed to load wishlist.</p>`;
      }
    } catch (error) {
      wishlistContainer.innerHTML = `<p>Error loading wishlist: ${error.message}</p>`;
    }
  }

//   function renderWishlistItem(product) {
//     const item = document.createElement('div');
//     item.className = 'wishlist-item';

//     item.innerHTML = `
//       <img src="${product.thumbnail}" alt="${product.title}">
//       <div class="title">
//         <a href="view.php?id=${product.product_id}">${product.title}</a>
//       </div>
//       <button class="remove-btn" data-id="${product.product_id}">Remove</button>
//     `;

//     const removeBtn = item.querySelector('.remove-btn');
//     removeBtn.addEventListener('click', () => {
//       removeFromWishlist(auth.api_key, product.product_id, item);
//     });

//     wishlistContainer.appendChild(item);
//   }


function renderWishlistItem(product) {
  const item = document.createElement('div');
  item.className = 'wishlist-item';

  item.innerHTML = `
    <a href="view.php?product_id=${product.product_id}" class="wishlist-link">
     


      <img src="${product.thumbnail}" alt="${product.title}">
      <div class="title">${product.title}</div>
    </a>
    <button class="remove-btn" data-id="${product.product_id}">Remove</button>
  `;

  const removeBtn = item.querySelector('.remove-btn');
  removeBtn.addEventListener('click', (e) => {
    e.preventDefault(); // Prevent accidental navigation
    removeFromWishlist(auth.api_key, product.product_id, item);
  });

  wishlistContainer.appendChild(item);
}


  async function removeFromWishlist(apiKey, productId, element) {
    try {
      const res = await fetch('../php/api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type: 'RemoveFromWishlist',
          apikey: apiKey,
          product_id: parseInt(productId)
        })
      });

      const data = await res.json();

      if (data.status === 'success') {
        element.remove();
        if (wishlistContainer.children.length === 0) {
          wishlistContainer.innerHTML = `<p>Your wishlist is now empty.</p>`;
        }
      } else {
        alert('Failed to remove product from wishlist.');
      }
    } catch (error) {
      alert('Error removing product: ' + error.message);
    }
  }
});
