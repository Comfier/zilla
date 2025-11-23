import { authAPI, ordersAPI } from './api.js';

let currentUser = null;

// Check authentication and initialize
async function initPlaceOrder() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'client') {
            window.location.href = 'client-login.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        
        // Pre-fill form with user info
        if (currentUser) {
            document.getElementById('customOrderClientName').value = currentUser.name;
            document.getElementById('customOrderClientEmail').value = currentUser.email;
        }
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'client-login.html';
    }
}

// Display user info
function displayUserInfo(user) {
    const userInfo = document.getElementById('clientUserInfo');
    if (userInfo) {
        userInfo.textContent = `Logged in as: ${user.email}`;
    }
}

// Logout functionality
const logoutBtn = document.getElementById('clientLogoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await authAPI.logout();
            window.location.href = 'client-login.html';
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = 'client-login.html';
        }
    });
}

// Form submission
const customOrderForm = document.getElementById('customOrderForm');
if (customOrderForm) {
    customOrderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('customOrderError');
        const successDiv = document.getElementById('customOrderSuccess');
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');
        successDiv.classList.remove('show');

        const orderData = {
            clientName: document.getElementById('customOrderClientName').value,
            clientEmail: document.getElementById('customOrderClientEmail').value,
            clientPhone: document.getElementById('customOrderClientPhone').value || '',
            design: document.getElementById('customOrderDesign').value,
            measurements: document.getElementById('customOrderMeasurements').value,
            instructions: document.getElementById('customOrderInstructions').value || '',
            status: 'pending',
            dueDate: document.getElementById('customOrderDueDate').value || null
        };

        try {
            const result = await ordersAPI.create(orderData);
            successDiv.textContent = 'Order placed successfully! Redirecting to payment...';
            successDiv.classList.add('show');
            
            // Get order ID from result
            const orderId = result.id || result.orderId;
            
            setTimeout(() => {
                if (orderId) {
                    window.location.href = `payment.html?order=${orderId}`;
                } else {
                    window.location.href = 'client-orders.html';
                }
            }, 1500);
        } catch (error) {
            errorDiv.textContent = error.message || 'Error placing order. Please try again.';
            errorDiv.classList.add('show');
            console.error('Error placing order:', error);
        }
    });
}

// Initialize page when it loads
initPlaceOrder();



