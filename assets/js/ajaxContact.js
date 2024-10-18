document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?route=envoi-message', true);

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                const messageElement = document.querySelector('#message');
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        messageElement.style.color = '#90E39A';
                        form.reset();
                    } else {
                        messageElement.style.color = '#FF715B';
                    }
                    
                    messageElement.textContent = response.message;
                } catch (e) {
                    console.error('Erreur lors du parsing de la réponse:', xhr.responseText);
                    messageElement.style.color = '#FF715B';
                    messageElement.textContent = "Une erreur inattendue s'est produite.";
                }
            }
        };

        xhr.onerror = function() {
            const messageElement = document.querySelector('#message');
            messageElement.style.color = '#FF715B'; // Couleur pour l'erreur
            messageElement.textContent = "Une erreur réseau est survenue.";
            console.error('Erreur réseau');
        };

        xhr.send(formData);
    });
});