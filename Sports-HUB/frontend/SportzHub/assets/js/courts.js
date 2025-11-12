// SportzHub Courts JavaScript
// Handles court listing, filtering, and display

class CourtsManager {
  constructor() {
    this.courts = [];
    this.filteredCourts = [];
    this.currentFilter = "all";
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadCourts();
  }

  bindEvents() {
    document.addEventListener("DOMContentLoaded", () => {
      // Filter buttons
      const filterButtons = document.querySelectorAll(".filter-btn");
      filterButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => this.handleFilter(e));
      });

      // Search functionality
      const searchInput = document.getElementById("courtSearch");
      if (searchInput) {
        searchInput.addEventListener("input", (e) => this.handleSearch(e));
      }

      // Sort functionality
      const sortSelect = document.getElementById("sortSelect");
      if (sortSelect) {
        sortSelect.addEventListener("change", (e) => this.handleSort(e));
      }
    });
  }

  async loadCourts() {
    try {
      this.courts = await app.apiCall("courts");
      this.filteredCourts = [...this.courts];
      this.displayCourts();
    } catch (error) {
      console.error("Error loading courts:", error);
      app.showMessage("Error loading courts", "error");
    }
  }

  displayCourts() {
    const courtsContainer = document.getElementById("courtsContainer");
    if (!courtsContainer) return;

    if (this.filteredCourts.length === 0) {
      courtsContainer.innerHTML = `
                <div class="no-results">
                    <h3>No courts found</h3>
                    <p>Try adjusting your filters or search terms.</p>
                </div>
            `;
      return;
    }

    courtsContainer.innerHTML = this.filteredCourts
      .map(
        (court) => `
            <div class="card court-card" data-type="${court.type}">
                <div class="court-image">
                    <img src="../assets/images/${court.type.toLowerCase()}.jpg" 
                         alt="${court.name}" 
                         onerror="this.src='../assets/images/default-court.jpg'">
                    <div class="court-status ${court.status}">
                        ${
                          court.status === "available"
                            ? "Available"
                            : "Maintenance"
                        }
                    </div>
                </div>
                <div class="court-info">
                    <h3>${court.name}</h3>
                    <p class="court-type">${court.type}</p>
                    <p class="court-location"><i class="icon-location"></i> ${
                      court.location
                    }</p>
                    <p class="court-features">${
                      court.features
                        ? court.features.join(", ")
                        : "Standard features"
                    }</p>
                    <div class="court-pricing">
                        <span class="price">$${court.price}</span>
                        <span class="price-unit">/hour</span>
                    </div>
                    <div class="court-actions">
                        <button class="btn btn-primary" onclick="window.location.href='booking.html?court=${
                          court.id
                        }'">
                            Book Now
                        </button>
                        <button class="btn btn-secondary" onclick="courtsManager.viewCourtDetails(${
                          court.id
                        })">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `
      )
      .join("");

    // Update results count
    this.updateResultsCount();
  }

  handleFilter(event) {
    const filterType = event.target.dataset.filter;
    this.currentFilter = filterType;

    // Update active filter button
    document
      .querySelectorAll(".filter-btn")
      .forEach((btn) => btn.classList.remove("active"));
    event.target.classList.add("active");

    // Apply filter
    if (filterType === "all") {
      this.filteredCourts = [...this.courts];
    } else {
      this.filteredCourts = this.courts.filter(
        (court) => court.type.toLowerCase() === filterType.toLowerCase()
      );
    }

    this.displayCourts();
  }

  handleSearch(event) {
    const searchTerm = event.target.value.toLowerCase();

    this.filteredCourts = this.courts.filter((court) => {
      const matchesSearch =
        court.name.toLowerCase().includes(searchTerm) ||
        court.location.toLowerCase().includes(searchTerm) ||
        court.type.toLowerCase().includes(searchTerm);

      const matchesFilter =
        this.currentFilter === "all" ||
        court.type.toLowerCase() === this.currentFilter.toLowerCase();

      return matchesSearch && matchesFilter;
    });

    this.displayCourts();
  }

  handleSort(event) {
    const sortBy = event.target.value;

    switch (sortBy) {
      case "name":
        this.filteredCourts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case "price-low":
        this.filteredCourts.sort((a, b) => a.price - b.price);
        break;
      case "price-high":
        this.filteredCourts.sort((a, b) => b.price - a.price);
        break;
      case "type":
        this.filteredCourts.sort((a, b) => a.type.localeCompare(b.type));
        break;
      default:
        // No sorting
        break;
    }

    this.displayCourts();
  }

  updateResultsCount() {
    const countElement = document.getElementById("resultsCount");
    if (countElement) {
      const total = this.courts.length;
      const showing = this.filteredCourts.length;
      countElement.textContent = `Showing ${showing} of ${total} courts`;
    }
  }

  async viewCourtDetails(courtId) {
    try {
      const court = await app.apiCall(`courts/${courtId}`);
      this.showCourtModal(court);
    } catch (error) {
      console.error("Error loading court details:", error);
      app.showMessage("Error loading court details", "error");
    }
  }

  showCourtModal(court) {
    const modal = document.createElement("div");
    modal.className = "modal";
    modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>${court.name}</h2>
                    <span class="modal-close">&times;</span>
                </div>
                <div class="modal-body">
                    <img src="../assets/images/${court.type.toLowerCase()}.jpg" alt="${
      court.name
    }" class="court-detail-image">
                    <div class="court-details">
                        <p><strong>Type:</strong> ${court.type}</p>
                        <p><strong>Location:</strong> ${court.location}</p>
                        <p><strong>Price:</strong> $${court.price}/hour</p>
                        <p><strong>Status:</strong> ${court.status}</p>
                        ${
                          court.description
                            ? `<p><strong>Description:</strong> ${court.description}</p>`
                            : ""
                        }
                        ${
                          court.features
                            ? `<p><strong>Features:</strong> ${court.features.join(
                                ", "
                              )}</p>`
                            : ""
                        }
                        ${
                          court.rules
                            ? `<p><strong>Rules:</strong> ${court.rules}</p>`
                            : ""
                        }
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="window.location.href='booking.html?court=${
                      court.id
                    }'">
                        Book This Court
                    </button>
                    <button class="btn btn-secondary modal-close-btn">Close</button>
                </div>
            </div>
        `;

    document.body.appendChild(modal);

    // Close modal events
    modal.querySelector(".modal-close").onclick = () => modal.remove();
    modal.querySelector(".modal-close-btn").onclick = () => modal.remove();
    modal.onclick = (e) => {
      if (e.target === modal) modal.remove();
    };
  }
}

// Initialize courts manager when on courts page
if (window.location.pathname.includes("courts.html")) {
  const courtsManager = new CourtsManager();
}
