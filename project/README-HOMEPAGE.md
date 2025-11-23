# Homepage Setup Guide

## New Homepage Feature

The system now has a **public homepage** that shows furniture catalog to everyone, and redirects clients to register when they want to order.

## How It Works

### 1. Homepage (`home.html`)
- **Public Access**: Anyone can view furniture without logging in
- **Shows Catalog**: Displays all active furniture items
- **Order Button**: When clicked, checks if user is logged in
  - **If logged in** → Redirects to catalog page to place order
  - **If not logged in** → Shows modal prompting to register/login

### 2. User Flow

```
Visitor → home.html (Browse furniture)
    ↓
Clicks "Order Now"
    ↓
Not logged in? → Modal appears
    ↓
"Create Account" → client-signup.html
    ↓
After signup → Auto-login → Can place order
```

### 3. File Structure

- **`home.html`** - Public homepage with furniture catalog
- **`index.html`** - Redirects to home.html (default entry point)
- **`admin-login.html`** - Separate admin login page
- **`client-login.html`** - Client login page
- **`client-signup.html`** - Client registration

## Access Points

### For Visitors/Clients:
- **Homepage**: `http://localhost/project/home.html`
- **Or**: `http://localhost/project/` (redirects to home.html)
- **Sign Up**: `http://localhost/project/client-signup.html`
- **Login**: `http://localhost/project/client-login.html`

### For Admins:
- **Admin Login**: `http://localhost/project/admin-login.html`

## Features

✅ **Public Catalog Viewing** - No login required to browse  
✅ **Smart Order Redirect** - Prompts registration when ordering  
✅ **Seamless Registration** - Easy signup flow  
✅ **Auto-Login After Signup** - Immediately can place orders  
✅ **Category Filtering** - Filter furniture by category  
✅ **Responsive Design** - Works on all devices  

## Testing

1. **Visit Homepage**: `http://localhost/project/home.html`
2. **Browse Furniture**: See all available items
3. **Click "Order Now"**: Modal appears asking to register
4. **Click "Create Account"**: Goes to signup page
5. **After Signup**: Can immediately place orders

## Navigation

- **Homepage** → Shows furniture, prompts registration
- **Client Signup** → Create account
- **Client Login** → Login to existing account
- **Admin Login** → Admin access

The homepage is now the default entry point! 🏠


