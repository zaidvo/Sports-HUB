# 🔧 Admin API Error Fix Guide

## 🔴 Error Identified

**Error**: `Failed to load resource: the server responded with a status of 404 (Not Found)` or `403 Forbidden`

**Location**: `admin.js:187:33`

**Cause**: The admin is trying to access protected API endpoints without a valid JWT token.

---

## 🎯 Root Cause

The admin panel requires authentication with a valid JWT token. The error occurs when:

1. **No token exists** - User hasn't logged in
2. **Invalid token** - Token expired or malformed
3. **Wrong role** - User is not an admin

---

## ✅ Solution Steps

### Step 1: Create Admin Users in Database

You need admin users in the database first. Run this command:

```bash
cd backend
php create_admin.php
```

This will create two admin users:

- Email: `shahzaib@admin.com` | Password: `123`
- Email: `sufyan@admin.com` | Password: `123`

**Output should be**:

```
Creating admin users...

✓ Created/Updated: Shahzaib Admin (shahzaib@admin.com)
✓ Created/Updated: Sufyan Admin (sufyan@admin.com)

✅ Admin users created successfully!
```

---

### Step 2: Login as Admin

1. Go to: `http://localhost:3000/pages/login.html`
2. Enter credentials:
   - Email: `shahzaib@admin.com`
   - Password: `123`
3. Click "Login"

**What happens**:

- Backend validates credentials
- Generates JWT token
- Returns user data + token
- Frontend stores token in localStorage
- Redirects to admin dashboard

---

### Step 3: Verify Token is Stored

Open browser console (F12) and run:

```javascript
console.log("Token:", localStorage.getItem("userToken"));
console.log("Role:", localStorage.getItem("userRole"));
console.log("Name:", localStorage.getItem("userName"));
```

**Expected output**:

```
Token: eyJ0eXAiOiJKV1QiLCJhbGc...  (long string)
Role: admin
Name: Shahzaib Admin
```

If token is `null`, you need to login again.

---

### Step 4: Test API Endpoints

After login, the admin panel should work. Test each endpoint:

**Dashboard**:

```
GET http://localhost:8000/admin/dashboard
Headers: Authorization: Bearer {token}
```

**Courts**:

```
GET http://localhost:8000/admin/courts
Headers: Authorization: Bearer {token}
```

**Bookings**:

```
GET http://localhost:8000/admin/bookings
Headers: Authorization: Bearer {token}
```

---

## 🔍 Troubleshooting

### Issue 1: "403 Forbidden"

**Cause**: Token is invalid or user is not admin

**Fix**:

1. Clear localStorage: `localStorage.clear()`
2. Login again as admin
3. Check user role in database:
   ```sql
   SELECT id, name, email, role FROM users WHERE email = 'shahzaib@admin.com';
   ```
   Role should be `'admin'`, not `'user'`

---

### Issue 2: "401 Unauthorized"

**Cause**: Token expired or missing

**Fix**:

1. Logout and login again
2. Check JWT_EXPIRES_IN in `.env` (default: 3600 seconds = 1 hour)
3. Increase expiration if needed:
   ```
   JWT_EXPIRES_IN=86400  # 24 hours
   ```

---

### Issue 3: "404 Not Found"

**Cause**: Backend route not registered or backend not running

**Fix**:

1. Check backend is running:
   ```bash
   curl http://localhost:8000/admin/dashboard
   ```
2. Restart backend:
   ```bash
   cd backend
   php -S localhost:8000 -t public
   ```
3. Check routes in `backend/public/index.php`

---

### Issue 4: CORS Error

**Cause**: Cross-origin request blocked

**Fix**: Backend already has CORS headers in `index.php`:

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

If still blocked, check browser console for specific CORS error.

---

## 🧪 Testing Checklist

### Before Testing

- [ ] Backend is running (`php -S localhost:8000 -t public`)
- [ ] Admin users created (`php create_admin.php`)
- [ ] Database has admin users with role='admin'

### Login Test

- [ ] Navigate to login page
- [ ] Enter admin credentials
- [ ] Click login
- [ ] Check console for token
- [ ] Verify redirect to admin dashboard

### Dashboard Test

- [ ] Dashboard loads without errors
- [ ] Stats display (bookings, revenue, courts, users)
- [ ] Recent bookings table shows data
- [ ] No 403/404 errors in console

### Courts Management Test

- [ ] Navigate to "Manage Courts"
- [ ] Courts table loads
- [ ] Can add new court
- [ ] Can edit existing court
- [ ] Can delete court

### Bookings Management Test

- [ ] Navigate to "Manage Bookings"
- [ ] Bookings table loads
- [ ] Can view booking details
- [ ] Can edit booking
- [ ] Can cancel booking
- [ ] Can delete booking

---

## 📊 API Response Examples

### Successful Dashboard Response

```json
{
  "totals": {
    "users": 5,
    "courts": 5,
    "bookings": 10,
    "revenue": 1250.5
  },
  "recent_bookings": [
    {
      "id": 1,
      "customer_name": "John Doe",
      "court_name": "Futsal Court Alpha",
      "booking_date": "2025-11-15",
      "start_time": "10:00:00",
      "total_price": "50.00",
      "status": "confirmed"
    }
  ]
}
```

### Successful Courts Response

```json
{
  "courts": [
    {
      "id": 1,
      "name": "Futsal Court Alpha",
      "type": "Futsal",
      "location": "Downtown Sports Complex",
      "price_per_hour": "50.00",
      "status": "active",
      "image_url": "https://..."
    }
  ]
}
```

### Error Response (403)

```json
{
  "message": "Access denied. Admin privileges required."
}
```

### Error Response (401)

```json
{
  "message": "Invalid or expired token."
}
```

---

## 🔐 Security Notes

### JWT Token Structure

```
Header.Payload.Signature
```

**Payload contains**:

- `sub`: User ID
- `role`: User role (admin/user)
- `iat`: Issued at timestamp
- `exp`: Expiration timestamp

### Token Validation

Backend validates:

1. Token signature is valid
2. Token hasn't expired
3. User exists in database
4. User has admin role (for admin endpoints)

---

## 🚀 Quick Fix Commands

### 1. Create Admin Users

```bash
cd backend
php create_admin.php
```

### 2. Start Backend

```bash
cd backend
php -S localhost:8000 -t public
```

### 3. Test Login API

```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"shahzaib@admin.com","password":"123"}'
```

### 4. Test Admin Dashboard (with token)

```bash
curl http://localhost:8000/admin/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## ✅ Expected Behavior

### After Successful Login:

1. **Dashboard Page**:

   - Shows 4 stat cards with numbers
   - Shows recent bookings table
   - No errors in console

2. **Courts Page**:

   - Shows list of courts
   - Add/Edit/Delete buttons work
   - Form submission works

3. **Bookings Page**:
   - Shows list of bookings
   - View/Edit/Cancel/Delete buttons work
   - Modal dialogs work

---

## 📝 Summary

**The admin panel requires**:

1. ✅ Admin users in database (run `php create_admin.php`)
2. ✅ Valid login (use admin credentials)
3. ✅ JWT token stored in localStorage
4. ✅ Backend running on port 8000

**Once logged in, all API calls will work!** 🎉

---

**Generated**: November 14, 2025  
**Status**: ✅ FIX GUIDE COMPLETE
