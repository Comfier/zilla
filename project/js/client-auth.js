import { authAPI } from './api.js';

// Check if client is logged in
async function checkClientAuth() {
    try {
        const result = await authAPI.check();
        if (result.authenticated) {
            // Check if user is a client
            if (result.user.role !== 'client') {
                // Admin trying to access client area - redirect to admin
                if (window.location.pathname.includes('client-')) {
                    window.location.href = 'dashboard.html';
                    return null;
                }
            }
            if (window.location.pathname.includes('client-login.html') || 
                window.location.pathname.includes('client-signup.html')) {
                window.location.href = 'client-dashboard.html';
            }
            return result.user;
        }
    } catch (error) {
        // Not authenticated
        if (window.location.pathname.includes('client-dashboard.html') || 
            window.location.pathname.includes('client-orders.html') ||
            window.location.pathname.includes('client-catalog.html') ||
            window.location.pathname.includes('place-order.html')) {
            window.location.href = 'client-login.html';
        }
    }
    return null;
}

// Check authentication on page load
checkClientAuth();

// Client Login functionality
const clientLoginForm = document.getElementById('clientLoginForm');
if (clientLoginForm) {
    clientLoginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('clientLoginEmail').value.trim();
        const password = document.getElementById('clientLoginPassword').value;
        const errorDiv = document.getElementById('clientLoginError');
        const loginBtn = document.getElementById('clientLoginBtn');
        const btnText = document.getElementById('clientLoginBtnText');
        const btnLoader = document.getElementById('clientLoginBtnLoader');

        // Validation
        if (!email || !password) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.classList.add('show');
            return;
        }

        // Email format validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorDiv.textContent = 'Please enter a valid email address';
            errorDiv.classList.add('show');
            loginBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
            return;
        }

        // Show loading state
        loginBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline';
        errorDiv.classList.remove('show');

        try {
            const result = await authAPI.login(email, password);
            if (result.success) {
                // Check role
                if (result.user.role === 'admin') {
                    window.location.href = 'dashboard.html';
                } else {
                    window.location.href = 'client-dashboard.html';
                }
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Login failed. Please check your email and password.';
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        } finally {
            // Reset button state
            loginBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
        }
    });
}

// Client Sign up functionality
const clientSignupForm = document.getElementById('clientSignupForm');
if (clientSignupForm) {
    clientSignupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('clientSignupName').value.trim();
        const email = document.getElementById('clientSignupEmail').value.trim();
        const phone = document.getElementById('clientSignupPhone').value.trim();
        const password = document.getElementById('clientSignupPassword').value;
        const confirmPassword = document.getElementById('clientSignupConfirmPassword').value;
        const errorDiv = document.getElementById('clientSignupError');
        const successDiv = document.getElementById('clientSignupSuccess');
        const signupBtn = document.getElementById('clientSignupBtn');
        const btnText = document.getElementById('clientSignupBtnText');
        const btnLoader = document.getElementById('clientSignupBtnLoader');

        // Clear previous messages
        errorDiv.classList.remove('show');
        successDiv.classList.remove('show');

        // Check terms agreement
        const agreeTerms = document.getElementById('agreeTerms');
        if (!agreeTerms || !agreeTerms.checked) {
            errorDiv.textContent = 'Please agree to the Terms of Service and Privacy Policy';
            errorDiv.classList.add('show');
            return;
        }

        // Validation
        if (!name || !email || !password || !confirmPassword) {
            errorDiv.textContent = 'Please fill in all required fields';
            errorDiv.classList.add('show');
            return;
        }

        // Name validation
        if (name.length < 2) {
            errorDiv.textContent = 'Please enter your full name (at least 2 characters)';
            errorDiv.classList.add('show');
            return;
        }

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            errorDiv.textContent = 'Please enter a valid email address';
            errorDiv.classList.add('show');
            return;
        }

        if (password !== confirmPassword) {
            errorDiv.textContent = 'Passwords do not match!';
            errorDiv.classList.add('show');
            return;
        }

        if (password.length < 6) {
            errorDiv.textContent = 'Password must be at least 6 characters!';
            errorDiv.classList.add('show');
            return;
        }

        // Show loading state
        signupBtn.disabled = true;
        btnText.style.display = 'none';
        btnLoader.style.display = 'inline';

        try {
            const result = await authAPI.signup(name, email, phone, password, 'client');
            if (result.success) {
                successDiv.textContent = '✓ Account created successfully! Redirecting to dashboard...';
                successDiv.classList.add('show');
                
                // Check if there's a pending order from homepage
                const pendingOrderId = sessionStorage.getItem('pendingOrderId');
                if (pendingOrderId) {
                    sessionStorage.removeItem('pendingOrderId');
                    setTimeout(() => {
                        window.location.href = `client-catalog.html?order=${pendingOrderId}`;
                    }, 2000);
                } else {
                    setTimeout(() => {
                        window.location.href = 'client-dashboard.html';
                    }, 2000);
                }
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Signup failed. Please try again.';
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        } finally {
            // Reset button state
            signupBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoader.style.display = 'none';
        }
    });
}

// Client Logout functionality
const clientLogoutBtn = document.getElementById('clientLogoutBtn');
if (clientLogoutBtn) {
    clientLogoutBtn.addEventListener('click', async () => {
        try {
            await authAPI.logout();
            window.location.href = 'client-login.html';
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = 'client-login.html';
        }
    });
}



