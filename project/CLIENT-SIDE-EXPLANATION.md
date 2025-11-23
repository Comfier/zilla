# Client-Side (Frontend) Architecture Explanation

## Overview

The client-side consists of **HTML pages**, **CSS styling**, and **JavaScript** that runs in the user's browser. It handles user interactions, displays data, and communicates with the PHP backend API.

---

## 1. HTML Structure

### Page Files:

#### `index.html` - Login Page
```html
- Login form with email and password fields
- "Sign Up" link to registration page
- Error message display area
- Links to CSS and JavaScript files
```

**Key Elements:**
- Form ID: `loginForm`
- Input IDs: `loginEmail`, `loginPassword`
- Error div: `loginError`

#### `signup.html` - Registration Page
```html
- Registration form with:
  - Full Name
  - Email
  - Phone Number
  - Password
  - Confirm Password
- "Login" link back to login page
- Success/Error message areas
```

**Key Elements:**
- Form ID: `signupForm`
- Input IDs: `signupName`, `signupEmail`, `signupPhone`, `signupPassword`, `signupConfirmPassword`
- Error/Success divs: `signupError`, `signupSuccess`

#### `dashboard.html` - Dashboard Page
```html
- Navigation bar with links
- Statistics cards (Total, Pending, Completed, In Progress)
- Recent orders section
- User info display
- Logout button
```

**Key Elements:**
- Stats: `totalOrders`, `pendingOrders`, `completedOrders`, `inProgressOrders`
- Recent orders list: `recentOrdersList`
- User info: `userInfo`
- Logout button: `logoutBtn`

#### `orders.html` - Orders Management Page
```html
- Navigation bar
- "Add New Order" button
- Filter section (client name, status)
- Orders grid display
- Modal popup for add/edit order form
```

**Key Elements:**
- Add button: `addOrderBtn`
- Filters: `filterClient`, `filterStatus`
- Orders list: `ordersList`
- Modal: `orderModal`
- Order form: `orderForm`

---

## 2. CSS Styling

### `css/style.css` - Global Styles
```css
- Body and container styles
- Form styling (inputs, buttons, labels)
- Navigation bar
- Error/success message styles
- Responsive design
- Gradient backgrounds
- Animations
```

**Key Features:**
- Modern gradient background
- Card-based layouts
- Smooth animations
- Responsive design for mobile
- Form validation styling

### `css/dashboard.css` - Dashboard Specific
```css
- Statistics card grid layout
- Stat icons and numbers
- Recent orders list styling
- Order status badges (colors)
```

### `css/orders.css` - Orders Page Specific
```css
- Order card grid layout
- Modal popup styling
- Filter section design
- Empty state styling
- Action buttons (Edit/Delete)
```

---

## 3. JavaScript Architecture

### Module System (ES6 Modules)

All JavaScript files use **ES6 modules** for better organization:
```javascript
// Import from api.js
import { authAPI } from './api.js';
```

### File Structure:

#### `js/api.js` - API Communication Layer

**Purpose:** Centralized API communication

**Key Functions:**

```javascript
// Main API call function
async function apiCall(endpoint, method, data)
- Makes fetch requests to PHP backend
- Handles JSON parsing
- Manages errors
- Includes credentials for session cookies

// Authentication API
export const authAPI = {
    login: (email, password) => ...
    signup: (name, email, phone, password) => ...
    logout: () => ...
    check: () => ...
}

// Orders API
export const ordersAPI = {
    getAll: (filters) => ...
    create: (orderData) => ...
    update: (orderData) => ...
    delete: (orderId) => ...
}

// Dashboard API
export const dashboardAPI = {
    getStats: () => ...
}
```

**How it works:**
1. Receives function calls from other modules
2. Constructs API endpoint URLs
3. Makes HTTP requests using `fetch()`
4. Handles responses and errors
5. Returns data or throws errors

**Example Flow:**
```javascript
// When you call: authAPI.login('email', 'password')
// It does:
fetch('php/auth.php?action=login', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({email, password}),
    credentials: 'include'  // For session cookies
})
```

---

#### `js/auth.js` - Authentication Logic

**Purpose:** Handles all authentication-related functionality

**Key Functions:**

