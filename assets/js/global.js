// Add 'loaded' class to body when content is fully loaded
window.onload = () => document.body.classList.add('loaded');

// Toggle class on multiple elements
function toggleClassOnElements(elements, className, add) {
  elements.forEach(element => element.classList[add ? 'add' : 'remove'](className));
}

// Apply accessibility preferences from local storage
function applyPreferences() {
  const dyslexiaPreference = JSON.parse(localStorage.getItem('dyslexia')) || false;
  const lineSpacingPreference = JSON.parse(localStorage.getItem('lineSpacing')) || false;

  // Select elements for dyslexia and line spacing
  const dyslexiaElements = document.querySelectorAll("h3:not(.no-toggle-dyslexia), h4:not(.no-toggle-dyslexia), h5:not(.no-toggle-dyslexia), h6:not(.no-toggle-dyslexia), p:not(.no-toggle-dyslexia)");
  const lineSpacingElements = document.querySelectorAll("h3:not(.no-toggle-line-spacing), h4:not(.no-toggle-line-spacing), h5:not(.no-toggle-line-spacing), h6:not(.no-toggle-line-spacing), p:not(.no-toggle-line-spacing)");

  // Apply preferences
  toggleClassOnElements(dyslexiaElements, "dyslexia", dyslexiaPreference);
  toggleClassOnElements(lineSpacingElements, "line-spacing", lineSpacingPreference);

  // Update toggle states
  updateToggleState("toggle-dyslexia", dyslexiaPreference);
  updateToggleState("toggle-line-spacing", lineSpacingPreference);
}

// Update toggle state and label
function updateToggleState(toggleId, isActive) {
  const toggle = document.getElementById(toggleId);
  if (toggle) {
    toggle.checked = isActive;
    toggle.setAttribute("data-active", isActive);
    document.getElementById(`${toggleId}-label`).textContent = isActive ? "Activé" : "Désactivé";
  }
}

// Save preference to localStorage
function savePreference(preference, value) {
  localStorage.setItem(preference, JSON.stringify(value));
}

document.addEventListener('DOMContentLoaded', function() {
  // Mobile menu functionality
  const burgerToggler = document.getElementById('menu-toggle');
  const navLinksContainer = document.getElementById('nav-links-container');
  const overlayMobile = document.querySelector('#overlay-mobile');
  const main = document.querySelector('main');
  const footer = document.querySelector('footer');

  // Toggle mobile navigation
  const toggleNav = () => {
    const isOpen = burgerToggler.classList.toggle('open');
    toggleClassOnElements([navLinksContainer, overlayMobile], 'open', isOpen);
    overlayMobile.classList.toggle('overlay-visible');
  };

  // Close mobile navigation
  const closeNav = () => {
    toggleClassOnElements([burgerToggler, navLinksContainer, overlayMobile], 'open', false);
    overlayMobile.classList.remove('overlay-visible');
  };

  // Event listeners for mobile menu
  burgerToggler.addEventListener('click', toggleNav);
  [main, footer, overlayMobile].forEach(element => element.addEventListener('click', closeNav));

  // Close nav on window resize
  new ResizeObserver(entries => {
    if (entries[0].contentRect.width >= 992) {
      navLinksContainer.style.transition = 'none';
      closeNav();
    }
  }).observe(document.body);

  // Accessibility modal functionality
  const modal = document.getElementById("customization-modal");
  const accessibilityButtons = document.querySelectorAll(".header-accessibility");
  const closeModalButton = document.getElementById("close-modal");

  // Open accessibility modal
  accessibilityButtons.forEach(button => {
    button.addEventListener("click", () => {
      if (burgerToggler.classList.contains('open')) closeNav();
      modal.style.display = "block";
    });
  });

  // Close accessibility modal
  closeModalButton.addEventListener("click", () => modal.style.display = "none");
  window.addEventListener("click", event => {
    if (event.target === modal) modal.style.display = "none";
  });

  // Apply saved preferences
  applyPreferences();

  // Toggle dyslexia-friendly font
  document.getElementById("toggle-dyslexia").addEventListener("change", (e) => {
    const isActive = e.target.checked;
    toggleClassOnElements(document.querySelectorAll("h3:not(.no-toggle-dyslexia), h4:not(.no-toggle-dyslexia), h5:not(.no-toggle-dyslexia), h6:not(.no-toggle-dyslexia), p:not(.no-toggle-dyslexia)"), "dyslexia", isActive);
    updateToggleState("toggle-dyslexia", isActive);
    savePreference('dyslexia', isActive);
  });

  // Toggle line spacing
  document.getElementById("toggle-line-spacing").addEventListener("change", (e) => {
    const isActive = e.target.checked;
    toggleClassOnElements(document.querySelectorAll("h3:not(.no-toggle-line-spacing), h4:not(.no-toggle-line-spacing), h5:not(.no-toggle-line-spacing), h6:not(.no-toggle-line-spacing), p:not(.no-toggle-line-spacing)"), "line-spacing", isActive);
    updateToggleState("toggle-line-spacing", isActive);
    savePreference('lineSpacing', isActive);
  });
});