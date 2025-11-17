// SportzHub Admin JavaScript - Refactored to match backend API
// Handles admin panel functionality with proper backend integration

class AdminManager {
  constructor() {
    this.currentUser = null;
    this.courts = [];
    this.bookings = [];
    this.init();
  }

  init() {
    this.checkAuth();
    this.bindEvents();
    this.loadDashboardData();
  }

  bindEvents() {
    document.addEventListener("DOMContentLoaded", () => {
      const logoutBtn = document.getElementById("logoutBtn");
      if (logoutBtn) {
        logoutBtn.addEventListener("click", () => this.logout());
      }

      this.bindFormEvents();
      this.bindTableEvents();
    });
  }

  async checkAuth() {
    try {
      const userToken = localStorage.getItem("userToken");
      const userRole = localStorage.getItem("userRole");
      const userName = localStorage.getItem("userName");

      if (!userToken || userRole !== "admin") {
        window.location.href = "../login.html";
        return;
      }

      this.currentUser = {
        name: userName || "Admin User",
        role: userRole,
      };
      this.updateUserInfo();
    } catch (error) {
      console.error("Auth check failed:", error);
      window.location.href = "../login.html";
    }
  }

  updateUserInfo() {
    const userNameEl = document.getElementById("userName");
    if (userNameEl && this.currentUser) {
      userNameEl.textContent = this.currentUser.name;
    }
  }

  logout() {
    if (confirm("Are you sure you want to logout?")) {
      localStorage.clear();
      window.location.href = "../login.html";
    }
  }

  async loadDashboardData() {
    if (!window.location.pathname.includes("dashboard.html")) return;

    try {
      const userToken = localStorage.getItem("userToken");

      // Load dashboard stats - Backend returns: { totals: {...}, recent_bookings: [...] }
      const response = await fetch("http://localhost:8000/admin/dashboard", {
        headers: {
          Authorization: `Bearer ${userToken}`,
        },
      });

      if (!response.ok) throw new Error("Failed to load dashboard");

      const data = await response.json();

      // Update stats
      if (data.totals) {
        this.updateDashboardStats(data.totals);
      }

      // Display recent bookings
      if (data.recent_bookings && data.recent_bookings.length > 0) {
        this.displayRecentBookings(data.recent_bookings);
      } else {
        this.displayRecentBookings([]);
      }
    } catch (error) {
      console.error("Error loading dashboard data:", error);
      this.showMessage("Error loading dashboard data", "error");
    }
  }

  updateDashboardStats(totals) {
    // Backend returns: { users, courts, bookings, revenue }
    const elements = {
      totalBookings: document.getElementById("totalBookings"),
      totalRevenue: document.getElementById("totalRevenue"),
      activeCourts: document.getElementById("activeCourts"),
      todayBookings: document.getElementById("todayBookings"),
    };

    if (elements.totalBookings)
      elements.totalBookings.textContent = totals.bookings || 0;
    if (elements.totalRevenue)
      elements.totalRevenue.textContent = `$${(totals.revenue || 0).toFixed(
        2
      )}`;
    if (elements.activeCourts)
      elements.activeCourts.textContent = totals.courts || 0;
    if (elements.todayBookings)
      elements.todayBookings.textContent = totals.bookings || 0;
  }

