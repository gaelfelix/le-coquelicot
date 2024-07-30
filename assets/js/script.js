document.addEventListener('DOMContentLoaded', function(){
    
/* Ouverture du menu burger et overlay opacité sur le reste du content */
    const menuToggle = document.querySelector('.burger-menu');
    const mainNav = document.querySelector('.nav');
    const overlayMenu = document.querySelector('.overlay-menu-mobile');


    menuToggle.addEventListener('click', function() {
        mainNav.classList.toggle('menu-active');
        overlayMenu.classList.toggle('menu-active');
    });

    overlayMenu.addEventListener('click', function() {
        mainNav.classList.remove('menu-active');
        overlayMenu.classList.remove('menu-active');
    });

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
        // screens greater than >= 775px
        breakpoint: 775,
        settings: {
          // Set to `auto` and provide item width to adjust to viewport
          slidesToShow: 2,
          slidesToScroll: 2,
          itemWidth: 150,
          duration: 0.25
        }
      },{
        // screens greater than >= 1024px
        breakpoint: 1024,
        settings: {
          slidesToShow: 2,
          slidesToScroll: 1,
          itemWidth: 150,
          duration: 0.25
        }
      }
    ]
  });

});