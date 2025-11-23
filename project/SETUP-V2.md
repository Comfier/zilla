# Setup Guide for Enhanced System (v2)

## Quick Setup Steps

### 1. Update Database Schema

Run the new schema file to add furniture catalog and role support:

**Option A: Via phpMyAdmin**
1. Open `http://localhost/phpmyadmin`
2. Select `furniture_orders` database
3. Go to SQL tab
4. Open and run `database/schema-v2.sql`

**Option B: Via Setup Script**
1. Run: `http://localhost/project/setup-database-v2.php` (if created)

### 2. Access Points

**Client Portal:**
- Client Login: `http://localhost/project/client-login.html`
- Client Signup: `http://localhost/project/client-signup.html`
- Client Dashboard: `http://localhost/project/client-dashboard.html`
- Browse Catalog: `http://localhost/project/client-catalog.html`

**Admin Portal:**
- Admin Login: `http://localhost/project/index.html`
- Admin Panel: `http://localhost/project/admin-panel.html`
- Manage Furniture: `http://localhost/project/admin-furniture.html`

### 3. Test Accounts

**Admin:**
- Email: `admin@furniture.com`
- Password: `admin123`
- Run `set-admin-password.php` if login doesn't work

**Client:**
- Create new account via `client-signup.html`
- Or use any email to register as client

### 4. Features to Test

**As Client:**
1. Register/Login
2. Browse furniture catalog
3. Place order from catalog
4. Place custom order
5. View your orders

**As Admin:**
1. Login
2. View all client orders in Admin Panel
3. Update order statuses
4. Manage furniture catalog (add/edit/delete)
5. View statistics

## Database Changes

The new schema adds:
- `role` column to `users` table
- `furniture` table with 10 sample items
- `furniture_id` column to `orders` table

## System Complete! 🎉

All files have been created. The system now supports:
- ✅ Client and Admin roles
- ✅ Furniture catalog
- ✅ Client order placement
- ✅ Admin order management
- ✅ Furniture management




