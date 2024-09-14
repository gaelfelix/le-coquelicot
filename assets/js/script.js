document.addEventListener('DOMContentLoaded', function(){
  
  // Get the burger toggler element and the nav links container
  const burgerToggler = document.getElementById('menu-toggle');
  const navLinksContainer = document.getElementById('nav-links-container');
  const overlayMobile = document.querySelector('#overlay-mobile');
  const main = document.querySelector('main');
  const footer = document.querySelector('footer');

  // Define the toggleNav function, which toggles the 'open' class
  const toggleNav = e => {
    burgerToggler.classList.toggle('open');
    navLinksContainer.classList.toggle('open');
    overlayMobile.classList.toggle('overlay-visible');
  }

  // Define the closeNav function, which remove the 'open' class
  const closeNav = e => {
    burgerToggler.classList.remove('open');
    navLinksContainer.classList.remove('open');
    overlayMobile.classList.remove('overlay-visible');
  }

  // Add a click event listener to the burger toggler element
  // that calls the toggleNav function when clicked
  // Add a click event listener to the main and footer element
  // that calls the closeNav function when clicked
  burgerToggler.addEventListener('click', toggleNav);
  main.addEventListener('click', closeNav);
  footer.addEventListener('click', closeNav);

  // Add a scroll event listener to the window element
  // that calls the closeNav function when scrolled
  window.addEventListener('scroll', closeNav);

  // This code uses the ResizeObserver API to detect when the screen size changes.
  // It adjusts the transition property of the navLinksContainer element and toggles the 'open' class on the burger toggler element when the screen size is less than 992 pixels.
  // The navLinksContainer element slides in and out smoothly using CSS transitions.
  // When the screen size is greater than or equal to 992 pixels, the transition property is set to 'none' to disable transitions, the 'open' class is removed from the burger toggler element, and the 'open' class is removed from the navLinksContainer element. 
  // This code is commented out because it is not currently being used.

  new ResizeObserver(entries => {
    if(entries[0].contentRect.width >= 992) {
      navLinksContainer.style.transition = 'none';
      burgerToggler.classList.remove('open');
      navLinksContainer.classList.remove('open');
      overlayMobile.classList.remove('overlay-visible');
    }
  }).observe(document.body);

/* INSCRIPTION - Fermer message erreur */
/*     const closeAlert = document.querySelector('.fa-xmark');

    closeAlert.addEventListener('click', function() {

  }); */


/* Carousel des events */
  new Glider(document.querySelector('.glider'), {
    // Mobile-first defaults
    slidesToShow: 1,
    slidesToScroll: 1,
    scrollLock: true,
    dots: '#dots',
    arrows: {
      prev: '.glider-prev',
      next: '.glider-next'
    },
    responsive: [
      {
        // screens greater than >= 850px
        breakpoint: 850,
        settings: {
          // Set to `auto` and provide item width to adjust to viewport
          slidesToShow: 2,
          slidesToScroll: 2,
          itemWidth: 150,
          duration: 0.5
        }
      },{
        // screens greater than >= 1250px
        breakpoint: 1250,
        settings: {
          slidesToShow: 3,
          slidesToScroll: 3,
          itemWidth: 150,
          duration: 0.5
        }
      },{
        // screens greater than >= 1700px
        breakpoint: 1700,
        settings: {
          slidesToShow: 4,
          slidesToScroll: 4,
          itemWidth: 150,
          duration: 0.5
        }
      }
    ]
  });

});