  displayRecentBookings(bookings) {
    const container = document.getElementById("recentBookingsTable");
    if (!container) return;

    if (!bookings || bookings.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">📅</div>
          <p>No recent bookings yet</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Court</th>
            <th>Date</th>
            <th>Time</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          ${bookings
            .map(
              (booking) => `
            <tr>
              <td><strong>#${booking.id}</strong></td>
              <td>${booking.customer_name}</td>
              <td>${booking.court_name || "N/A"}</td>
              <td>${booking.booking_date}</td>
              <td>${booking.start_time}</td>
              <td>$${parseFloat(booking.total_price || 0).toFixed(2)}</td>
              <td><span class="status-badge status-${booking.status}">${
                booking.status
              }</span></td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    `;
  }

  // ==================== COURTS MANAGEMENT ====================

  async loadCourts() {
    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch("http://localhost:8000/admin/courts", {
        headers: {
          Authorization: `Bearer ${userToken}`,
        },
      });

      if (!response.ok) throw new Error("Failed to load courts");

      const data = await response.json();
      this.courts = data.courts || data;
      this.displayCourtsTable(this.courts);
    } catch (error) {
      console.error("Error loading courts:", error);
      this.showMessage("Error loading courts", "error");
    }
  }

  displayCourtsTable(courts) {
    const container = document.getElementById("courtsTable");
    if (!container) return;

    if (!courts || courts.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">🏟️</div>
          <p>No courts found. Add your first court!</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Location</th>
            <th>Price/Hour</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${courts
            .map(
              (court) => `
            <tr>
              <td>${court.id}</td>
              <td>${court.name}</td>
              <td>${court.type}</td>
              <td>${court.location}</td>
              <td>$${parseFloat(court.price_per_hour).toFixed(2)}</td>
              <td><span class="status-badge status-${
                court.status || "active"
              }">${
                (court.status || "active").charAt(0).toUpperCase() +
                (court.status || "active").slice(1)
              }</span></td>
              <td>
                <div class="action-buttons">
                  <button class="btn btn-small btn-primary" onclick="adminManager.editCourt(${
                    court.id
                  })">Edit</button>
                  <button class="btn btn-small btn-delete" onclick="adminManager.deleteCourt(${
                    court.id
                  })">Delete</button>
                </div>
              </td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    `;
  }

  async editCourt(courtId) {
    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/courts/${courtId}`,
        {
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Court not found");

      const data = await response.json();
      const court = data.court || data;

      // Show form
      document.getElementById("courtFormSection").style.display = "block";
      document.getElementById("formTitle").textContent = "Edit Court";

      // Populate form
      document.getElementById("courtId").value = court.id;
      document.getElementById("courtName").value = court.name;
      document.getElementById("courtType").value = court.type;
      document.getElementById("location").value = court.location;
      document.getElementById("price").value = court.price_per_hour;
      document.getElementById("status").value = court.status || "active";
      document.getElementById("imageUrl").value = court.image_url || "";

      // Scroll to form
      document
        .getElementById("courtFormSection")
        .scrollIntoView({ behavior: "smooth" });
    } catch (error) {
      console.error("Error loading court:", error);
      this.showMessage("Error loading court details", "error");
    }
  }

  async deleteCourt(courtId) {
    if (!confirm("Are you sure you want to delete this court?")) return;

    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/courts/${courtId}`,
        {
          method: "DELETE",
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Failed to delete court");

      this.showMessage("Court deleted successfully", "success");
      this.loadCourts();
    } catch (error) {
      console.error("Error deleting court:", error);
      this.showMessage("Error deleting court", "error");
    }
  }

  async handleCourtSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const courtId = document.getElementById("courtId").value;

    // Match backend API structure
    const courtData = {
      name: formData.get("name"),
      type: formData.get("type"),
      location: formData.get("location"),
      price_per_hour: parseFloat(formData.get("price")),
      status: formData.get("status") || "active",
      image_url: formData.get("image_url") || null,
    };

    try {
      const userToken = localStorage.getItem("userToken");
      const url = courtId
        ? `http://localhost:8000/admin/courts/${courtId}`
        : "http://localhost:8000/admin/courts";
      const method = courtId ? "PUT" : "POST";

      const response = await fetch(url, {
        method: method,
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userToken}`,
        },
        body: JSON.stringify(courtData),
      });

      if (!response.ok) throw new Error("Failed to save court");

      const data = await response.json();
      this.showMessage(
        courtId ? "Court updated successfully" : "Court created successfully",
        "success"
      );

      this.loadCourts();
      event.target.reset();
      document.getElementById("courtId").value = "";
      document.getElementById("formTitle").textContent = "Add New Court";
      document.getElementById("courtFormSection").style.display = "none";
    } catch (error) {
      console.error("Error saving court:", error);
      this.showMessage("Error saving court", "error");
    }
  }

  // ==================== BOOKINGS MANAGEMENT ====================

  async loadBookings() {
    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch("http://localhost:8000/admin/bookings", {
        headers: {
          Authorization: `Bearer ${userToken}`,
        },
      });

      if (!response.ok) throw new Error("Failed to load bookings");

      const data = await response.json();
      this.bookings = data.bookings || data;
      this.displayBookingsTable(this.bookings);
    } catch (error) {
      console.error("Error loading bookings:", error);
      this.showMessage("Error loading bookings", "error");
    }
  }

  displayBookingsTable(bookings) {
    const container = document.getElementById("bookingsTable");
    if (!container) return;

    if (!bookings || bookings.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">📅</div>
          <p>No bookings found</p>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Court</th>
            <th>Date</th>
            <th>Time</th>
            <th>Duration</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${bookings
            .map(
              (booking) => `
            <tr>
              <td><strong>#${booking.id}</strong></td>
              <td>
                ${booking.customer_name}<br>
                <small style="color: #6c757d;">${booking.customer_email}</small>
              </td>
              <td>${booking.court_name || "N/A"}</td>
              <td>${booking.booking_date}</td>
              <td>${booking.start_time}</td>
              <td>${booking.duration}h</td>
              <td>$${parseFloat(booking.total_price || 0).toFixed(2)}</td>
              <td><span class="status-badge status-${booking.status}">${
                booking.status
              }</span></td>
              <td>
                <div class="action-buttons">
                  <button class="btn btn-small btn-info" onclick="adminManager.viewBooking(${
                    booking.id
                  })">View</button>
                  <button class="btn btn-small btn-primary" onclick="adminManager.editBookingModal(${
                    booking.id
                  })">Edit</button>
                  ${
                    booking.status !== "cancelled"
                      ? `<button class="btn btn-small btn-warning" onclick="adminManager.cancelBooking(${booking.id})">Cancel</button>`
                      : ""
                  }
                  <button class="btn btn-small btn-delete" onclick="adminManager.deleteBooking(${
                    booking.id
                  })">Delete</button>
                </div>
              </td>
            </tr>
          `
            )
            .join("")}
        </tbody>
      </table>
    `;
  }

