document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        // Create form data and send AJAX request
        const formData = new FormData(form);
        fetch('index.php?route=inscription-newsletter', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const messageElement = document.querySelector('#message');
            
            // Set message color and reset form if successful
            messageElement.style.color = data.success ? '#90E39A' : '#FF715B';
            if (data.success) form.reset();

            // Display response message
            messageElement.textContent = data.message;
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});