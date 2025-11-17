// SportzHub Main JavaScript
// Handles general site functionality, navigation, and component loading

class SportzHub {
  constructor() {
    this.init();
  }

  init() {
    this.bindEvents();
    this.loadComponents();
    this.initNavigation();
    this.initAuth();
  }

  bindEvents() {
    document.addEventListener("DOMContentLoaded", () => {
      this.handleMobileMenu();
      this.updateNavigationForAuth();
    });
  }

  // Initialize authentication checks
  initAuth() {
    // Check authentication on protected pages
    const currentPage = window.location.pathname;
    const protectedPages = ["/pages/booking.html", "/pages/courts.html"];
    const adminPages = currentPage.includes("/admin/");

    if (
      protectedPages.some((page) => currentPage.includes(page)) ||
      adminPages
    ) {
      this.checkAuthentication(adminPages);
    }
  }

  // Check if user is authenticated
  checkAuthentication(requireAdmin = false) {
    const userToken = localStorage.getItem("userToken");
    const userRole = localStorage.getItem("userRole");

    if (!userToken) {
      // Not logged in, redirect to login
      window.location.href = "../login.html";
      return false;
    }

    if (requireAdmin && userRole !== "admin") {
      // Not admin, redirect to user dashboard
      alert("Access denied. Admin privileges required.");
      window.location.href = "../index.html";
      return false;
    }

    return true;
  }

  // Update navigation based on authentication status
  updateNavigationForAuth() {
    const userToken = localStorage.getItem("userToken");
    const userName = localStorage.getItem("userName");
    const userRole = localStorage.getItem("userRole");

    setTimeout(() => {
      const navLinks = document.querySelector(".nav-links");

      if (navLinks && userToken) {
        // User is logged in, update navigation
        const loginLink = navLinks.querySelector('a[href*="login"]');
        const registerLink = navLinks.querySelector('a[href*="register"]');

        if (loginLink) {
          loginLink.textContent = `Welcome, ${userName || "User"}`;
          loginLink.href = "#";
          loginLink.style.cursor = "default";
        }

        if (registerLink) {
          registerLink.textContent = "Logout";
          registerLink.href = "#";
          registerLink.onclick = (e) => {
            e.preventDefault();
            this.logout();
          };
        }

        // Add admin link for admin users
        if (userRole === "admin") {
          const adminLink = document.createElement("li");
          adminLink.innerHTML =
            '<a href="../admin/dashboard.html">Admin Panel</a>';
          navLinks.appendChild(adminLink);
        }
      }
    }, 100);
  }

  // Logout function
  logout() {
    if (confirm("Are you sure you want to logout?")) {
      localStorage.removeItem("userToken");
      localStorage.removeItem("userRole");
      localStorage.removeItem("userName");
      localStorage.removeItem("userId");

      // Redirect to login page
      const currentPath = window.location.pathname;
      const loginPath = currentPath.includes("/admin/")
        ? "../login.html"
        : "login.html";
      window.location.href = loginPath;
    }
  }

  // Load header and footer components
  async loadComponents() {
    try {
      // Only load components if their containers exist
      await this.loadComponent("header", "../components/header.html");
      await this.loadComponent("footer", "../components/footer.html");
    } catch (error) {
      console.error("Error loading components:", error);
    }
  }

  async loadComponent(elementId, componentPath) {
    const element = document.getElementById(elementId);
    if (element) {
      try {
        const response = await fetch(componentPath);
        const html = await response.text();
        element.innerHTML = html;
      } catch (error) {
        console.error(`Error loading ${elementId}:`, error);
      }
    }
    // If element doesn't exist (like on login/register pages), just skip silently
  }

  initNavigation() {
    // Highlight current page in navigation
    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach((link) => {
      const href = link.getAttribute("href");
      if (href && href.includes(currentPage)) {
        link.classList.add("active");
      }
    });
  }

  handleMobileMenu() {
    const menuToggle = document.querySelector(".menu-toggle");
    const navLinks = document.querySelector(".nav-links");

    if (menuToggle && navLinks) {
      menuToggle.addEventListener("click", () => {
        navLinks.classList.toggle("open");
      });
    }
  }

  // Utility functions
  showMessage(message, type = "info") {
    const messageDiv = document.createElement("div");
    messageDiv.className = `alert alert-${type}`;
    messageDiv.textContent = message;

    const container = document.querySelector(".container");
    if (container) {
      container.insertBefore(messageDiv, container.firstChild);

      // Auto remove after 5 seconds
      setTimeout(() => {
        messageDiv.remove();
      }, 5000);
    }
  }

  // Form validation helpers
  validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  validatePhone(phone) {
    const re = /^[\d\s\-\+\(\)]{10,}$/;
    return re.test(phone);
  }
}

// Initialize the main app
const app = new SportzHub();
