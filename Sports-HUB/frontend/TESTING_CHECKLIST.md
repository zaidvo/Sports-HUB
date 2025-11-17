# ✅ Testing Checklist - SportzHub Admin Features

## Prerequisites

- [ ] Backend server running on `http://localhost:8000`
- [ ] Admin account created and credentials ready
- [ ] Browser console open (F12) for debugging

---

## 🏟️ Courts Management Testing

### Add Court

- [ ] Click "+ Add New Court" button
- [ ] Verify form appears
- [ ] Fill all required fields:
  - [ ] Name: "Test Court"
  - [ ] Type: Select "Futsal"
  - [ ] Location: "Dubai"
  - [ ] Price: "50"
  - [ ] Status: "active"
  - [ ] Image URL: (optional)
- [ ] Click "Save Court"
- [ ] Verify success message appears
- [ ] Verify new court appears in table
- [ ] Verify form resets

### Edit Court

- [ ] Locate a court in the table
- [ ] Click "Edit" button
- [ ] Verify form scrolls into view
- [ ] Verify form is populated with court data
- [ ] Verify title says "Edit Court"
- [ ] Modify court name
- [ ] Click "Save Court"
- [ ] Verify success message: "Court updated successfully"
- [ ] Verify table shows updated information
- [ ] Verify form resets and title returns to "Add New Court"

### Delete Court

- [ ] Locate a court in the table
- [ ] Click "Delete" button
- [ ] Verify confirmation dialog appears
- [ ] Click "Cancel" - verify nothing happens
- [ ] Click "Delete" again, click "OK"
- [ ] Verify success message: "Court deleted successfully"
- [ ] Verify court removed from table

### Edge Cases

- [ ] Try editing non-existent court ID
- [ ] Try submitting form with missing required fields
- [ ] Try deleting court with active bookings (if backend prevents)

---

## 📅 Bookings Management Testing

### View Booking

- [ ] Navigate to "Manage Bookings"
- [ ] Click "View" button on any booking
- [ ] Verify modal appears with animation
- [ ] Verify all booking details displayed:
  - [ ] Customer name, email, phone
  - [ ] Court name
  - [ ] Date, time, duration
  - [ ] Total price
  - [ ] Status badge (colored)
  - [ ] Notes (if any)
- [ ] Click "Edit" button in modal
- [ ] Verify switches to edit modal
- [ ] Click "Close" button
- [ ] Verify modal closes
- [ ] Click "View" again
- [ ] Click outside modal
- [ ] Verify modal closes

### Edit Booking

- [ ] Click "Edit" button on any booking
- [ ] Verify edit modal appears
- [ ] Verify all fields are populated
- [ ] Modify customer name
- [ ] Modify date (select future date)
- [ ] Modify total price
- [ ] Click "Save Changes"
- [ ] Verify success message: "Booking updated successfully"
- [ ] Verify modal closes
- [ ] Verify table shows updated data
- [ ] Click "Edit" again
- [ ] Modify multiple fields
- [ ] Click "Cancel"
- [ ] Verify modal closes without saving

### Cancel Booking

- [ ] Find a booking with status "confirmed" or "pending"
- [ ] Verify "Cancel" button is visible
- [ ] Click "Cancel" button
- [ ] Verify confirmation dialog
- [ ] Click "Cancel" on dialog - verify nothing happens
- [ ] Click "Cancel" button again, click "OK"
- [ ] Verify success message: "Booking cancelled successfully"
- [ ] Verify status changes to "cancelled" with red badge
- [ ] Verify "Cancel" button is now hidden for this row

### Delete Booking

- [ ] Click "Delete" button on any booking
- [ ] Verify confirmation dialog: "permanently delete"
- [ ] Click "Cancel" - verify nothing happens
- [ ] Click "Delete" again, click "OK"
- [ ] Verify success message: "Booking deleted successfully"
- [ ] Verify booking removed from table

### Dashboard Integration

- [ ] Navigate to "Dashboard"
- [ ] Verify recent bookings table shows
- [ ] Click "View" button on any booking
- [ ] Verify modal works same as bookings page

---

## 🎨 UI/UX Testing

### Modals

- [ ] View modal has dark header with white text
- [ ] Edit modal has dark header with white text
- [ ] Both modals animate smoothly (fade + slide)
- [ ] Close button (×) works
- [ ] Click outside modal closes it
- [ ] Press ESC key (should close modal, if implemented)
- [ ] Scroll works if content overflows
- [ ] Modal centers on screen

### Buttons

- [ ] View button is cyan/teal color
- [ ] Edit button is blue
- [ ] Cancel button is yellow
- [ ] Delete button is red
- [ ] All buttons have hover effects
- [ ] Button text is readable
- [ ] Buttons are touch-friendly on mobile

