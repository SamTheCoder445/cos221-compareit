document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const errorMessage = document.getElementById('error-message');
    const API_URL = "../php/api.php"; // Adjust path as needed

    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        errorMessage.textContent = '';
        errorMessage.style.display = 'none';

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        try {
            // Basic client-side validation
            if (!email || !password) {
                throw new Error('Email and password are required');
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                throw new Error('Please enter a valid email address');
            }

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: 'Login',
                    email: email,
                    password: password
                })
            });

            const data = await response.json();

            // if (data.status === 'success') {
            //     // Store user data in sessionStorage
            //     sessionStorage.setItem('auth', JSON.stringify({
            //         api_key: data.data.api_key,
            //         user_id: data.data.user_id,
            //         name: data.data.name,
            //         user_type: data.data.user_type
            //     }));

            //     // Redirect to products page
            //     window.location.href = 'products.php';
            // } 
            
            if (data.status === 'success') {
    const auth = {
        api_key: data.data.api_key,
        user_id: data.data.user_id,
        name: data.data.name,
        user_type: data.data.user_type
    };

    sessionStorage.setItem('auth', JSON.stringify(auth));

    // Sync with PHP session
    await fetch('../php/sync_session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ api_key: auth.api_key })
    });

    // Now redirect to products
    // window.location.href = 'products.php';
    window.location.replace('products.php');

}

            
            
            
            
            
            
            
            else {
                throw new Error(data.message || 'Invalid email or password');
            }
        } catch (error) {
            console.error('Login Error:', error);
            errorMessage.textContent = error.message;
            errorMessage.style.display = 'block';
        }
    });
});