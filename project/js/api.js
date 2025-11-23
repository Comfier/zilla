// API Configuration
const API_BASE_URL = 'php';

// Helper function for API calls
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include' // Include cookies for session
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`, options);
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.error || 'API request failed');
        }
        
        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Authentication API
export const authAPI = {
    login: (email, password) => apiCall('auth.php?action=login', 'POST', { email, password }),
    signup: (name, email, phone, password, role = 'client') => apiCall('auth.php?action=signup', 'POST', { name, email, phone, password, role }),
    logout: () => apiCall('auth.php?action=logout', 'POST'),
    check: () => apiCall('auth.php?action=check', 'GET')
};

// Orders API
export const ordersAPI = {
    getAll: (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.clientName) params.append('client_name', filters.clientName);
        if (filters.status && filters.status !== 'all') params.append('status', filters.status);
        const query = params.toString();
        return apiCall(`orders.php${query ? '?' + query : ''}`, 'GET');
    },
    create: (orderData) => apiCall('orders.php', 'POST', orderData),
    update: (orderData) => apiCall('orders.php', 'PUT', orderData),
    delete: (orderId) => apiCall(`orders.php?id=${orderId}`, 'DELETE')
};

// Dashboard API
export const dashboardAPI = {
    getStats: () => apiCall('dashboard.php', 'GET')
};

// Furniture API
export const furnitureAPI = {
    getAll: (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.category && filters.category !== 'all') params.append('category', filters.category);
        const query = params.toString();
        return apiCall(`furniture.php${query ? '?' + query : ''}`, 'GET');
    },
    getById: (id) => apiCall(`furniture.php?id=${id}`, 'GET'),
    create: (furnitureData) => apiCall('furniture.php', 'POST', furnitureData),
    update: (furnitureData) => apiCall('furniture.php', 'PUT', furnitureData),
    delete: (id) => apiCall(`furniture.php?id=${id}`, 'DELETE')
};

// Admin API
export const adminAPI = {
    getAllOrders: (filters = {}) => {
        const params = new URLSearchParams();
        if (filters.clientName) params.append('client_name', filters.clientName);
        if (filters.status && filters.status !== 'all') params.append('status', filters.status);
        const query = params.toString();
        return apiCall(`admin.php?action=orders${query ? '&' + query : ''}`, 'GET');
    },
    getStats: () => apiCall('admin.php?action=stats', 'GET'),
    updateOrder: (orderData) => apiCall('admin.php?action=update-order', 'PUT', orderData)
};