1. **`checkAuth()`** - Checks if user is logged in
   ```javascript
   - Calls authAPI.check()
   - If authenticated: redirects to dashboard (if on login page)
   - If not authenticated: redirects to login (if on protected pages)
   ```

2. **Login Form Handler**
   ```javascript
   - Listens for form submission
   - Prevents default form behavior
   - Gets email and password from form
   - Calls authAPI.login()
   - On success: redirects to dashboard
   - On error: displays error message
   ```

3. **Signup Form Handler**
   ```javascript
   - Validates passwords match
   - Validates password length (min 6 chars)
   - Calls authAPI.signup()
   - On success: shows success message, redirects
   - On error: displays error message
   ```

4. **Logout Handler**
   ```javascript
   - Calls authAPI.logout()
   - Redirects to login page
   ```

**Event Listeners:**
- `loginForm.addEventListener('submit', ...)`
- `signupForm.addEventListener('submit', ...)`
- `logoutBtn.addEventListener('click', ...)`

---

#### `js/dashboard.js` - Dashboard Functionality

**Purpose:** Manages dashboard page logic

**Key Functions:**

1. **`initDashboard()`** - Initializes dashboard
   ```javascript
   - Checks authentication
   - Displays user info
   - Loads dashboard data
   ```

2. **`loadDashboardData()`** - Fetches and displays data
   ```javascript
   - Calls dashboardAPI.getStats()
   - Updates statistics cards
   - Displays recent orders
   ```

3. **`updateStats(stats)`** - Updates stat cards
   ```javascript
   - Sets total orders count
   - Sets pending count
   - Sets completed count
   - Sets in-progress count
   ```

4. **`displayRecentOrders(orders)`** - Shows recent orders
   ```javascript
   - Takes array of orders
   - Creates HTML for each order
   - Displays in recent orders section
   ```

**Data Flow:**
```
Page Load → initDashboard() → checkAuth() → loadDashboardData() 
→ API Call → Update UI
```

---

#### `js/orders.js` - Order Management Logic

**Purpose:** Handles all order CRUD operations

**Key Functions:**

1. **`initOrders()`** - Initializes orders page
   ```javascript
   - Checks authentication
   - Loads orders list
   ```

2. **`loadOrders(filters)`** - Fetches orders
   ```javascript
   - Calls ordersAPI.getAll(filters)
   - Displays orders in grid
   - Handles empty state
   ```

3. **`displayOrders(orders)`** - Renders orders
   ```javascript
   - Creates HTML cards for each order
   - Includes: client info, design, measurements, status
   - Adds Edit/Delete buttons
   - Handles empty state (no orders)
   ```

4. **`editOrder(orderId)`** - Opens edit modal
   ```javascript
   - Fetches order data
   - Populates form fields
   - Opens modal
   - Sets currentEditId
   ```

5. **`deleteOrder(orderId)`** - Deletes order
   ```javascript
   - Confirms deletion
   - Calls ordersAPI.delete()
   - Refreshes orders list
   ```

6. **`applyFilters()`** - Applies search/filter
   ```javascript
   - Gets filter values
   - Calls loadOrders() with filters
   ```

**Modal Management:**
- Opens/closes modal
- Handles form submission
- Validates data
- Creates or updates orders

**Event Listeners:**
- Form submission
- Filter inputs (on input/change)
- Clear filters button
- Modal close buttons
- Edit/Delete buttons (via onclick)

---

## 4. Client-Side Data Flow

### Example: Creating an Order

```
1. User clicks "Add New Order" button
   ↓
2. JavaScript: addOrderBtn click handler
   ↓
3. Opens modal, resets form
   ↓
4. User fills form and clicks "Save"
   ↓
5. JavaScript: orderForm submit handler
   ↓
6. Prevents default form submission
   ↓
7. Collects form data into object
   ↓
8. Calls: ordersAPI.create(orderData)
   ↓
9. api.js: Makes POST request to php/orders.php
   ↓
10. Waits for response
   ↓
11. On success: Closes modal, refreshes orders list
   ↓
12. On error: Displays error message
```

### Example: Authentication Check

```
1. User navigates to dashboard.html
   ↓
2. Page loads, JavaScript executes
   ↓
3. dashboard.js: initDashboard() runs
   ↓
4. Calls: authAPI.check()
   ↓
5. api.js: GET request to php/auth.php?action=check
   ↓
6. PHP checks session, returns user data or 401
   ↓
7. JavaScript receives response
   ↓
8. If authenticated: Loads dashboard data
   ↓
9. If not authenticated: Redirects to index.html
```

