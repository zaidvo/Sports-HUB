// SportzHub Booking JavaScript
// Handles booking form functionality and validation

class BookingManager {
  constructor() {
    this.selectedCourt = null;
    this.selectedDate = null;
    this.selectedTime = null;
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadCourts();
    this.initDatePicker();
  }

  bindEvents() {
    document.addEventListener("DOMContentLoaded", () => {
      const bookingForm = document.getElementById("bookingForm");
      if (bookingForm) {
        bookingForm.addEventListener("submit", (e) =>
          this.handleBookingSubmit(e)
        );
      }

      // Court selection
      document.addEventListener("click", (e) => {
        if (e.target.classList.contains("court-select-btn")) {
          this.selectCourt(e.target.dataset.courtId);
        }
      });

      // Date change
      const dateInput = document.getElementById("bookingDate");
      if (dateInput) {
        dateInput.addEventListener("change", (e) => this.handleDateChange(e));
      }

      // Time slot selection
      document.addEventListener("click", (e) => {
        if (e.target.classList.contains("time-slot")) {
          this.selectTimeSlot(e.target);
        }
      });
    });
  }

  async loadCourts() {
    try {
      const courts = await app.apiCall("courts");
      this.displayCourts(courts);
    } catch (error) {
      console.error("Error loading courts:", error);
      app.showMessage("Error loading courts", "error");
    }
  }

  displayCourts(courts) {
    const courtsList = document.getElementById("courtsList");
    if (!courtsList) return;

    courtsList.innerHTML = courts
      .map(
        (court) => `
            <div class="card court-card" data-court-id="${court.id}">
                <h3>${court.name}</h3>
                <p><strong>Type:</strong> ${court.type}</p>
                <p><strong>Location:</strong> ${court.location}</p>
                <p><strong>Price:</strong> $${court.price}/hour</p>
                <button class="btn btn-primary court-select-btn" data-court-id="${court.id}">
                    Select Court
                </button>
            </div>
        `
      )
      .join("");
  }

  selectCourt(courtId) {
    // Remove previous selections
    document.querySelectorAll(".court-card").forEach((card) => {
      card.classList.remove("selected");
    });

    // Mark selected court
    const courtCard = document.querySelector(`[data-court-id="${courtId}"]`);
    if (courtCard) {
      courtCard.classList.add("selected");
      this.selectedCourt = courtId;
      this.loadAvailableSlots();
    }
  }

  initDatePicker() {
    const dateInput = document.getElementById("bookingDate");
    if (dateInput) {
      // Set minimum date to today
      const today = new Date().toISOString().split("T")[0];
      dateInput.min = today;

      // Set maximum date to 30 days from today
      const maxDate = new Date();
      maxDate.setDate(maxDate.getDate() + 30);
      dateInput.max = maxDate.toISOString().split("T")[0];
    }
  }

  handleDateChange(event) {
    this.selectedDate = event.target.value;
    if (this.selectedCourt) {
      this.loadAvailableSlots();
    }
  }

  async loadAvailableSlots() {
    if (!this.selectedCourt || !this.selectedDate) return;

    try {
      const slots = await app.apiCall(
        `courts/${this.selectedCourt}/availability?date=${this.selectedDate}`
      );
      this.displayTimeSlots(slots);
    } catch (error) {
      console.error("Error loading time slots:", error);
      app.showMessage("Error loading available time slots", "error");
    }
  }

  displayTimeSlots(slots) {
    const slotsContainer = document.getElementById("timeSlots");
    if (!slotsContainer) return;

    slotsContainer.innerHTML = `
            <h3>Available Time Slots</h3>
            <div class="time-slots-grid">
                ${slots
                  .map(
                    (slot) => `
                    <button class="btn time-slot ${
                      slot.available ? "" : "disabled"
                    }" 
                            data-time="${slot.time}" 
                            ${!slot.available ? "disabled" : ""}>
                        ${slot.time}
                        ${!slot.available ? " (Booked)" : ""}
                    </button>
                `
                  )
                  .join("")}
            </div>
        `;
  }

  selectTimeSlot(slotElement) {
    if (slotElement.disabled) return;

    // Remove previous selections
    document.querySelectorAll(".time-slot").forEach((slot) => {
      slot.classList.remove("selected");
    });

    // Mark selected slot
    slotElement.classList.add("selected");
    this.selectedTime = slotElement.dataset.time;
  }

  async handleBookingSubmit(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const bookingData = {
      court_id: this.selectedCourt,
      date: this.selectedDate,
      time: this.selectedTime,
      duration: formData.get("duration"),
      customer_name: formData.get("customerName"),
      customer_email: formData.get("customerEmail"),
      customer_phone: formData.get("customerPhone"),
      notes: formData.get("notes"),
    };

    // Validation
    if (!this.validateBooking(bookingData)) {
      return;
    }

    try {
      const response = await app.apiCall("bookings", "POST", bookingData);
      if (response.success) {
        app.showMessage(
          "Booking confirmed! Check your email for confirmation.",
          "success"
        );
        this.resetForm();
      } else {
        app.showMessage(response.message || "Booking failed", "error");
      }
    } catch (error) {
      console.error("Booking error:", error);
      app.showMessage("Error processing booking", "error");
    }
  }

  validateBooking(data) {
    const errors = [];

    if (!data.court_id) errors.push("Please select a court");
    if (!data.date) errors.push("Please select a date");
    if (!data.time) errors.push("Please select a time slot");
    if (!data.customer_name || data.customer_name.length < 2)
      errors.push("Please enter a valid name");
    if (!app.validateEmail(data.customer_email))
      errors.push("Please enter a valid email");
    if (!app.validatePhone(data.customer_phone))
      errors.push("Please enter a valid phone number");

    if (errors.length > 0) {
      app.showMessage(errors.join("<br>"), "error");
      return false;
    }

    return true;
  }

  resetForm() {
    document.getElementById("bookingForm").reset();
    document
      .querySelectorAll(".selected")
      .forEach((el) => el.classList.remove("selected"));
    this.selectedCourt = null;
    this.selectedDate = null;
    this.selectedTime = null;
  }
}

// Initialize booking manager when on booking page
if (window.location.pathname.includes("booking.html")) {
  const bookingManager = new BookingManager();
}
