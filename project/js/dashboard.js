import { dashboardAPI, authAPI } from './api.js';

let currentUser = null;

// Check authentication and load dashboard
async function initDashboard() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated) {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = authResult.user;
        
        // Check if user is admin - if not, redirect to appropriate dashboard
        if (currentUser.role === 'client') {
            window.location.href = 'client-dashboard.html';
            return;
        }
        
        // Show admin quick actions
        const adminQuickActions = document.getElementById('adminQuickActions');
        if (adminQuickActions) {
            adminQuickActions.style.display = 'block';
        }
        
        displayUserInfo(currentUser);
        await loadDashboardData();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'index.html';
    }
}

// Display user info
function displayUserInfo(user) {
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        if (user.role === 'admin') {
            userInfo.textContent = `Admin: ${user.email}`;
        } else {
            userInfo.textContent = `Logged in as: ${user.email}`;
        }
    }
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
            window.location.href = 'index.html';
        }
    });
}

// Load dashboard data
async function loadDashboardData() {
    try {
        const data = await dashboardAPI.getStats();
        updateStats(data.stats);
        displayRecentOrders(data.recentOrders);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        document.getElementById('recentOrdersList').innerHTML = 
            '<p class="loading">Error loading orders. Please try again.</p>';
    }
}

// Update statistics
function updateStats(stats) {
    document.getElementById('totalOrders').textContent = stats.total || 0;
    document.getElementById('pendingOrders').textContent = stats.pending || 0;
    document.getElementById('completedOrders').textContent = stats.completed || 0;
    document.getElementById('inProgressOrders').textContent = stats.inProgress || 0;
}

// Display recent orders
function displayRecentOrders(orders) {
    const recentOrdersList = document.getElementById('recentOrdersList');

    if (!orders || orders.length === 0) {
        recentOrdersList.innerHTML = '<p class="loading">No orders yet. Create your first order!</p>';
        return;
    }

    recentOrdersList.innerHTML = orders.map(order => `
        <div class="order-item">
            <div class="order-item-header">
                <h3>${order.clientName}</h3>
                <span class="order-status ${order.status}">${order.status}</span>
            </div>
            <div class="order-item-details">
                <div><strong>Email:</strong> ${order.clientEmail}</div>
                <div><strong>Design:</strong> ${order.design.substring(0, 50)}${order.design.length > 50 ? '...' : ''}</div>
                ${order.dueDate ? `<div><strong>Due Date:</strong> ${formatDate(order.dueDate)}</div>` : ''}
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
initDashboard();
