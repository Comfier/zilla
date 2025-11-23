import { authAPI, ordersAPI } from './api.js';

let currentUser = null;

// Check authentication and initialize
async function initClientDashboard() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'client') {
            window.location.href = 'client-login.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        await loadClientOrders();
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

// Load client orders
async function loadClientOrders() {
    try {
        const orders = await ordersAPI.getAll();
        const recentOrders = orders.slice(0, 5);
        displayRecentOrders(recentOrders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('clientRecentOrders').innerHTML = 
            '<p class="loading">Error loading orders. Please try again.</p>';
    }
}

// Display recent orders
function displayRecentOrders(orders) {
    const ordersList = document.getElementById('clientRecentOrders');

    if (!orders || orders.length === 0) {
        ordersList.innerHTML = '<p class="loading">No orders yet. Browse our catalog to place your first order!</p>';
        return;
    }

    ordersList.innerHTML = orders.map(order => `
        <div class="order-item">
            <div class="order-item-header">
                <h3>${order.design.substring(0, 50)}${order.design.length > 50 ? '...' : ''}</h3>
                <span class="order-status ${order.status}">${order.status}</span>
            </div>
            <div class="order-item-details">
                <div><strong>Measurements:</strong> ${order.measurements.substring(0, 60)}${order.measurements.length > 60 ? '...' : ''}</div>
                ${order.dueDate ? `<div><strong>Due Date:</strong> ${formatDate(order.dueDate)}</div>` : ''}
                <div><strong>Created:</strong> ${formatDate(order.createdAt)}</div>
            </div>
        </div>
    `).join('');
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Initialize dashboard when page loads
initClientDashboard();




