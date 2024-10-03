// Wait for all the content to load before displaying the body
window.onload = function() {
  document.body.classList.add('loaded');
};

// Utility function to add/remove a class on a list of elements
function toggleClassOnElements(elements, className, add) {
  elements.forEach(element => {
    if (add) {
      element.classList.add(className); // Add the class if 'add' is true
    } else {
      element.classList.remove(className); // Remove the class if 'add' is false
    }
  });
}

// Function to apply preferences from local storage
function applyPreferences() {
  const dyslexiaPreference = JSON.parse(localStorage.getItem('dyslexia')) || false;
  const lineSpacingPreference = JSON.parse(localStorage.getItem('lineSpacing')) || false;

  // Select all relevant headings and text excluding those with class "no-toggle-dyslexia" or "no-toggle-line-spacing"
  const headingsAndTextDyslexia = document.querySelectorAll("h3:not(.no-toggle-dyslexia), h4:not(.no-toggle-dyslexia), h5:not(.no-toggle-dyslexia), h6:not(.no-toggle-dyslexia), p:not(.no-toggle-dyslexia)"); 
  const headingsAndTextLineSpacing = document.querySelectorAll("h3:not(.no-toggle-line-spacing), h4:not(.no-toggle-line-spacing), h5:not(.no-toggle-line-spacing), h6:not(.no-toggle-line-spacing), p:not(.no-toggle-line-spacing)"); 

  // Apply dyslexia class if preference is true
  toggleClassOnElements(headingsAndTextDyslexia, "dyslexia", dyslexiaPreference);
  
  // Set toggle state for dyslexia
  const dyslexiaToggle = document.getElementById("toggle-dyslexia");
  if (dyslexiaToggle) {
    dyslexiaToggle.checked = dyslexiaPreference;
    dyslexiaToggle.setAttribute("data-active", dyslexiaPreference);
    document.getElementById("toggle-dyslexia-label").textContent = dyslexiaPreference ? "Activé" : "Désactivé";
  }

  // Apply line spacing class if preference is true
  toggleClassOnElements(headingsAndTextLineSpacing, "line-spacing", lineSpacingPreference);
  
  // Set toggle state for line spacing
  const lineSpacingToggle = document.getElementById("toggle-line-spacing");
  if (lineSpacingToggle) {
    lineSpacingToggle.checked = lineSpacingPreference;
    lineSpacingToggle.setAttribute("data-active", lineSpacingPreference);
    document.getElementById("toggle-line-spacing-label").textContent = lineSpacingPreference ? "Activé" : "Désactivé";
  }
}

// Save preferences to localStorage
function savePreference(preference, value) {
  localStorage.setItem(preference, JSON.stringify(value));
}

document.addEventListener('DOMContentLoaded', function() {
  // MENU BURGER JS
  const burgerToggler = document.getElementById('menu-toggle');
  const navLinksContainer = document.getElementById('nav-links-container');
  const overlayMobile = document.querySelector('#overlay-mobile');
  const main = document.querySelector('main');
  const footer = document.querySelector('footer');

  const toggleNav = () => {
    const isOpen = burgerToggler.classList.toggle('open');
    toggleClassOnElements([navLinksContainer, overlayMobile], 'open', isOpen);
    overlayMobile.classList.toggle('overlay-visible');
  };

  const closeNav = () => {
    toggleClassOnElements([burgerToggler, navLinksContainer, overlayMobile], 'open', false);
    overlayMobile.classList.remove('overlay-visible');
  };

  burgerToggler.addEventListener('click', toggleNav); 
  main.addEventListener('click', closeNav); 
  footer.addEventListener('click', closeNav); 
  overlayMobile.addEventListener('click', closeNav); 
  window.addEventListener('scroll', closeNav); 

  new ResizeObserver(entries => {
    if (entries[0].contentRect.width >= 992) {
      navLinksContainer.style.transition = 'none'; 
      closeNav(); 
    }
  }).observe(document.body); 

  const modal = document.getElementById("customization-modal");
  const accessibilityButtons = document.querySelectorAll(".header-accessibility"); // Query all accessibility buttons
  const closeModalButton = document.getElementById("close-modal");

  // Add click event listener to each accessibility button
  accessibilityButtons.forEach(button => {
    button.addEventListener("click", () => {
      // Close the navigation if it is open
      const isNavOpen = burgerToggler.classList.contains('open');
      if (isNavOpen) {
        closeNav(); // Close the navigation and the overlay
      }
      
      // Show the modal
      modal.style.display = "block"; 
    });
  });

  closeModalButton.addEventListener("click", () => {
    modal.style.display = "none"; 
  });

  window.addEventListener("click", event => {
    if (event.target === modal) {
      modal.style.display = "none"; 
    }
  });

  // Apply preferences from localStorage when page loads
  applyPreferences();

  // Select all relevant headings and text excluding those with class "no-toggle-dyslexia" or "no-toggle-line-spacing"
  const headingsAndTextDyslexia = document.querySelectorAll("h3:not(.no-toggle-dyslexia), h4:not(.no-toggle-dyslexia), h5:not(.no-toggle-dyslexia), h6:not(.no-toggle-dyslexia), p:not(.no-toggle-dyslexia)"); 
  const headingsAndTextLineSpacing = document.querySelectorAll("h3:not(.no-toggle-line-spacing), h4:not(.no-toggle-line-spacing), h5:not(.no-toggle-line-spacing), h6:not(.no-toggle-line-spacing), p:not(.no-toggle-line-spacing)"); 

  // Toggle dyslexia-friendly font
  const dyslexiaToggle = document.getElementById("toggle-dyslexia");
  dyslexiaToggle.addEventListener("change", () => {
    const isActive = dyslexiaToggle.checked;
    
    toggleClassOnElements(headingsAndTextDyslexia, "dyslexia", isActive);
    dyslexiaToggle.setAttribute("data-active", isActive);
    document.getElementById("toggle-dyslexia-label").textContent = isActive ? "Activé" : "Désactivé"; 
    
    // Save to localStorage
    savePreference('dyslexia', isActive);
  });

  // Toggle line spacing
  const lineSpacingToggle = document.getElementById("toggle-line-spacing");
  lineSpacingToggle.addEventListener("change", () => {
    const isActive = lineSpacingToggle.checked;
    
    toggleClassOnElements(headingsAndTextLineSpacing, "line-spacing", isActive);
    lineSpacingToggle.setAttribute("data-active", isActive);
    document.getElementById("toggle-line-spacing-label").textContent = isActive ? "Activé" : "Désactivé"; 
    
    // Save to localStorage
    savePreference('lineSpacing', isActive);
  });

});