---

## 5. DOM Manipulation

### How JavaScript Updates the UI:

**Example: Displaying Orders**
```javascript
// Get container element
const ordersList = document.getElementById('ordersList');

// Create HTML string
const html = orders.map(order => `
    <div class="order-card">
        <h3>${order.clientName}</h3>
        <p>${order.design}</p>
        ...
    </div>
`).join('');

// Insert into DOM
ordersList.innerHTML = html;
```

**Example: Showing Error Messages**
```javascript
const errorDiv = document.getElementById('loginError');
errorDiv.textContent = 'Invalid email or password';
errorDiv.classList.add('show');  // Makes it visible
```

**Example: Updating Statistics**
```javascript
document.getElementById('totalOrders').textContent = stats.total;
document.getElementById('pendingOrders').textContent = stats.pending;
```

---

## 6. Event Handling

### Form Submissions:
```javascript
form.addEventListener('submit', async (e) => {
    e.preventDefault();  // Stop default form submission
    // Handle form data
    // Make API call
    // Update UI
});
```

### Button Clicks:
```javascript
button.addEventListener('click', async () => {
    // Perform action
    // Make API call
    // Update UI
});
```

### Input Changes (Filters):
```javascript
input.addEventListener('input', () => {
    // Get input value
    // Apply filter
    // Reload data
});
```

---

## 7. Error Handling

### Try-Catch Blocks:
```javascript
try {
    const result = await authAPI.login(email, password);
    // Success handling
} catch (error) {
    // Error handling
    errorDiv.textContent = error.message;
    errorDiv.classList.add('show');
}
```

### User Feedback:
- Success messages (green)
- Error messages (red)
- Loading states
- Empty states

---

## 8. State Management

### Current State Variables:
- `currentUser` - Stores logged-in user data
- `currentEditId` - Tracks which order is being edited
- Filter values - Stored in form inputs

### Session State:
- Managed by PHP sessions (server-side)
- JavaScript checks session via API calls
- No client-side session storage

---

## 9. Responsive Design

### CSS Media Queries:
```css
@media (max-width: 768px) {
    /* Mobile styles */
    .orders-grid {
        grid-template-columns: 1fr;  /* Single column */
    }
}
```

### Flexible Layouts:
- CSS Grid for responsive cards
- Flexbox for navigation
- Percentage-based widths
- Mobile-friendly forms

---

## 10. Key Client-Side Features

✅ **Form Validation** - Before submission  
✅ **Real-time Filtering** - As user types  
✅ **Modal Dialogs** - For add/edit forms  
✅ **Dynamic Content** - Updates without page reload  
✅ **Error Handling** - User-friendly error messages  
✅ **Loading States** - Shows "Loading..." messages  
✅ **Empty States** - Shows message when no data  
✅ **Responsive Design** - Works on mobile/tablet/desktop  
✅ **Session Management** - Checks auth on page load  
✅ **Auto-redirect** - Based on authentication status  

---

## 11. File Dependencies

### HTML Files Load:
```
index.html → js/api.js + js/auth.js
signup.html → js/api.js + js/auth.js
dashboard.html → js/api.js + js/dashboard.js
orders.html → js/api.js + js/orders.js
```

### JavaScript Module Chain:
```
auth.js → imports from → api.js
dashboard.js → imports from → api.js
orders.js → imports from → api.js
```

---

## 12. Browser Compatibility

**Modern Browsers Support:**
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Uses ES6 features (async/await, modules, arrow functions)

**Required Features:**
- Fetch API
- ES6 Modules
- LocalStorage (optional)
- Session cookies

---

## Summary

The client-side is responsible for:
1. **User Interface** - HTML structure and CSS styling
2. **User Interactions** - Event handlers for clicks, forms, etc.
3. **Data Display** - Rendering data from API in HTML
4. **API Communication** - Making requests to PHP backend
5. **State Management** - Tracking current user, filters, etc.
6. **Error Handling** - Showing user-friendly error messages
7. **Navigation** - Redirecting based on authentication

All client-side code runs in the **user's browser** and communicates with the **PHP backend** via **HTTP requests** (API calls).




