import { authAPI, furnitureAPI, ordersAPI } from './api.js';

let currentUser = null;
let currentFurniture = null;

// Check authentication and initialize
async function initCatalog() {
    try {
        const authResult = await authAPI.check();
        if (!authResult.authenticated || authResult.user.role !== 'client') {
            window.location.href = 'client-login.html';
            return;
        }
        
        currentUser = authResult.user;
        displayUserInfo(currentUser);
        await loadFurniture();
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

// Load furniture catalog
async function loadFurniture(filters = {}) {
    try {
        const furniture = await furnitureAPI.getAll(filters);
        displayFurniture(furniture);
    } catch (error) {
        console.error('Error loading furniture:', error);
        document.getElementById('furnitureCatalog').innerHTML = 
            '<p class="loading">Error loading catalog. Please try again.</p>';
    }
}

// Display furniture
function displayFurniture(furniture) {
    const catalog = document.getElementById('furnitureCatalog');

    if (!furniture || furniture.length === 0) {
        catalog.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🛋️</div>
                <h3>No furniture available</h3>
                <p>Check back later for new items!</p>
            </div>
        `;
        return;
    }

    catalog.innerHTML = furniture.map(item => `
        <div class="furniture-card">
            <div class="furniture-image">
                ${item.image_url && item.image_url.trim() !== '' ? 
                    `<img src="${item.image_url}" alt="${item.name}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" onload="this.style.display='block';">
                     <div class="no-image" style="display: none;">🛋️</div>` : 
                    '<div class="no-image">🛋️</div>'}
            </div>
            <div class="furniture-content">
                <div class="furniture-category">${item.category}</div>
                <h3>${item.name}</h3>
                <p class="furniture-description">${item.description}</p>
                <div class="furniture-details">
                    <p><strong>Measurements:</strong> ${item.default_measurements}</p>
                    ${item.price_range ? `<p><strong>Price Range:</strong> ${item.price_range}</p>` : ''}
                    ${item.features ? `<p><strong>Features:</strong> ${item.features}</p>` : ''}
                </div>
                <button class="btn btn-primary" onclick="openOrderModal(${item.id})">Order This Item</button>
            </div>
        </div>
    `).join('');
}

// Open order modal
window.openOrderModal = async function(furnitureId) {
    try {
        const furniture = await furnitureAPI.getById(furnitureId);
        currentFurniture = furniture;
        
        document.getElementById('selectedFurnitureId').value = furniture.id;
        document.getElementById('selectedFurnitureName').textContent = furniture.name;
        document.getElementById('defaultDescription').textContent = furniture.description;
        document.getElementById('defaultMeasurements').textContent = furniture.default_measurements;
        document.getElementById('orderDesign').value = furniture.description;
        document.getElementById('orderMeasurements').value = furniture.default_measurements;
        
        // Pre-fill with user info
        if (currentUser) {
            document.getElementById('orderClientName').value = currentUser.name;
            document.getElementById('orderClientEmail').value = currentUser.email;
        }
        
        // Clear any previous error messages
        const errorDiv = document.getElementById('catalogOrderError');
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');
        errorDiv.style.color = '';
        errorDiv.style.background = '';
        
        // Show modal and prevent body scroll
        const modal = document.getElementById('orderFromCatalogModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error loading furniture:', error);
        alert('Error loading furniture details. Please try again.');
    }
};

// Modal management
const orderModal = document.getElementById('orderFromCatalogModal');
const orderForm = document.getElementById('orderFromCatalogForm');
const closeModal = document.querySelector('#orderFromCatalogModal .close');
const cancelBtn = document.getElementById('cancelCatalogOrder');

// Function to close modal and restore body scroll
function closeOrderModal() {
    orderModal.classList.remove('show');
    document.body.style.overflow = '';
}

if (closeModal) {
    closeModal.addEventListener('click', closeOrderModal);
}

if (cancelBtn) {
    cancelBtn.addEventListener('click', closeOrderModal);
}

if (orderModal) {
    window.addEventListener('click', (e) => {
        if (e.target === orderModal) {
            closeOrderModal();
        }
    });
}

// Form submission
if (orderForm) {
    orderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('catalogOrderError');
        errorDiv.textContent = '';
        errorDiv.classList.remove('show');

        const orderData = {
            furnitureId: parseInt(document.getElementById('selectedFurnitureId').value),
            clientName: document.getElementById('orderClientName').value,
            clientEmail: document.getElementById('orderClientEmail').value,
            clientPhone: document.getElementById('orderClientPhone').value || '',
            design: document.getElementById('orderDesign').value,
            measurements: document.getElementById('orderMeasurements').value,
            instructions: document.getElementById('orderInstructions').value || '',
            status: 'pending',
            dueDate: document.getElementById('orderDueDate').value || null
        };

        try {
            const result = await ordersAPI.create(orderData);
            
            // Show success message
            errorDiv.textContent = 'Order placed successfully!';
            errorDiv.style.color = '#10b981';
            errorDiv.style.background = 'rgba(16, 185, 129, 0.1)';
            errorDiv.classList.add('show');
            
            // Reset form and close modal after 2 seconds
            setTimeout(() => {
                orderForm.reset();
                closeOrderModal();
                errorDiv.textContent = '';
                errorDiv.classList.remove('show');
                errorDiv.style.color = '';
                errorDiv.style.background = '';
                
                // Optionally redirect to orders page after closing
                // window.location.href = 'client-orders.html';
            }, 2000);
        } catch (error) {
            errorDiv.textContent = error.message || 'Error placing order. Please try again.';
            errorDiv.style.color = '#dc2626';
            errorDiv.style.background = 'rgba(220, 38, 38, 0.1)';
            errorDiv.classList.add('show');
            console.error('Error placing order:', error);
        }
    });
}

// Filter functionality
const categoryFilter = document.getElementById('categoryFilter');
const clearFiltersBtn = document.getElementById('clearCatalogFilters');

if (categoryFilter) {
    categoryFilter.addEventListener('change', () => {
        applyFilters();
    });
}

if (clearFiltersBtn) {
    clearFiltersBtn.addEventListener('click', () => {
        categoryFilter.value = 'all';
        loadFurniture();
    });
}

function applyFilters() {
    const filters = {
        category: categoryFilter.value
    };
    loadFurniture(filters);
}

// Check for order parameter in URL
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('order');
    if (orderId) {
        // Wait for catalog to load, then open order modal
        setTimeout(() => {
            if (typeof openOrderModal === 'function') {
                openOrderModal(parseInt(orderId));
            }
        }, 1000);
    }
});

// Initialize catalog when page loads
initCatalog();



