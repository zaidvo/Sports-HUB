# 🎨 SportzHub Admin UI - Visual Guide

## Courts Management Page

### Table View

```
┌─────────────────────────────────────────────────────────────────────────┐
│ ID │ Name          │ Type      │ Location  │ Price │ Status │ Actions  │
├─────────────────────────────────────────────────────────────────────────┤
│ 1  │ Court Alpha   │ Futsal    │ Dubai     │ $50   │ Active │ [Edit]   │
│    │               │           │           │       │        │ [Delete] │
├─────────────────────────────────────────────────────────────────────────┤
│ 2  │ Court Beta    │ Badminton │ Abu Dhabi │ $35   │ Active │ [Edit]   │
│    │               │           │           │       │        │ [Delete] │
└─────────────────────────────────────────────────────────────────────────┘
```

### Actions:

- **[Edit]** (Blue Button) - Loads court data into form for editing
- **[Delete]** (Red Button) - Permanently deletes court (with confirmation)

---

## Bookings Management Page

### Table View

```
┌───────────────────────────────────────────────────────────────────────────────────────┐
│ ID │ Customer    │ Court       │ Date       │ Time  │ Duration │ Total │ Status    │ Actions       │
├───────────────────────────────────────────────────────────────────────────────────────┤
│ #1 │ John Doe    │ Court Alpha │ 2025-11-15 │ 14:00 │ 2h       │ $100  │ Confirmed │ [View]        │
│    │ john@x.com  │             │            │       │          │       │           │ [Edit]        │
│    │             │             │            │       │          │       │           │ [Cancel]      │
│    │             │             │            │       │          │       │           │ [Delete]      │
├───────────────────────────────────────────────────────────────────────────────────────┤
│ #2 │ Jane Smith  │ Court Beta  │ 2025-11-16 │ 10:00 │ 1h       │ $35   │ Cancelled │ [View]        │
│    │ jane@x.com  │             │            │       │          │       │           │ [Edit]        │
│    │             │             │            │       │          │       │           │ [Delete]      │
└───────────────────────────────────────────────────────────────────────────────────────┘
```

### Actions:

- **[View]** (Cyan Button) - Opens modal with full booking details
- **[Edit]** (Blue Button) - Opens modal with editable form
- **[Cancel]** (Yellow Button) - Sets status to 'cancelled' (hidden if already cancelled)
- **[Delete]** (Red Button) - Permanently removes booking (with confirmation)

---

## View Booking Modal

```
╔════════════════════════════════════════════════════════════╗
║  Booking Details - #1                                   [×]║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  Customer Name:          Email:                            ║
║  John Doe                john@example.com                  ║
║                                                            ║
║  Phone:                  Court:                            ║
║  +971501234567          Court Alpha                        ║
║                                                            ║
║  Date:                   Start Time:                       ║
║  2025-11-15             14:00                              ║
║                                                            ║
║  Duration:               Total Price:                      ║
║  2 hour(s)              $100.00                            ║
║                                                            ║
║  Status:                                                   ║
║  [Confirmed]                                               ║
║                                                            ║
║  Notes:                                                    ║
║  Customer requested quiet hours                            ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║                              [Edit] [Close]                ║
╚════════════════════════════════════════════════════════════╝
```

**Features:**

- Clean 2-column grid layout
- Color-coded status badges
- Direct access to edit mode
- Click outside or × to close

---

## Edit Booking Modal

```
╔════════════════════════════════════════════════════════════╗
║  Edit Booking - #1                                      [×]║
╠════════════════════════════════════════════════════════════╣
║                                                            ║
║  Customer Name:                Email:                      ║
║  [John Doe____________]       [john@example.com_____]     ║
║                                                            ║
║  Phone:                        Court ID:                   ║
║  [+971501234567_______]       [1___]                      ║
║                                                            ║
║  Date:                         Start Time:                 ║
║  [2025-11-15__________]       [14:00___]                  ║
║                                                            ║
║  Duration (hours):             Total Price ($):            ║
║  [2___]                       [100.00_____]               ║
║                                                            ║
║  Status:                                                   ║
║  [Confirmed ▼]                                            ║
║   - Pending                                               ║
║   - Confirmed                                             ║
║   - Cancelled                                             ║
║   - Completed                                             ║
║                                                            ║
║  Notes:                                                    ║
║  [Customer requested quiet hours___________________]      ║
║  [________________________________________________]      ║
║                                                            ║
╠════════════════════════════════════════════════════════════╣
║                        [Save Changes] [Cancel]             ║
╚════════════════════════════════════════════════════════════╝
```

