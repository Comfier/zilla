import { authAPI, ordersAPI } from './api.js';

let currentUser = null;

// Check authentication and initialize
async function initClientOrders() {
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
async function loadClientOrders(filters = {}) {
    try {
        const orders = await ordersAPI.getAll(filters);
        displayOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('clientOrdersList').innerHTML = 
            '<p class="loading">Error loading orders. Please try again.</p>';
    }
}

// Display orders
function displayOrders(orders) {
    const ordersList = document.getElementById('clientOrdersList');

    if (!orders || orders.length === 0) {
        ordersList.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>No orders found</h3>
                <p>Place your first order from the catalog!</p>
            </div>
        `;
        return;
    }

    ordersList.innerHTML = orders.map(order => {
        const paymentStatus = order.paymentStatus || 'pending';
        const amount = order.amount ? `$${parseFloat(order.amount).toFixed(2)}` : 'Not set';
        const paymentBadgeClass = paymentStatus === 'paid' ? 'paid' : paymentStatus === 'failed' ? 'failed' : 'pending-payment';
        
        return `
        <div class="order-card">
            <div class="order-card-header">
                <div>
                    <h3>${order.design.substring(0, 60)}${order.design.length > 60 ? '...' : ''}</h3>
                    <p style="color: #666; font-size: 14px; margin-top: 5px;">Order #${order.id}</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 5px; align-items: flex-end;">
                    <span class="order-status ${order.status}">${order.status}</span>
                    <span class="order-status ${paymentBadgeClass}" style="font-size: 11px;">${paymentStatus === 'paid' ? '✓ Paid' : paymentStatus === 'failed' ? '✗ Failed' : '⏳ Payment Pending'}</span>
                </div>
            </div>
            <div class="order-card-body">
                <p><strong>Measurements:</strong> ${order.measurements}</p>
                ${order.instructions ? `<p><strong>Instructions:</strong> ${order.instructions}</p>` : ''}
                ${order.dueDate ? `<p><strong>Due Date:</strong> ${formatDate(order.dueDate)}</p>` : ''}
                <p><strong>Amount:</strong> ${amount}</p>
                ${order.paymentDate ? `<p><strong>Paid on:</strong> ${formatDate(order.paymentDate)}</p>` : ''}
                <p><strong>Created:</strong> ${formatDate(order.createdAt)}</p>
            </div>
            <div class="order-card-actions">
                ${paymentStatus !== 'paid' ? `<a href="payment.html?order=${order.id}" class="btn btn-primary" style="flex: 1; text-align: center; text-decoration: none; display: block;">Pay Now</a>` : ''}
            </div>
        </div>
    `;
    }).join('');
}

// Filter functionality
const statusFilter = document.getElementById('clientFilterStatus');
const clearFiltersBtn = document.getElementById('clearClientFilters');

if (statusFilter) {
    statusFilter.addEventListener('change', () => {
        applyFilters();
    });
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
        statusFilter.value = 'all';
        loadClientOrders();
    });
}

function applyFilters() {
    const filters = {
        status: statusFilter.value
    };
    loadClientOrders(filters);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Initialize orders page when it loads
initClientOrders();



