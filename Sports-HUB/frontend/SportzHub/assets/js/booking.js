// SportzHub Booking JavaScript
// Handles booking form functionality and validation

class BookingManager {
  constructor() {
    this.selectedCourt = null;
    this.selectedDate = null;
    this.selectedTime = null;
    this.selectedSlotId = null;
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
      // Get court type from URL parameter
      const urlParams = new URLSearchParams(window.location.search);
      const courtType = urlParams.get("type");

      // Build API URL with type filter if provided
      let apiUrl = "http://localhost:8000/courts";
      if (courtType) {
        apiUrl += `?type=${encodeURIComponent(courtType)}`;
      }

      const response = await fetch(apiUrl).then((res) => res.json());
      const courts = response.courts || response;

      // Display courts with type header if filtered
      this.displayCourts(courts, courtType);
    } catch (error) {
      console.error("Error loading courts:", error);
      app.showMessage("Error loading courts", "error");
    }
  }

  displayCourts(courts, courtType = null) {
    const courtsList = document.getElementById("courtsList");
    if (!courtsList) return;

    // Add type header if filtering by type
    let headerHtml = "";
    if (courtType) {
      const typeEmojis = {
        Futsal: "🥅",
        Badminton: "🏸",
        Padel: "🎾",
        Cricket: "🏏",
        Tennis: "🎾",
      };
      const emoji = typeEmojis[courtType] || "🏟️";
      headerHtml = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 1rem; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 8px; color: white; margin-bottom: 1rem;">
          <h3 style="margin: 0; font-size: 1.5rem;">${emoji} ${courtType} Courts</h3>
          <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Select a ${courtType.toLowerCase()} court to continue booking</p>
        </div>
      `;
    }

    if (!courts || courts.length === 0) {
      courtsList.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
          <h3>No ${courtType || ""} Courts Available</h3>
          <p>Please check back later or <a href="../pages/index.html">browse other sports</a>.</p>
        </div>
      `;
      return;
    }

    courtsList.innerHTML =
      headerHtml +
      courts
        .map(
          (court) => `
            <div class="card court-card" data-court-id="${court.id}">
                <h3>${court.name}</h3>
                <p><strong>Type:</strong> ${court.type}</p>
                <p><strong>Location:</strong> ${court.location}</p>
                <p><strong>Price:</strong> $${court.price_per_hour}/hour</p>
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
      const response = await fetch(
        `http://localhost:8000/courts/${this.selectedCourt}/slots?date=${this.selectedDate}`
      ).then((res) => res.json());
      const slots = response.slots || response;
      this.displayTimeSlots(slots);
    } catch (error) {
      console.error("Error loading time slots:", error);
      app.showMessage("Error loading available time slots", "error");
    }
  }

  displayTimeSlots(slots) {
    const slotsContainer = document.getElementById("timeSlots");
    if (!slotsContainer) return;

    if (!slots || slots.length === 0) {
      slotsContainer.innerHTML = `
        <h3>Available Time Slots</h3>
        <p>No slots available for this date. Please select another date.</p>
      `;
      return;
    }

    slotsContainer.innerHTML = `
            <h3>Available Time Slots</h3>
            <p style="color: #666; margin-bottom: 1rem;">Select a time slot to continue (all slots shown are available)</p>
            <div class="time-slots-grid">
                ${slots
                  .map((slot) => {
                    const startTime = slot.start_time;
                    const endTime = slot.end_time;
                    // Backend only returns available slots, so all are clickable
                    return `
                    <button class="btn time-slot" 
                            data-time="${startTime}" 
                            data-end-time="${endTime}">
                        ${startTime} - ${endTime}
                    </button>
                `;
                  })
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
    this.selectedSlotId = slotElement.dataset.slotId;
  }

  async handleBookingSubmit(event) {
    event.preventDefault();

    const userToken = localStorage.getItem("userToken");
    if (!userToken) {
      alert("Please login to make a booking");
      window.location.href = "login.html";
      return;
    }

    // Validate selections
    if (!this.selectedCourt) {
      alert("Please select a court first");
      return;
    }

    if (!this.selectedDate) {
      alert("Please select a date");
      return;
    }

    if (!this.selectedTime) {
      alert("Please select a time slot");
      return;
    }

    const formData = new FormData(event.target);

    // Match exact backend API structure
    const bookingData = {
      court_id: parseInt(this.selectedCourt),
      booking_date: this.selectedDate,
      start_time: this.selectedTime,
      duration: parseInt(formData.get("duration")) || 1,
      customer_name: formData.get("customerName"),
      customer_email: formData.get("customerEmail"),
      customer_phone: formData.get("customerPhone"),
      notes: formData.get("notes") || "",
    };

    console.log("Submitting booking:", bookingData);

    try {
      const response = await fetch("http://localhost:8000/bookings", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${userToken}`,
        },
        body: JSON.stringify(bookingData),
      });

      const data = await response.json();
      console.log("Booking response:", data);

      if (response.ok && (data.booking || data.success)) {
        alert("Booking confirmed! Redirecting to your bookings...");
        window.location.href = "my-bookings.html";
      } else {
        alert(data.message || "Booking failed. Please try again.");
      }
    } catch (error) {
      console.error("Booking error:", error);
      alert("Error processing booking. Please try again.");
    }
  }

  validateBooking(data) {
    const errors = [];

    if (!data.court_id) errors.push("Please select a court");
    if (!data.booking_date) errors.push("Please select a date");
    if (!data.start_time) errors.push("Please select a time slot");
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
    this.selectedSlotId = null;
  }
}

// Initialize booking manager when on booking page
if (window.location.pathname.includes("booking.html")) {
  const bookingManager = new BookingManager();
}