  async viewBooking(bookingId) {
    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/bookings/${bookingId}`,
        {
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Booking not found");

      const data = await response.json();
      const booking = data.booking || data;

      const modalHtml = `
        <div class="modal-overlay" id="bookingViewModal" onclick="adminManager.closeModal()">
          <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
              <h3>Booking Details - #${booking.id}</h3>
              <button class="close-btn" onclick="adminManager.closeModal()">×</button>
            </div>
            <div class="modal-body">
              <div class="booking-details-grid">
                <div class="detail-item">
                  <strong>Customer Name:</strong>
                  <span>${booking.customer_name}</span>
                </div>
                <div class="detail-item">
                  <strong>Email:</strong>
                  <span>${booking.customer_email}</span>
                </div>
                <div class="detail-item">
                  <strong>Phone:</strong>
                  <span>${booking.customer_phone}</span>
                </div>
                <div class="detail-item">
                  <strong>Court:</strong>
                  <span>${booking.court_name || "N/A"}</span>
                </div>
                <div class="detail-item">
                  <strong>Date:</strong>
                  <span>${booking.booking_date}</span>
                </div>
                <div class="detail-item">
                  <strong>Start Time:</strong>
                  <span>${booking.start_time}</span>
                </div>
                <div class="detail-item">
                  <strong>Duration:</strong>
                  <span>${booking.duration} hour(s)</span>
                </div>
                <div class="detail-item">
                  <strong>Total Price:</strong>
                  <span>$${parseFloat(booking.total_price || 0).toFixed(
                    2
                  )}</span>
                </div>
                <div class="detail-item">
                  <strong>Status:</strong>
                  <span class="status-badge status-${booking.status}">${
        booking.status
      }</span>
                </div>
                ${
                  booking.notes
                    ? `
                <div class="detail-item full-width">
                  <strong>Notes:</strong>
                  <span>${booking.notes}</span>
                </div>
                `
                    : ""
                }
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" onclick="adminManager.editBookingModal(${
                booking.id
              })">Edit</button>
              <button class="btn btn-secondary" onclick="adminManager.closeModal()">Close</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML("beforeend", modalHtml);
    } catch (error) {
      console.error("Error loading booking:", error);
      this.showMessage("Error loading booking details", "error");
    }
  }

  async editBookingModal(bookingId) {
    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/bookings/${bookingId}`,
        {
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Booking not found");

      const data = await response.json();
      const booking = data.booking || data;

      this.closeModal();

      const modalHtml = `
        <div class="modal-overlay" id="bookingEditModal" onclick="adminManager.closeModal()">
          <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
              <h3>Edit Booking - #${booking.id}</h3>
              <button class="close-btn" onclick="adminManager.closeModal()">×</button>
            </div>
            <div class="modal-body">
              <form id="editBookingForm" class="booking-edit-form">
                <input type="hidden" id="editBookingId" value="${booking.id}">
                
                <div class="form-row">
                  <div class="form-group">
                    <label for="editCustomerName">Customer Name</label>
                    <input type="text" id="editCustomerName" value="${
                      booking.customer_name
                    }" required>
                  </div>
                  <div class="form-group">
                    <label for="editCustomerEmail">Email</label>
                    <input type="email" id="editCustomerEmail" value="${
                      booking.customer_email
                    }" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editCustomerPhone">Phone</label>
                    <input type="tel" id="editCustomerPhone" value="${
                      booking.customer_phone
                    }" required>
                  </div>
                  <div class="form-group">
                    <label for="editCourtId">Court ID</label>
                    <input type="number" id="editCourtId" value="${
                      booking.court_id
                    }" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editBookingDate">Date</label>
                    <input type="date" id="editBookingDate" value="${
                      booking.booking_date
                    }" required>
                  </div>
                  <div class="form-group">
                    <label for="editStartTime">Start Time</label>
                    <input type="time" id="editStartTime" value="${
                      booking.start_time
                    }" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editDuration">Duration (hours)</label>
                    <input type="number" id="editDuration" value="${
                      booking.duration
                    }" min="1" required>
                  </div>
                  <div class="form-group">
                    <label for="editTotalPrice">Total Price ($)</label>
                    <input type="number" id="editTotalPrice" value="${
                      booking.total_price
                    }" step="0.01" required>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editStatus">Status</label>
                    <select id="editStatus" required>
                      <option value="pending" ${
                        booking.status === "pending" ? "selected" : ""
                      }>Pending</option>
                      <option value="confirmed" ${
                        booking.status === "confirmed" ? "selected" : ""
                      }>Confirmed</option>
                      <option value="cancelled" ${
                        booking.status === "cancelled" ? "selected" : ""
                      }>Cancelled</option>
                      <option value="completed" ${
                        booking.status === "completed" ? "selected" : ""
                      }>Completed</option>
                    </select>
                  </div>
                </div>

                <div class="form-group full-width">
                  <label for="editNotes">Notes</label>
                  <textarea id="editNotes" rows="3">${
                    booking.notes || ""
                  }</textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary" onclick="adminManager.submitBookingEdit()">Save Changes</button>
              <button class="btn btn-secondary" onclick="adminManager.closeModal()">Cancel</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML("beforeend", modalHtml);
    } catch (error) {
      console.error("Error loading booking:", error);
      this.showMessage("Error loading booking details", "error");
    }
  }

  async submitBookingEdit() {
    const bookingId = document.getElementById("editBookingId").value;

    const bookingData = {
      court_id: parseInt(document.getElementById("editCourtId").value),
      booking_date: document.getElementById("editBookingDate").value,
      start_time: document.getElementById("editStartTime").value,
      duration: parseInt(document.getElementById("editDuration").value),
      customer_name: document.getElementById("editCustomerName").value,
      customer_email: document.getElementById("editCustomerEmail").value,
      customer_phone: document.getElementById("editCustomerPhone").value,
      status: document.getElementById("editStatus").value,
      total_price: parseFloat(document.getElementById("editTotalPrice").value),
      notes: document.getElementById("editNotes").value || "",
    };

    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/bookings/${bookingId}`,
        {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${userToken}`,
          },
          body: JSON.stringify(bookingData),
        }
      );

      if (!response.ok) throw new Error("Failed to update booking");

      this.showMessage("Booking updated successfully", "success");
      this.closeModal();
      this.loadBookings();
    } catch (error) {
      console.error("Error updating booking:", error);
      this.showMessage("Error updating booking", "error");
    }
  }

  async cancelBooking(bookingId) {
    if (!confirm("Are you sure you want to cancel this booking?")) return;

    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/bookings/${bookingId}/cancel`,
        {
          method: "PATCH",
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Failed to cancel booking");

      this.showMessage("Booking cancelled successfully", "success");
      this.loadBookings();
    } catch (error) {
      console.error("Error cancelling booking:", error);
      this.showMessage("Error cancelling booking", "error");
    }
  }

  async deleteBooking(bookingId) {
    if (!confirm("Are you sure you want to permanently delete this booking?"))
      return;

    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch(
        `http://localhost:8000/admin/bookings/${bookingId}`,
        {
          method: "DELETE",
          headers: {
            Authorization: `Bearer ${userToken}`,
          },
        }
      );

      if (!response.ok) throw new Error("Failed to delete booking");

      this.showMessage("Booking deleted successfully", "success");
      this.loadBookings();
    } catch (error) {
      console.error("Error deleting booking:", error);
      this.showMessage("Error deleting booking", "error");
    }
  }

  async handleBookingSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    // Match backend API structure for POST /admin/bookings
    const bookingData = {
      court_id: parseInt(formData.get("court_id")),
      booking_date: formData.get("booking_date"),
      start_time: formData.get("start_time"),
      duration: parseInt(formData.get("duration")),
      customer_name: formData.get("customer_name"),
      customer_email: formData.get("customer_email"),
      customer_phone: formData.get("customer_phone"),
      total_price: parseFloat(formData.get("total_price")),
      status: formData.get("status") || "confirmed",
      notes: formData.get("notes") || "",
    };

    try {
      const userToken = localStorage.getItem("userToken");
      const response = await fetch("http://localhost:8000/admin/bookings", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userToken}`,
        },
        body: JSON.stringify(bookingData),
      });

      if (!response.ok) throw new Error("Failed to create booking");

      this.showMessage("Booking created successfully", "success");
      this.loadBookings();
      event.target.reset();
      document.getElementById("bookingFormSection").style.display = "none";
    } catch (error) {
      console.error("Error creating booking:", error);
      this.showMessage("Error creating booking", "error");
    }
  }

  // ==================== UTILITY METHODS ====================

  bindFormEvents() {
    const courtForm = document.getElementById("courtForm");
    if (courtForm) {
      courtForm.addEventListener("submit", (e) => this.handleCourtSubmit(e));
    }

    const bookingForm = document.getElementById("bookingForm");
    if (bookingForm) {
      bookingForm.addEventListener("submit", (e) =>
        this.handleBookingSubmit(e)
      );
    }
  }

  bindTableEvents() {
    if (window.location.pathname.includes("courts.html")) {
      this.loadCourts();
    } else if (window.location.pathname.includes("bookings.html")) {
      this.loadBookings();
    }
  }

  closeModal() {
    const modals = document.querySelectorAll(".modal-overlay");
    modals.forEach((modal) => modal.remove());
  }

  showMessage(message, type = "info") {
    // Create toast notification
    const toast = document.createElement("div");
    toast.className = `alert alert-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
      position: fixed;
      top: 90px;
      right: 20px;
      z-index: 9999;
      padding: 1rem 1.5rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = "slideOut 0.3s ease";
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }
}

// Initialize admin manager
const adminManager = new AdminManager();

// Add CSS animations
const style = document.createElement("style");
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(400px);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(400px);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
