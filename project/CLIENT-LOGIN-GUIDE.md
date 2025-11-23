# How Client Login Works

## For Users (Simple Steps)

### Step 1: Access Login Page
1. Open your browser
2. Go to: `http://localhost/project/client-login.html`
3. You'll see the login form

### Step 2: Enter Credentials
1. **Email**: Enter your registered email address
   - Example: `john@example.com`
2. **Password**: Enter your password
   - Click the eye icon (👁️) to show/hide password
3. **Optional**: Check "Remember me" to stay logged in

### Step 3: Click Login
1. Click the "Login" button
2. Wait for authentication (you'll see "⏳ Logging in...")
3. If successful → Redirected to Client Dashboard
4. If failed → Error message appears

### Step 4: Access Dashboard
- After successful login, you'll be redirected to:
  - `http://localhost/project/client-dashboard.html`
- From there you can:
  - Browse furniture catalog
  - Place orders
  - View your orders

---

## Technical Flow (How It Works Behind the Scenes)

### 1. User Submits Form
```
User enters email & password → Clicks "Login" button
```

### 2. JavaScript Captures Form
```javascript
// File: js/client-auth.js
- Prevents default form submission
- Gets email and password from form inputs
- Validates email format
- Shows loading state on button
```

### 3. API Call Made
```javascript
// Calls: authAPI.login(email, password)
// Makes POST request to: php/auth.php?action=login
// Sends JSON: { email: "...", password: "..." }
```

### 4. PHP Backend Processes
```php
// File: php/auth.php
1. Receives email and password
2. Connects to MySQL database
3. Queries: SELECT * FROM users WHERE email = ?
4. Verifies password using password_verify()
5. Checks user role (client or admin)
6. Creates PHP session
7. Returns success response with user data
```

### 5. Session Created
```php
// PHP creates session with:
$_SESSION['user_id'] = user ID
$_SESSION['user_email'] = user email
$_SESSION['user_name'] = user name
$_SESSION['user_role'] = 'client'
```

### 6. JavaScript Receives Response
```javascript
// If successful:
- Redirects to client-dashboard.html
- If user is admin → redirects to dashboard.html instead

// If failed:
- Shows error message
- Resets button state
```

### 7. Protected Pages Check Authentication
```javascript
// On every protected page load:
- Calls authAPI.check()
- PHP checks if session exists
- If no session → redirects to login
- If session exists → allows access
```

---

## Login Requirements

### To Login, You Need:
1. **Registered Account**
   - Must have signed up first at `client-signup.html`
   - Email must be in database
   - Password must match

### Account Types:
- **Client Account** → Logs into client portal
- **Admin Account** → Logs into admin panel (even from client-login.html)

---

## Example Login Flow

```
1. User visits: client-login.html
   ↓
2. Enters: john@example.com / mypassword123
   ↓
3. Clicks "Login"
   ↓
4. JavaScript validates email format
   ↓
5. API call: POST php/auth.php?action=login
   ↓
6. PHP checks database:
   - Finds user with email "john@example.com"
   - Verifies password hash matches
   ↓
7. PHP creates session
   ↓
8. Returns: { success: true, user: {...} }
   ↓
9. JavaScript redirects to: client-dashboard.html
   ↓
10. Dashboard checks auth → Session exists → Shows dashboard
```

---

## Error Handling

### Common Errors:

**"Invalid email or password"**
- Email not found in database
- Password is incorrect
- Solution: Check credentials or create account

**"Please enter a valid email address"**
- Email format is wrong
- Solution: Use format like: user@example.com

**"Please fill in all required fields"**
- Email or password is empty
- Solution: Fill in both fields

**"Login failed. Please try again."**
- Network error or server issue
- Solution: Check internet connection, try again

---

## Security Features

1. **Password Hashing**
   - Passwords stored as hashes (not plain text)
   - Uses PHP's `password_hash()` and `password_verify()`

2. **Session Management**
   - PHP sessions used for authentication
   - Session ID stored in secure cookie

3. **SQL Injection Protection**
   - All queries use prepared statements
   - Parameters bound separately

4. **Email Validation**
   - Client-side and server-side validation
   - Prevents invalid email formats

---

## Testing Login

### Test Account (if exists):
- Email: `test@example.com`
- Password: `test123`

### Or Create New Account:
1. Go to: `client-signup.html`
2. Fill in registration form
3. Create account
4. Then login with those credentials

---

## Files Involved

1. **Frontend:**
   - `client-login.html` - Login page UI
   - `js/client-auth.js` - Login logic
   - `js/api.js` - API communication

2. **Backend:**
   - `php/auth.php` - Authentication processing
   - `php/config.php` - Database connection

3. **Database:**
   - `users` table - Stores user credentials

---

## Quick Reference

**Login URL:** `http://localhost/project/client-login.html`

**After Login:** `http://localhost/project/client-dashboard.html`

**Signup URL:** `http://localhost/project/client-signup.html`

**Admin Login:** `http://localhost/project/index.html`

---

That's how client login works! Simple for users, secure behind the scenes. 🔐


