# Furniture Order Management System

A web-based digital management system designed for furniture makers to efficiently manage client designs, measurements, and instructions. This system helps streamline order processing, reduce errors, and improve customer satisfaction.

## Features

- **User Authentication**: Secure login and signup functionality using PHP sessions
- **Dashboard**: Overview of orders with statistics (Total, Pending, In Progress, Completed)
- **Order Management**: 
  - Create, view, edit, and delete client orders
  - Store client designs, measurements, and special instructions
  - Track order status (Pending, In Progress, Completed, Cancelled)
- **Filtering**: Filter orders by client name and status
- **Responsive Design**: Modern, mobile-friendly UI

## Technologies Used

- **HTML5**: Structure and markup
- **CSS3**: Styling and responsive design
- **JavaScript (ES6+)**: Client-side functionality
- **PHP**: Backend API and server-side logic
- **MySQL**: Database for data storage (via XAMPP)

## System Requirements

- XAMPP (or any PHP/MySQL server)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser (Chrome, Firefox, Edge, Safari)

## Setup Instructions

### Step 1: Install and Start XAMPP

1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start XAMPP Control Panel
3. Start **Apache** and **MySQL** services

### Step 2: Create the Database

1. Open phpMyAdmin by navigating to `http://localhost/phpmyadmin` in your browser
2. Click on the **SQL** tab
3. Open the file `database/schema.sql` from this project
4. Copy and paste the entire SQL script into the SQL tab
5. Click **Go** to execute the script
6. This will create:
   - Database: `furniture_orders`
   - Table: `users` (for authentication)
   - Table: `orders` (for order management)
   - A test admin user (email: `admin@furniture.com`, password: `admin123`)

**OR** manually create the database:

1. In phpMyAdmin, click "New" to create a database
2. Name it: `furniture_orders`
3. Select "utf8mb4_unicode_ci" as collation
4. Click "Create"
5. Import the SQL file from `database/schema.sql`

### Step 3: Configure Database Connection

1. Open `php/config.php`
2. Update the database credentials if needed (default XAMPP settings):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Empty by default in XAMPP
   define('DB_NAME', 'furniture_orders');
   ```
3. If you changed the MySQL root password, update `DB_PASS`

### Step 4: Place Project Files

1. Copy the entire project folder to `C:\xampp\htdocs\project` (or your XAMPP htdocs directory)
2. Ensure the folder structure is:
   ```
   C:\xampp\htdocs\project\
   ├── index.html
   ├── signup.html
   ├── dashboard.html
   ├── orders.html
   ├── css/
   ├── js/
   ├── php/
   └── database/
   ```

### Step 5: Access the Application

1. Open your web browser
2. Navigate to: `http://localhost/project/`
3. You should see the login page

## Usage Guide

### Creating an Account

1. Click "Sign Up" on the login page
2. Fill in your details:
   - Full Name
   - Email
   - Phone Number
   - Password (minimum 6 characters)
   - Confirm Password
3. Click "Sign Up"
4. You'll be redirected to the dashboard upon successful registration

### Logging In

1. Enter your email and password
2. Click "Login"
3. You'll be redirected to the dashboard

**Test Account:**
- Email: `admin@furniture.com`
- Password: `admin123`

### Managing Orders

1. **View Orders**: Navigate to the "Orders" page from the navigation menu
2. **Add New Order**: Click the "+ Add New Order" button
3. **Fill Order Details**:
   - Client Name (required)
   - Client Email (required)
   - Client Phone (optional)
   - Design Description (required)
   - Measurements (required)
   - Special Instructions (optional)
   - Status (required)
   - Due Date (optional)
4. **Edit Order**: Click the "Edit" button on any order card
5. **Delete Order**: Click the "Delete" button on any order card
6. **Filter Orders**: Use the filter section to search by client name or filter by status

### Dashboard

The dashboard provides:
- **Statistics Cards**: Total orders, Pending, Completed, In Progress
- **Recent Orders**: Last 5 orders with key information

## Project Structure

