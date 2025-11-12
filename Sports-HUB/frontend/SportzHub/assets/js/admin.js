// SportzHub Admin JavaScript
// Handles admin panel functionality

class AdminManager {
  constructor() {
    this.currentUser = null;
    this.init();
  }

  init() {
    this.checkAuth();
    this.bindEvents();
    this.loadDashboardData();
  }

  bindEvents() {
    document.addEventListener("DOMContentLoaded", () => {
      // Logout button
      const logoutBtn = document.getElementById("logoutBtn");
      if (logoutBtn) {
        logoutBtn.addEventListener("click", () => this.logout());
      }

      // Mobile menu toggle
      const menuToggle = document.querySelector(".menu-toggle");
      if (menuToggle) {
        menuToggle.addEventListener("click", () => this.toggleSidebar());
      }

      // Form submissions
      this.bindFormEvents();
      this.bindTableEvents();
    });
  }

  async checkAuth() {
    try {
      // For now, check if user has admin role in localStorage
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

  async loadDashboardData() {
    if (!window.location.pathname.includes("dashboard.html")) return;

    try {
      const stats = await app.apiCall("admin/dashboard");
      if (stats.success && stats.stats) {
        this.updateDashboardStats(stats.stats);
      }

      // Load recent bookings
      const bookings = await app.apiCall("admin/bookings");
      if (bookings.success && bookings.bookings) {
        this.displayRecentBookings(bookings.bookings.slice(0, 5)); // Show recent 5
      }
    } catch (error) {
      console.error("Error loading dashboard data:", error);
      app.showMessage("Error loading dashboard data", "error");

      // Show demo data if API fails
      this.updateDashboardStats({
        total_bookings: 5,
        total_revenue: 250,
        active_courts: 3,
        todays_bookings: 2,
      });
    }
  }

  updateDashboardStats(stats) {
    const statElements = {
      totalBookings: document.getElementById("totalBookings"),
      totalRevenue: document.getElementById("totalRevenue"),
      activeCourts: document.getElementById("activeCourts"),
      todayBookings: document.getElementById("todayBookings"),
    };

    if (statElements.totalBookings)
      statElements.totalBookings.textContent =
        stats.total_bookings || stats.totalBookings || 0;
    if (statElements.totalRevenue)
      statElements.totalRevenue.textContent = `$${
        stats.total_revenue || stats.totalRevenue || 0
      }`;
    if (statElements.activeCourts)
      statElements.activeCourts.textContent =
        stats.active_courts || stats.activeCourts || 0;
    if (statElements.todayBookings)
      statElements.todayBookings.textContent =
        stats.todays_bookings || stats.todayBookings || 0;
  }

  displayRecentBookings(bookings) {
    const container = document.getElementById("recentBookingsTable");
    if (!container) return;

    container.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Court</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${bookings
                      .map(
                        (booking) => `
                        <tr>
                            <td>#${booking.id}</td>
                            <td>${booking.customer_name}</td>
                            <td>${booking.court_name}</td>
                            <td>${booking.date}</td>
                            <td>${booking.time}</td>
                            <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-small btn-view" onclick="adminManager.viewBooking(${booking.id})">View</button>
                                    <button class="btn btn-small btn-edit" onclick="adminManager.editBooking(${booking.id})">Edit</button>
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

  // Courts Management
  async loadCourts() {
    try {
      const response = await app.apiCall("courts");
      if (response.success && response.courts) {
        this.displayCourtsTable(response.courts);
      } else {
        throw new Error(response.message || "Failed to load courts");
      }
    } catch (error) {
      console.error("Error loading courts:", error);
      app.showMessage("Error loading courts", "error");
    }
  }

  displayCourtsTable(courts) {
    const container = document.getElementById("courtsTable");
    if (!container) return;

    container.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${courts
                      .map(
                        (court) => `
                        <tr>
                            <td>${court.court_id || court.id}</td>
                            <td>${court.court_name || court.name}</td>
                            <td>${court.court_type || court.type}</td>
                            <td>${court.location}</td>
                            <td>$${court.price_per_hour || court.price}</td>
                            <td><span class="status-badge status-active">${
                              court.is_active ? "Active" : "Inactive"
                            }</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-small btn-edit" onclick="adminManager.editCourt(${
                                      court.court_id || court.id
                                    })">Edit</button>
                                    <button class="btn btn-small btn-delete" onclick="adminManager.deleteCourt(${
                                      court.court_id || court.id
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

  // Bookings Management
  async loadBookings() {
    try {
      const response = await app.apiCall("admin/bookings");
      if (response.success && response.bookings) {
        this.displayBookingsTable(response.bookings);
      } else {
        throw new Error(response.message || "Failed to load bookings");
      }
    } catch (error) {
      console.error("Error loading bookings:", error);
      app.showMessage("Error loading bookings", "error");
    }
  }

  displayBookingsTable(bookings) {
    const container = document.getElementById("bookingsTable");
    if (!container) return;

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
                            <td>#${booking.id}</td>
                            <td>${booking.customer_name}<br><small>${booking.customer_email}</small></td>
                            <td>${booking.court_name}</td>
                            <td>${booking.date}</td>
                            <td>${booking.time}</td>
                            <td>${booking.duration}h</td>
                            <td>$${booking.total}</td>
                            <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-small btn-view" onclick="adminManager.viewBooking(${booking.id})">View</button>
                                    <button class="btn btn-small btn-edit" onclick="adminManager.editBooking(${booking.id})">Edit</button>
                                    <button class="btn btn-small btn-delete" onclick="adminManager.cancelBooking(${booking.id})">Cancel</button>
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

  bindFormEvents() {
    // Court form
    const courtForm = document.getElementById("courtForm");
    if (courtForm) {
      courtForm.addEventListener("submit", (e) => this.handleCourtSubmit(e));
    }

    // Booking form
    const bookingForm = document.getElementById("bookingForm");
    if (bookingForm) {
      bookingForm.addEventListener("submit", (e) =>
        this.handleBookingSubmit(e)
      );
    }
  }

  bindTableEvents() {
    // Load data based on current page
    if (window.location.pathname.includes("courts.html")) {
      this.loadCourts();
    } else if (window.location.pathname.includes("bookings.html")) {
      this.loadBookings();
    }
  }

  async handleCourtSubmit(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const courtData = Object.fromEntries(formData);

    try {
      const response = await app.apiCall("admin/courts", "POST", courtData);
      if (response.success) {
        app.showMessage("Court saved successfully", "success");
        this.loadCourts();
        event.target.reset();
      } else {
        app.showMessage(response.message || "Error saving court", "error");
      }
    } catch (error) {
      console.error("Error saving court:", error);
      app.showMessage("Error saving court", "error");
    }
  }

  async editCourt(courtId) {
    try {
      const court = await app.apiCall(`admin/courts/${courtId}`);
      this.showCourtEditModal(court);
    } catch (error) {
      console.error("Error loading court:", error);
      app.showMessage("Error loading court details", "error");
    }
  }

  async deleteCourt(courtId) {
    if (!confirm("Are you sure you want to delete this court?")) return;

    try {
      const response = await app.apiCall(`admin/courts/${courtId}`, "DELETE");
      if (response.success) {
        app.showMessage("Court deleted successfully", "success");
        this.loadCourts();
      } else {
        app.showMessage(response.message || "Error deleting court", "error");
      }
    } catch (error) {
      console.error("Error deleting court:", error);
      app.showMessage("Error deleting court", "error");
    }
  }

  async viewBooking(bookingId) {
    try {
      const booking = await app.apiCall(`admin/bookings/${bookingId}`);
      this.showBookingModal(booking);
    } catch (error) {
      console.error("Error loading booking:", error);
      app.showMessage("Error loading booking details", "error");
    }
  }

  showBookingModal(booking) {
    // Implementation for booking details modal
    console.log("Show booking modal:", booking);
  }

  toggleSidebar() {
    const sidebar = document.querySelector(".admin-sidebar");
    if (sidebar) {
      sidebar.classList.toggle("open");
    }
  }

  async logout() {
    try {
      await app.apiCall("admin/auth/logout", "POST");
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      window.location.href = "../login.html";
    }
  }
}

// Initialize admin manager when on admin pages
if (window.location.pathname.includes("/admin/")) {
  const adminManager = new AdminManager();
}
