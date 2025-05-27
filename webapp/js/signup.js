document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    const API_URL = "../php/api.php"; // Using local API endpoint

    // Password validation
    function validatePassword(password) {
        const minLength = password.length >= 8;
        const hasUpper = /[A-Z]/.test(password);
        const hasLower = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

        // Update visual indicators
        document.getElementById('length-req').classList.toggle('valid', minLength);
        document.getElementById('upper-req').classList.toggle('valid', hasUpper);
        document.getElementById('lower-req').classList.toggle('valid', hasLower);
        document.getElementById('number-req').classList.toggle('valid', hasNumber);
        document.getElementById('special-req').classList.toggle('valid', hasSpecial);

        return minLength && hasUpper && hasLower && hasNumber && hasSpecial;
    }

    // Form validation
    function validateForm(formData) {
        let isValid = true;

        // Clear previous errors
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

        // Name validation
        if (!formData.name.trim()) {
            document.getElementById('nameError').textContent = 'First name is required';
            isValid = false;
        }

        // Surname validation
        if (!formData.surname.trim()) {
            document.getElementById('surnameError').textContent = 'Last name is required';
            isValid = false;
        }

        // Email validation
        if (!formData.email) {
            document.getElementById('emailError').textContent = 'Email is required';
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
            document.getElementById('emailError').textContent = 'Invalid email format';
            isValid = false;
        }

        // Password validation
        if (!formData.password) {
            document.getElementById('passwordError').textContent = 'Password is required';
            isValid = false;
        } else if (!validatePassword(formData.password)) {
            document.getElementById('passwordError').textContent = 'Password does not meet requirements';
            isValid = false;
        }

        // User type validation
        if (!formData.user_type) {
            document.getElementById('typeError').textContent = 'Account type is required';
            isValid = false;
        }

        return isValid;
    }

    // Form submission
    signupForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('name').value.trim(),
            surname: document.getElementById('surname').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            user_type: document.getElementById('userType').value
        };

        if (!validateForm(formData)) return;

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    type: 'Register',
                    name: formData.name,
                    surname: formData.surname,
                    email: formData.email,
                    password: formData.password,
                    user_type: formData.user_type
                })
            });

            const data = await response.json();

            // if (data.status === 'success') {
            //     // Store API key in sessionStorage
            //     sessionStorage.setItem('api_key', data.data.api_key);
                
            //     // Redirect to products page
            //     window.location.href = 'products.php';
            // } 
            if (data.status === 'success') {
    // Store all auth data in sessionStorage
    sessionStorage.setItem('auth', JSON.stringify({
        api_key: data.data.api_key,
        user_id: data.data.user_id,
        name: formData.name,  // Using formData since response may not include name
        user_type: formData.user_type
    }));
    
    // Redirect to products page
    window.location.href = 'products.php';
}
            
            
            else {
                // Handle specific errors
                if (data.message.includes('Email already registered')) {
                    document.getElementById('emailError').textContent = data.message;
                } else {
                    alert('Registration failed: ' + (data.message || 'Unknown error'));
                }
            }
        } catch (error) {
            console.error('Signup Error:', error);
            alert('An error occurred during registration. Please try again.');
        }
    });

    // Real-time password validation
    document.getElementById('password').addEventListener('input', function() {
        validatePassword(this.value);
    });
});



















