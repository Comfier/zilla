# Quick Setup Guide

## Step-by-Step Setup for XAMPP

### 1. Start XAMPP Services
- Open XAMPP Control Panel
- Click **Start** for **Apache**
- Click **Start** for **MySQL**

### 2. Create Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click **SQL** tab
3. Copy contents from `database/schema.sql`
4. Paste and click **Go**

### 3. Verify Database
- Database `furniture_orders` should be created
- Tables `users` and `orders` should exist

### 4. Access Application
- Open browser: `http://localhost/project/`
- Login with:
  - Email: `admin@furniture.com`
  - Password: `admin123`

### 5. Test Account Creation
- Click "Sign Up" to create your own account
- Fill in the form and register

## Troubleshooting

**Can't connect to database?**
- Check MySQL is running in XAMPP
- Verify `php/config.php` has correct credentials

**404 errors?**
- Ensure Apache is running
- Check files are in `C:\xampp\htdocs\project\`

**Session issues?**
- Clear browser cookies
- Check PHP session settings

## File Structure Check

Make sure you have:
```
project/
├── index.html
├── signup.html
├── dashboard.html
├── orders.html
├── css/ (3 files)
├── js/ (4 files)
├── php/ (4 files)
└── database/ (schema.sql)
```




