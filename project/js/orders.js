import { ordersAPI, authAPI } from './api.js';

let currentUser = null;
let currentEditId = null;
let pendingDeleteOrderId = null;

// Delete confirmation modal setup
const deleteModal = document.getElementById('deleteConfirmModal');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');

if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', async () => {
        if (pendingDeleteOrderId !== null) {
            try {
                await ordersAPI.delete(pendingDeleteOrderId);
                deleteModal.classList.remove('show');
                document.body.style.overflow = '';
                await loadOrders();
                pendingDeleteOrderId = null;
            } catch (error) {
                console.error('Error deleting order:', error);
                alert('Error deleting order. Please try again.');
            }
        }
    });
}

if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', () => {
        deleteModal.classList.remove('show');
        document.body.style.overflow = '';
        pendingDeleteOrderId = null;
    });
}

if (deleteModal) {
    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.classList.remove('show');
            document.body.style.overflow = '';
            pendingDeleteOrderId = null;
        }
    });
}

// Check authentication and initialize
async function initOrders() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated) {
            window.location.href = 'index.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        await loadOrders();
    } catch (error) {
        console.error('Auth check failed:', error);
        window.location.href = 'index.html';
    }
}

// Display user info
function displayUserInfo(user) {
    const userInfo = document.getElementById('userInfo');
    if (userInfo) {
        userInfo.textContent = `Logged in as: ${user.email}`;
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

// Load orders
async function loadOrders(filters = {}) {
    try {
        const orders = await ordersAPI.getAll(filters);
        displayOrders(orders);
    } catch (error) {
        console.error('Error loading orders:', error);
        document.getElementById('ordersList').innerHTML = 
            '<p class="loading">Error loading orders. Please try again.</p>';
    }
}

// Display orders
function displayOrders(orders) {
    const ordersList = document.getElementById('ordersList');

    if (!orders || orders.length === 0) {
        ordersList.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>No orders found</h3>
                <p>Create your first order to get started!</p>
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
                </div>
                <span class="order-status ${order.status}">${order.status}</span>
            </div>
            <div class="order-card-body">
                <p><strong>Design:</strong> ${order.design}</p>
                <p><strong>Measurements:</strong> ${order.measurements}</p>
                ${order.instructions ? `<p><strong>Instructions:</strong> ${order.instructions}</p>` : ''}
                ${order.dueDate ? `<p><strong>Due Date:</strong> ${formatDate(order.dueDate)}</p>` : ''}
                <p><strong>Created:</strong> ${formatDate(order.createdAt)}</p>
            </div>
            <div class="order-card-actions">
                <button class="btn btn-edit" onclick="window.editOrder(${order.id})">✏️ Edit</button>
                <button class="btn btn-delete" onclick="window.deleteOrder(${order.id})">🗑️ Delete</button>
            </div>
        </div>
    `).join('');
}

// Add order modal
const addOrderBtn = document.getElementById('addOrderBtn');
const orderModal = document.getElementById('orderModal');
const orderForm = document.getElementById('orderForm');
const closeModal = document.querySelector('.close');
const cancelOrderBtn = document.getElementById('cancelOrderBtn');

if (addOrderBtn) {
    addOrderBtn.addEventListener('click', (e) => {
        e.preventDefault();
        // Redirect to add order page
        window.location.href = 'add-order.html';
    });
}

if (closeModal) {
    closeModal.addEventListener('click', () => {
        orderModal.classList.remove('show');
    });
}

if (cancelOrderBtn) {
    cancelOrderBtn.addEventListener('click', () => {
        orderModal.classList.remove('show');
    });
}

if (orderModal) {
    window.addEventListener('click', (e) => {
        if (e.target === orderModal) {
            orderModal.classList.remove('show');
        }
    });
}

// Form submission
if (orderForm) {
    orderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('orderError');
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');

        const orderData = {
            clientName: document.getElementById('orderClientName').value,
            clientEmail: document.getElementById('orderClientEmail').value,
            clientPhone: document.getElementById('orderClientPhone').value || '',
            design: document.getElementById('orderDesign').value,
            measurements: document.getElementById('orderMeasurements').value,
            instructions: document.getElementById('orderInstructions').value || '',
            status: document.getElementById('orderStatus').value,
            dueDate: document.getElementById('orderDueDate').value || null
        };

        try {
            if (currentEditId) {
                // Update existing order
                orderData.id = currentEditId;
                await ordersAPI.update(orderData);
            } else {
                // Add new order
                await ordersAPI.create(orderData);
            }

            orderModal.classList.remove('show');
            orderForm.reset();
            await loadOrders();
        } catch (error) {
            errorDiv.textContent = error.message || 'Error saving order. Please try again.';
            errorDiv.classList.add('show');
            console.error('Error saving order:', error);
        }
    });
}

// Edit order - redirect to edit page
window.editOrder = function(orderId) {
    orderId = parseInt(orderId);
    if (isNaN(orderId)) {
        alert('Invalid order ID');
        return;
    }
    window.location.href = `edit-order-user.html?id=${orderId}`;
};

// Delete order
window.deleteOrder = async function(orderId) {
    // Convert to number if string
    orderId = parseInt(orderId);
    
    // Show confirmation modal instead of confirm dialog
    pendingDeleteOrderId = orderId;
    deleteConfirmMessage.textContent = 'Are you sure you want to delete this order? This action cannot be undone.';
    deleteModal.classList.add('show');
    document.body.style.overflow = 'hidden';
};

// Filter functionality
const filterClient = document.getElementById('filterClient');
const filterStatus = document.getElementById('filterStatus');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');

if (filterClient) {
    filterClient.addEventListener('input', () => {
        applyFilters();
    });
}

if (filterStatus) {
    filterStatus.addEventListener('change', () => {
        applyFilters();
    });
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
        filterClient.value = '';
        filterStatus.value = 'all';
        loadOrders();
    });
}

function applyFilters() {
    const filters = {
        clientName: filterClient.value,
        status: filterStatus.value
    };
    loadOrders(filters);
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Initialize orders page when it loads
initOrders();
