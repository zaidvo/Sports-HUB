# SportzHub Frontend

A modern, responsive frontend application for sports court booking management system.

## 🏗️ Project Structure

```
SportzHub/
├── 📁 assets/
│   ├── 📁 css/
│   │   ├── style.css           # Main stylesheet (responsive design)
│   │   └── admin.css           # Admin panel specific styles
│   ├── 📁 js/
│   │   ├── main.js             # Core functionality & utilities
│   │   ├── booking.js          # Booking system logic
│   │   ├── courts.js           # Court listing & filtering
│   │   └── admin.js            # Admin panel functionality
│   └── 📁 images/
│       └── README.txt          # Image requirements guide
├── 📁 pages/
│   ├── index.html              # Homepage
│   ├── courts.html             # Court listings
│   ├── booking.html            # Booking form
│   ├── login.html              # User/admin login
│   ├── register.html           # User registration
│   └── 📁 admin/
│       ├── dashboard.html      # Admin dashboard
│       ├── courts.html         # Court management
│       └── bookings.html       # Booking management
├── 📁 components/
│   ├── header.html             # Shared navigation
│   └── footer.html             # Shared footer
├── index.html                  # Entry point (redirects to pages/)
└── README.md                   # This file
```

## 🚀 Getting Started

### Prerequisites

- Web server (local or hosted)
- Modern web browser
- Backend API (SportzHub backend should be running)

### Quick Start

1. **Static File Server (Development)**

   ```bash
   # Using Python 3
   cd frontend/SportzHub
   python -m http.server 8080

   # Using Node.js
   npx serve -s . -l 8080

   # Using PHP
   php -S localhost:8080
   ```

2. **Open in Browser**
   ```
   http://localhost:8080
   ```

### Production Deployment

1. **Upload Files**

   - Upload all files to your web server
   - Ensure proper directory structure is maintained

2. **Configure Backend API**

   - Update API endpoints in `assets/js/main.js`
   - Modify `apiCall()` function base URL

3. **Add Images**
   - Add required images to `assets/images/` (see README.txt in that folder)

## ✨ Features

### User Features

- **Responsive Design** - Mobile-friendly interface
- **Court Browsing** - Filter by sport type, search, sort
- **Easy Booking** - Step-by-step booking process
- **User Registration** - Account creation with validation
- **Real-time Availability** - Live court availability checking

### Admin Features

- **Dashboard** - Statistics and overview
- **Court Management** - Add, edit, delete courts
- **Booking Management** - View, edit, cancel bookings
- **Responsive Admin Panel** - Mobile-friendly admin interface

### Technical Features

- **Component-based Architecture** - Reusable header/footer
- **Modern JavaScript** - ES6+ features, async/await
- **CSS Grid & Flexbox** - Modern layout techniques
- **Form Validation** - Client-side validation
- **Error Handling** - User-friendly error messages

## 🔧 Configuration

### API Configuration

Edit `assets/js/main.js` to configure your backend API:

```javascript
// Update this line in main.js
const response = await fetch(`YOUR_BACKEND_URL/api/${endpoint}`, config);
```

### Styling Customization

- **Main styles**: `assets/css/style.css`
- **Admin styles**: `assets/css/admin.css`
- **Brand colors**: Update CSS variables in style.css

## 📱 Browser Support

- ✅ Chrome 60+
- ✅ Firefox 55+
- ✅ Safari 12+
- ✅ Edge 79+

## 🎯 Pages Overview

### Public Pages

- **Homepage** (`pages/index.html`) - Hero section, features, call-to-action
- **Courts** (`pages/courts.html`) - Browse available courts with filters
- **Booking** (`pages/booking.html`) - Multi-step booking process
- **Login** (`pages/login.html`) - User/admin authentication
- **Register** (`pages/register.html`) - User account creation

### Admin Pages

- **Dashboard** (`pages/admin/dashboard.html`) - Statistics and overview
- **Courts** (`pages/admin/courts.html`) - Court management interface
- **Bookings** (`pages/admin/bookings.html`) - Booking management interface

## 🔌 API Integration

The frontend expects the following API endpoints:

### Public API

- `GET /api/courts` - List all courts
- `GET /api/courts/{id}` - Get court details
- `GET /api/courts/{id}/availability` - Get court availability
- `POST /api/bookings` - Create booking
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration

### Admin API

- `GET /api/admin/dashboard/stats` - Dashboard statistics
- `GET /api/admin/courts` - Court management
- `POST /api/admin/courts` - Create/update court
- `DELETE /api/admin/courts/{id}` - Delete court
- `GET /api/admin/bookings` - Booking management

## 🎨 Customization

### Colors & Branding

Update the CSS variables in `assets/css/style.css`:

```css
:root {
  --primary-color: #1e3c72;
  --secondary-color: #ff6b35;
  --accent-color: #2a5298;
}
```

### Adding New Sports

1. Add new court type to admin forms
2. Add corresponding image to `assets/images/`
3. Update filter buttons in courts.html

## 🐛 Troubleshooting

### Common Issues

1. **Components not loading**

   - Check file paths in header/footer includes
   - Ensure web server serves .html files correctly

2. **API calls failing**

   - Verify backend is running
   - Check CORS configuration
   - Update API base URL in main.js

3. **Images not displaying**

   - Add required images to assets/images/
   - Check image file names match code expectations

4. **Styling issues**
   - Verify CSS files are loading correctly
   - Check for JavaScript errors in console

## 📄 License

This project is part of the SportzHub application suite.

## 🤝 Contributing

1. Follow the existing code structure
2. Maintain responsive design principles
3. Test on multiple browsers
4. Update documentation when adding features

---

**Next Steps:**

1. Add required images to `assets/images/`
2. Configure API endpoints to match your backend
3. Customize branding and colors
4. Test all functionality
5. Deploy to production server
