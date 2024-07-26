document.addEventListener('DOMContentLoaded', function(){
    
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

    const buttonsWrapper = document.querySelector(".map");
    const slides = document.querySelector(".home-events-container");

    buttonsWrapper.addEventListener("click", e => {
        if (e.target.nodeName === "BUTTON") {
            Array.from(buttonsWrapper.children).forEach(item =>
            item.classList.remove("active")
            );
            if (e.target.classList.contains("first")) {
            slides.style.transform = "translateX(-10%)";
            e.target.classList.add("active");
            } else if (e.target.classList.contains("second")) {
                slides.style.transform = "translateX(-33.33333333333333%)";
                /* slides.style.transform = "translateX(-22.2222222222%)"; */
            e.target.classList.add("active");
            } else if (e.target.classList.contains('third')){
                /* slides.style.transform = "translateX(-33.33333333333%)"; */
                slides.style.transform = 'translatex(-66.6666666667%)';
            e.target.classList.add('active');
            }
        }
    });
});