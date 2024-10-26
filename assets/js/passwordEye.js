document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".eyes").forEach(eye => {

        // For each eye icon, we add a click event listener
        eye.addEventListener("click", function(e) {
            e.preventDefault(); // Prevents the default click behavior (in case it's a link or button)

            // Find the associated password input by searching within the closest parent container
            const password = this.closest('.password-container').querySelector('.password');
            
            // Find the eye icon itself (the <i> tag inside the clicked element)
            const fas = this.querySelector('.eyesImag');

            // If the input field type is "password", change it to "text" to show the password
            if (password.type === 'password') {
                password.setAttribute('type', 'text'); // Change the input type to "text"
                fas.classList.remove('fa-eye-slash'); // Change the icon from "eye-slash" to "eye"
                fas.classList.add('fa-eye');
            } else {
                // If the input type is "text", change it back to "password" to hide the password
                password.setAttribute('type', 'password'); // Hide the password again
                fas.classList.add('fa-eye-slash'); // Switch back to the "eye-slash" icon
                fas.classList.remove('fa-eye');
            }

        });
    });

    // Function to reset the input type to "password" and restore the "eye-slash" icon
    function closeEyes(password, fas) {
        password.setAttribute('type', 'password'); // Hide the password again
        fas.classList.add('fa-eye-slash'); // Restore the "eye-slash" icon
        fas.classList.remove('fa-eye'); // Remove the "eye" icon
        fas.removeAttribute('style'); // Remove any inline styling (e.g., red color)
    }

});