```
project/
├── index.html              # Login page
├── signup.html            # Registration page
├── dashboard.html         # Dashboard with statistics
├── orders.html            # Orders management page
├── css/
│   ├── style.css         # Main stylesheet
│   ├── dashboard.css     # Dashboard-specific styles
│   └── orders.css        # Orders page styles
├── js/
│   ├── api.js            # API helper functions
│   ├── auth.js           # Authentication logic
│   ├── dashboard.js      # Dashboard functionality
│   └── orders.js         # Orders management logic
├── php/
│   ├── config.php        # Database configuration
│   ├── auth.php          # Authentication API endpoints
│   ├── orders.php        # Orders CRUD API endpoints
│   └── dashboard.php     # Dashboard data API
├── database/
│   └── schema.sql        # Database schema and structure
└── README.md             # This file
```

## API Endpoints

### Authentication (`php/auth.php`)

- `GET ?action=check` - Check if user is authenticated
- `POST ?action=login` - Login user
- `POST ?action=signup` - Register new user
- `POST ?action=logout` - Logout user

### Orders (`php/orders.php`)

- `GET` - Get all orders (with optional filters: `?client_name=...&status=...`)
- `POST` - Create new order
- `PUT` - Update existing order
- `DELETE ?id=...` - Delete order

### Dashboard (`php/dashboard.php`)

- `GET` - Get dashboard statistics and recent orders

## Troubleshooting

### Database Connection Error

- **Problem**: "Connection failed" error
- **Solution**: 
  - Ensure MySQL is running in XAMPP Control Panel
  - Check database credentials in `php/config.php`
  - Verify database `furniture_orders` exists in phpMyAdmin

### Session Not Working

- **Problem**: User gets logged out frequently
- **Solution**: 
  - Check PHP session settings in `php.ini`
  - Ensure cookies are enabled in your browser
  - Clear browser cache and cookies

### 404 Errors on API Calls

- **Problem**: API endpoints return 404
- **Solution**:
  - Ensure Apache is running in XAMPP
  - Check that files are in correct directory structure
  - Verify file paths in `js/api.js` (should be `php/`)

### CORS Errors

- **Problem**: Cross-origin request blocked
- **Solution**: 
  - The PHP files already include CORS headers
  - If issues persist, check Apache configuration
  - Ensure you're accessing via `http://localhost` not `file://`

### Password Not Working

- **Problem**: Can't login with test account
- **Solution**:
  - Default password hash might need regeneration
  - Create a new account via signup page
  - Or reset password in database using:
    ```sql
    UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'admin@furniture.com';
    ```
    (This sets password to: `admin123`)

## Security Notes

### For Production Use:

1. **Update Database Password**: Change MySQL root password
2. **Update Security Rules**: Add proper input validation and sanitization
3. **Use Prepared Statements**: Already implemented in PHP files
4. **HTTPS**: Use SSL certificate for secure connections
5. **Session Security**: Configure secure session settings
6. **Password Hashing**: Already using `password_hash()` and `password_verify()`

## Future Enhancements

- File upload for design images
- Email notifications for order status changes
- Advanced reporting and analytics
- Multi-user collaboration
- Mobile app version
- Integration with CAD software
- Automated order tracking
- Client portal for order status
- PDF report generation
- Export orders to Excel/CSV

## Development Notes

- PHP sessions are used for authentication
- All database queries use prepared statements to prevent SQL injection
- Passwords are hashed using PHP's `password_hash()` function
- API responses are in JSON format
- CORS headers are included for cross-origin requests

## License

This project is created for academic/research purposes.

## Support

For issues or questions:
- Check XAMPP logs: `C:\xampp\apache\logs\error.log`
- Check PHP errors: Enable error display in `php.ini` or check error logs
- Verify database connection in phpMyAdmin

---

**Quick Start Checklist:**
- [ ] XAMPP installed and running (Apache + MySQL)
- [ ] Database created using `database/schema.sql`
- [ ] Project files in `htdocs/project` folder
- [ ] Database credentials updated in `php/config.php` (if needed)
- [ ] Access `http://localhost/project/` in browser
- [ ] Login with test account or create new account
