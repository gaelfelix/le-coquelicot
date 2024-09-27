document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?route=inscription-newsletter', true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const messageElement = document.querySelector('#message');
                
                if (response.success) {
                    messageElement.style.color = '#90E39A';
                } else {
                    messageElement.style.color = '#FF715B';
                }

                messageElement.textContent = response.message;
            }
        };

        xhr.send(formData);
    });
});
