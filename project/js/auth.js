import { authAPI } from './api.js';

// Check if user is logged in
async function checkAuth() {
    try {
        const result = await authAPI.check();
        if (result.authenticated) {
            if (window.location.pathname.includes('index.html') || 
                window.location.pathname.includes('signup.html') ||
                window.location.pathname.endsWith('/') ||
                window.location.pathname.endsWith('/project/')) {
                window.location.href = 'dashboard.html';
            }
            return result.user;
        }
    } catch (error) {
        // Not authenticated
        if (window.location.pathname.includes('dashboard.html') || 
            window.location.pathname.includes('orders.html')) {
            window.location.href = 'index.html';
        }
    }
    return null;
}

// Check authentication on page load
checkAuth();

// Login functionality
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('loginEmail').value;
        const password = document.getElementById('loginPassword').value;
        const errorDiv = document.getElementById('loginError');

        try {
            const result = await authAPI.login(email, password);
            if (result.success) {
                window.location.href = 'dashboard.html';
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Login failed. Please try again.';
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }
    });
}

// Sign up functionality
const signupForm = document.getElementById('signupForm');
if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = document.getElementById('signupName').value;
        const email = document.getElementById('signupEmail').value;
        const phone = document.getElementById('signupPhone').value;
        const password = document.getElementById('signupPassword').value;
        const confirmPassword = document.getElementById('signupConfirmPassword').value;
        const errorDiv = document.getElementById('signupError');
        const successDiv = document.getElementById('signupSuccess');

        // Validate passwords match
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

        try {
            const result = await authAPI.signup(name, email, phone, password);
            if (result.success) {
                successDiv.textContent = 'Account created successfully! Redirecting...';
                successDiv.classList.add('show');
                
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 2000);
            }
        } catch (error) {
            errorDiv.textContent = error.message || 'Signup failed. Please try again.';
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }
    });
}

// Logout functionality
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await authAPI.logout();
            window.location.href = 'index.html';
        } catch (error) {
            console.error('Logout error:', error);
            // Still redirect even if logout fails
            window.location.href = 'index.html';
        }
    });
}