**Features:**

- All fields editable
- Date/time pickers
- Status dropdown
- Real-time validation
- Pre-populated with current data

---

## Court Edit Form (Inline)

When "Edit" is clicked, the "Add New Court" form is populated:

```
┌─────────────────────────────────────────────────────────────┐
│ Edit Court                                                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Court Name:                   Court Type:                  │
│  [Court Alpha_________]        [Futsal ▼]                  │
│                                                             │
│  Location:                     Price per Hour:              │
│  [Dubai_______________]        [50.00_____]                │
│                                                             │
│  Status:                       Image URL:                   │
│  [Active ▼]                    [https://...____________]   │
│                                                             │
│  [Save Court] [Cancel]                                      │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Features:**

- Inline form editing
- Auto-scrolls to form
- Title changes to "Edit Court"
- Reverts to "Add New Court" on cancel/submit

---

## Color Scheme

### Buttons

- **Primary (Blue):** `#3498db` - Edit, View
- **Success (Green):** `#27ae60` - Save, Confirm
- **Warning (Yellow):** `#f39c12` - Cancel
- **Danger (Red):** `#e74c3c` - Delete
- **Info (Cyan):** `#17a2b8` - View Details
- **Secondary (Gray):** `#6c757d` - Cancel/Close

### Status Badges

- **Active/Confirmed:** Green (`#d4edda`)
- **Inactive/Cancelled:** Red (`#f8d7da`)
- **Pending:** Yellow (`#fff3cd`)
- **Completed:** Blue (`#d1ecf1`)

---

## Responsive Behavior

### Desktop (> 768px)

- Side-by-side form fields
- Multi-column tables
- Modal: 700px max-width
- Action buttons inline

### Tablet (768px - 480px)

- Stacked form fields
- Scrollable tables
- Modal: 90% width
- Action buttons inline

### Mobile (< 480px)

- Full-width forms
- Single column layout
- Full-screen modals
- Stacked action buttons

---

## User Flow Examples

### Adding a Court:

1. Click "+ Add New Court" button
2. Form appears below header
3. Fill in required fields
4. Click "Save Court"
5. Success message appears
6. Form resets, table refreshes

### Editing a Court:

1. Click "Edit" button in table
2. Form auto-populates with court data
3. Title changes to "Edit Court"
4. Modify desired fields
5. Click "Save Court"
6. Success message, form resets, table refreshes

### Viewing a Booking:

1. Click "View" button in bookings table
2. Modal slides up with booking details
3. Review information
4. Click "Edit" to switch to edit mode
5. Or click "Close" / outside to dismiss

### Editing a Booking:

1. Click "Edit" button in bookings table
2. Edit modal opens with form
3. Modify any field
4. Click "Save Changes"
5. Modal closes, table refreshes

### Cancelling a Booking:

1. Click "Cancel" button (yellow)
2. Confirmation dialog appears
3. Confirm action
4. Status changes to "cancelled"
5. "Cancel" button disappears from row

### Deleting Records:

1. Click "Delete" button (red)
2. Confirmation dialog: "Are you sure?"
3. Confirm deletion
4. Record removed from database
5. Success message, table refreshes

---

## Keyboard Shortcuts

- **ESC:** Close any open modal
- **Enter:** Submit active form
- **Tab:** Navigate form fields

---

## Animations

- **Modal Open:** Fade in (0.3s) + Slide up (0.3s)
- **Modal Close:** Fade out (0.3s)
- **Button Hover:** Color transition (0.3s)
- **Table Row Hover:** Background fade (0.2s)

---

## Accessibility Features

✅ Semantic HTML5
✅ ARIA labels where needed
✅ Keyboard navigation
✅ Focus indicators
✅ Contrast ratios meet WCAG AA
✅ Screen reader friendly
✅ Touch-friendly buttons (min 44px)

---

**Design System:** Professional admin panel following material design principles
**Framework:** Vanilla JavaScript + Custom CSS
**Icons:** Unicode emoji for cross-browser compatibility
