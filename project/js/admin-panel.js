import { authAPI, adminAPI } from './api.js';

let currentUser = null;

// Check authentication and initialize
async function initAdminPanel() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'admin') {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        await loadStats();
        await loadAllOrders();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'index.html';
    }
}

// Display user info
function displayUserInfo(user) {
    const userInfo = document.getElementById('adminUserInfo');
    if (userInfo) {
        userInfo.textContent = `Admin: ${user.email}`;
    }
}

// Logout functionality
const logoutBtn = document.getElementById('adminLogoutBtn');
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

// Load statistics
async function loadStats() {
    try {
        const stats = await adminAPI.getStats();
        document.getElementById('adminTotalOrders').textContent = stats.total || 0;
        document.getElementById('adminPendingOrders').textContent = stats.pending || 0;
        document.getElementById('adminInProgressOrders').textContent = stats.inProgress || 0;
        document.getElementById('adminCompletedOrders').textContent = stats.completed || 0;
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load all orders
async function loadAllOrders(filters = {}) {
    try {
        const orders = await adminAPI.getAllOrders(filters);
        displayAllOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('adminAllOrdersList').innerHTML = 
            '<p class="loading">Error loading orders. Please try again.</p>';
    }
}

// Display all orders
function displayAllOrders(orders) {
    const ordersList = document.getElementById('adminAllOrdersList');

    if (!orders || orders.length === 0) {
        ordersList.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>No orders found</h3>
                <p>No client orders yet.</p>
            </div>
        `;
        return;
    }

    ordersList.innerHTML = orders.map(order => `
        <div class="order-card">
            <div class="order-card-header">
                <div>
                    <h3>${order.clientName}</h3>
                    <p style="color: #666; font-size: 14px; margin-top: 5px;">${order.clientEmail}</p>
                    ${order.furnitureName ? `<p style="color: #888; font-size: 12px;">Furniture: ${order.furnitureName}</p>` : ''}
                </div>
                <span class="order-status ${order.status}">${order.status}</span>
            </div>
            <div class="order-card-body">
                <p><strong>Design:</strong> ${order.design}</p>
                <p><strong>Measurements:</strong> ${order.measurements}</p>
                ${order.instructions ? `<p><strong>Instructions:</strong> ${order.instructions}</p>` : ''}
                ${order.dueDate ? `<p><strong>Due Date:</strong> ${formatDate(order.dueDate)}</p>` : ''}
                <p><strong>Ordered by:</strong> ${order.userName} (${order.userEmail})</p>
                <p><strong>Created:</strong> ${formatDate(order.createdAt)}</p>
            </div>
            <div class="order-card-actions">
                <button class="btn btn-edit" onclick="window.editAdminOrder(${order.id})">✏️ Update Status</button>
            </div>
        </div>
    `).join('');
}

// Edit order modal
const adminOrderModal = document.getElementById('adminOrderModal');
const adminOrderForm = document.getElementById('adminOrderForm');
const closeModal = document.querySelector('#adminOrderModal .close');
const cancelBtn = document.getElementById('cancelAdminOrder');

if (closeModal) {
    closeModal.addEventListener('click', () => {
        adminOrderModal.classList.remove('show');
    });
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
        adminOrderModal.classList.remove('show');
    });
}

window.addEventListener('click', (e) => {
    if (e.target === adminOrderModal) {
        adminOrderModal.classList.remove('show');
    }
});

// Edit order - redirect to edit page
window.editAdminOrder = function(orderId) {
    orderId = parseInt(orderId);
    if (isNaN(orderId)) {
        alert('Invalid order ID');
        return;
    }
    window.location.href = `edit-order.html?id=${orderId}`;
};

// Form submission
if (adminOrderForm) {
    adminOrderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('adminOrderError');
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');

        const orderData = {
            id: parseInt(document.getElementById('adminOrderId').value),
            status: document.getElementById('adminOrderStatus').value,
            dueDate: document.getElementById('adminOrderDueDate').value || null
        };

        try {
            await adminAPI.updateOrder(orderData);
            adminOrderModal.classList.remove('show');
            await loadStats();
            await loadAllOrders();
        } catch (error) {
            errorDiv.textContent = error.message || 'Error updating order. Please try again.';
            errorDiv.classList.add('show');
            console.error('Error updating order:', error);
        }
    });
}

// Filter functionality
const clientFilter = document.getElementById('adminFilterClient');
const statusFilter = document.getElementById('adminFilterStatus');
const clearFiltersBtn = document.getElementById('clearAdminFilters');

if (clientFilter) {
    clientFilter.addEventListener('input', () => {
        applyFilters();
    });
}

if (statusFilter) {
    statusFilter.addEventListener('change', () => {
        applyFilters();
    });
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
        clientFilter.value = '';
        statusFilter.value = 'all';
        loadAllOrders();
    });
}

function applyFilters() {
    const filters = {
        clientName: clientFilter.value,
        status: statusFilter.value
    };
    loadAllOrders(filters);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Initialize admin panel when page loads
initAdminPanel();




