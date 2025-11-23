# System Enhancement - Client & Admin Sides

## Overview

The system has been enhanced with **two separate sides**:

1. **Client Side** - Where clients can browse furniture and place orders
2. **Admin Side** - Where admins can manage all orders and furniture catalog

---

## New Features

### 1. **Furniture Catalog**
- Browse available furniture items
- View furniture details, measurements, features
- Place orders based on catalog items
- Admin can manage furniture catalog

### 2. **Client Portal**
- Client registration and login
- Browse furniture catalog
- Place custom orders
- View their own orders
- Track order status

### 3. **Admin Panel**
- View ALL client orders
- Manage furniture catalog (add, edit, delete)
- Update order statuses
- View statistics for all orders

---

## Database Changes

### New Table: `furniture`
Stores furniture catalog items with:
- Name, category, description
- Default measurements
- Price range
- Features
- Image URL
- Active status

### Updated Table: `users`
- Added `role` field: 'admin' or 'client'

### Updated Table: `orders`
- Added `furniture_id` to link orders to catalog items

---

## New Pages

### Client Side:
- `client-login.html` - Client login
- `client-signup.html` - Client registration
- `client-dashboard.html` - Client dashboard
- `client-catalog.html` - Browse furniture catalog
- `client-orders.html` - View client's orders
- `place-order.html` - Place custom order

### Admin Side:
- `admin-panel.html` - View all client orders
- `admin-furniture.html` - Manage furniture catalog

---

## Setup Instructions

### 1. Update Database
Run the new schema: `database/schema-v2.sql`
This will:
- Add `role` column to users table
- Create `furniture` table
- Add `furniture_id` to orders table
- Insert sample furniture items

### 2. Access Points

**Client Portal:**
- Login: `http://localhost/project/client-login.html`
- Signup: `http://localhost/project/client-signup.html`

**Admin Portal:**
- Login: `http://localhost/project/index.html` (existing)
- Admin Panel: `http://localhost/project/admin-panel.html`

---

## User Roles

### Client Role:
- Can browse furniture catalog
- Can place orders
- Can view their own orders
- Cannot see other clients' orders
- Cannot manage furniture

### Admin Role:
- Can view ALL client orders
- Can manage furniture catalog
- Can update order statuses
- Can see statistics for all orders
- Has access to admin panel

---

## Workflow

### Client Places Order:
1. Client logs in → `client-login.html`
2. Browses catalog → `client-catalog.html`
3. Selects furniture item
4. Fills order form with customizations
5. Submits order
6. Order appears in admin panel

### Admin Manages Orders:
1. Admin logs in → `index.html`
2. Goes to Admin Panel → `admin-panel.html`
3. Sees all client orders
4. Updates order status
5. Manages furniture catalog if needed

---

## Next Steps

1. Run `database/schema-v2.sql` to update database
2. Test client registration and login
3. Browse furniture catalog
4. Place test orders
5. View orders in admin panel

The system now supports both client and admin workflows!




