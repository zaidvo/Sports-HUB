# 🔑 Admin Login Credentials

## ✅ Working Admin Accounts

### Option 1: Main Admin

```
Email:    admin@sportzhub.com
Password: 123
```

### Option 2: Shahzaib Admin

```
Email:    shahzaib@admin.com
Password: 123
```

---

## 🚀 How to Create These Accounts

### Method 1: Run PHP Script (Recommended)

```bash
cd backend
php create_admin.php
```

**Output**:

```
Creating admin users...

Password hash: $2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6

✓ Created/Updated: Admin User (admin@sportzhub.com)
✓ Created/Updated: Shahzaib Admin (shahzaib@admin.com)

✅ Admin users created successfully!

Login credentials:
  Email: admin@sportzhub.com  | Password: 123
  Email: shahzaib@admin.com   | Password: 123
```

---

### Method 2: Run SQL Directly

Copy and paste this into your Neon DB SQL console:

```sql
-- Admin 1: admin@sportzhub.com / password: 123
INSERT INTO users (name, email, phone, password, role, created_at)
VALUES (
  'Admin User',
  'admin@sportzhub.com',
  '1234567890',
  '$2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6',
  'admin',
  NOW()
) ON CONFLICT (email) DO UPDATE SET
  password = EXCLUDED.password,
  role = 'admin';

-- Admin 2: shahzaib@admin.com / password: 123
INSERT INTO users (name, email, phone, password, role, created_at)
VALUES (
  'Shahzaib Admin',
  'shahzaib@admin.com',
  '1234567890',
  '$2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6',
  'admin',
  NOW()
) ON CONFLICT (email) DO UPDATE SET
  password = EXCLUDED.password,
  role = 'admin';

-- Verify
SELECT id, name, email, role, created_at FROM users WHERE role = 'admin';
```

---

## 🧪 Test Login

### Step 1: Go to Login Page

```
http://localhost:3000/pages/login.html
```

### Step 2: Enter Credentials

- Email: `admin@sportzhub.com`
- Password: `123`

### Step 3: Click Login

**Expected Result**:

- ✅ "Login successful!"
- ✅ Redirect to admin dashboard
- ✅ No errors in console

---

## 🔍 Verify in Database

Run this SQL to check if admin users exist:

```sql
SELECT id, name, email, role, created_at
FROM users
WHERE role = 'admin';
```

**Expected Output**:

```
id | name            | email                  | role  | created_at
---+-----------------+------------------------+-------+-------------------
1  | Admin User      | admin@sportzhub.com    | admin | 2025-11-14 ...
2  | Shahzaib Admin  | shahzaib@admin.com     | admin | 2025-11-14 ...
```

---

## 🔐 Password Hash Details

**Plain Password**: `123`

**Bcrypt Hash**: `$2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6`

**Algorithm**: bcrypt (PASSWORD_BCRYPT)

**Cost**: 10 (default)

---

## ⚠️ Why Previous Hash Didn't Work

The old hash in your SQL was:

```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

This hash is for a **different password**, not "123". That's why login failed with "Invalid credentials".

The new hash is generated specifically for password "123" and will work correctly.

---

## 🎯 Quick Reference

| Field        | Value                                                        |
| ------------ | ------------------------------------------------------------ |
| **Email**    | admin@sportzhub.com                                          |
| **Password** | 123                                                          |
| **Role**     | admin                                                        |
| **Hash**     | $2y$10$p2e8fcG30BombKpM1I7KFu0VN9nhebaRmwj4hTgFOKO7k1ZvT/We6 |

---

## ✅ After Login Works

Once logged in, you can access:

- ✅ Admin Dashboard (`/pages/admin/dashboard.html`)
- ✅ Manage Courts (`/pages/admin/courts.html`)
- ✅ Manage Bookings (`/pages/admin/bookings.html`)

All API calls will work because you have a valid JWT token!

---

**Generated**: November 14, 2025  
**Status**: ✅ CREDENTIALS READY