### Status Badges

- [ ] Active/Confirmed: Green background
- [ ] Inactive/Cancelled: Red background
- [ ] Pending: Yellow background
- [ ] Completed: Blue background (if exists)

### Forms

- [ ] Required fields marked with \*
- [ ] Validation works (try empty submit)
- [ ] Date picker appears for date fields
- [ ] Time picker appears for time fields
- [ ] Dropdown shows all options
- [ ] Form fields have proper spacing
- [ ] Labels are above inputs

---

## 📱 Responsive Testing

### Desktop (1920x1080)

- [ ] Sidebar is fixed
- [ ] Tables show all columns
- [ ] Modals are 700px wide
- [ ] Action buttons are inline

### Tablet (768px)

- [ ] Layout remains functional
- [ ] Tables may scroll horizontally
- [ ] Modals are 90% width
- [ ] Forms remain 2-column

### Mobile (375px)

- [ ] Sidebar toggles (if implemented)
- [ ] Tables scroll horizontally
- [ ] Modals are full-width
- [ ] Forms stack vertically
- [ ] Buttons stack vertically
- [ ] Touch targets are 44px minimum

---

## 🔒 Security Testing

### Authentication

- [ ] Logout, try accessing admin pages
- [ ] Verify redirect to login
- [ ] Login as regular user
- [ ] Try accessing admin endpoints
- [ ] Verify access denied

### Authorization

- [ ] Check Bearer token is sent with all API calls
- [ ] Verify token in localStorage
- [ ] Clear localStorage, try actions
- [ ] Verify requests fail appropriately

---

## ⚡ Performance Testing

### Load Times

- [ ] Courts table loads within 1 second
- [ ] Bookings table loads within 1 second
- [ ] Modal opens immediately (< 300ms)
- [ ] Form submission responds quickly

### Animations

- [ ] All animations are smooth (60fps)
- [ ] No janky scrolling
- [ ] Modal transitions are fluid

---

## 🐛 Error Handling Testing

### Network Errors

- [ ] Stop backend server
- [ ] Try loading data
- [ ] Verify error message appears
- [ ] Try submitting form
- [ ] Verify error message appears

### Invalid Data

- [ ] Try editing booking with past date (if backend prevents)
- [ ] Try invalid email format
- [ ] Try negative price
- [ ] Try invalid court ID

### Edge Cases

- [ ] Empty tables (no courts/bookings)
- [ ] Very long text in fields
- [ ] Special characters in names
- [ ] Unicode characters

---

## 🌐 Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## ✅ API Integration Testing

### Courts

- [ ] GET /admin/courts returns list
- [ ] POST /admin/courts creates court
- [ ] GET /admin/courts/{id} returns single court
- [ ] PUT /admin/courts/{id} updates court
- [ ] DELETE /admin/courts/{id} removes court

### Bookings

- [ ] GET /admin/bookings returns list
- [ ] POST /admin/bookings creates booking
- [ ] GET /admin/bookings/{id} returns single booking
- [ ] PUT /admin/bookings/{id} updates booking
- [ ] PATCH /admin/bookings/{id}/cancel cancels booking
- [ ] DELETE /admin/bookings/{id} removes booking

---

## 📊 Console Testing

### Check for Errors

- [ ] Open browser console (F12)
- [ ] Navigate through all pages
- [ ] Perform all actions
- [ ] Verify no JavaScript errors
- [ ] Verify no 404 errors
- [ ] Verify no CORS errors

### Network Tab

- [ ] Check API calls are made correctly
- [ ] Verify status codes (200, 201, etc.)
- [ ] Verify Bearer token in headers
- [ ] Check response payloads

---

## 🎯 User Acceptance Testing

### Admin Workflow

- [ ] Login as admin
- [ ] Add 3 new courts
- [ ] Edit 2 courts
- [ ] Delete 1 court
- [ ] View 5 bookings
- [ ] Edit 3 bookings
- [ ] Cancel 2 bookings
- [ ] Delete 1 booking
- [ ] Verify everything works smoothly
- [ ] Logout

---

## 📝 Documentation Testing

- [ ] Read ADMIN_FEATURES.md
- [ ] Verify all features listed work
- [ ] Read UI_GUIDE.md
- [ ] Verify UI matches descriptions
- [ ] Read IMPLEMENTATION_SUMMARY.md
- [ ] Verify all 19 APIs are accessible

---

## ✅ Sign-Off

**Tester Name:** ********\_********
**Date:** ********\_********
**Version Tested:** 2.0

**Overall Status:**

- [ ] All tests passed ✅
- [ ] Minor issues found (list below)
- [ ] Major issues found (list below)

**Issues Found:**

1. ***
2. ***
3. ***

**Comments:**

---

---

---

---

**Testing Complete!** 🎉
