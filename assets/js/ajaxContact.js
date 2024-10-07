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
                
                // Vérifier le statut de la réponse
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    
                    // Affichage du message de succès ou d'erreur
                    if (response.success) {
                        messageElement.style.color = '#90E39A'; // Couleur pour le succès
                        form.reset(); // Réinitialiser le formulaire
                    } else {
                        messageElement.style.color = '#FF715B'; // Couleur pour l'erreur
                    }
                    
                    messageElement.textContent = response.message; // Affiche le message
                } else {
                    // Gérer les erreurs réseau ou autres erreurs HTTP
                    messageElement.style.color = '#FF715B';
                    messageElement.textContent = "Une erreur est survenue lors de l'envoi du message.";
                    console.error('Erreur lors de la requête :', xhr.statusText);
                }
            }
        };

        // Gestion des erreurs de la requête
        xhr.onerror = function() {
            const messageElement = document.querySelector('#message');
            messageElement.style.color = '#FF715B'; // Couleur pour l'erreur
            messageElement.textContent = "Une erreur réseau est survenue.";
            console.error('Erreur réseau');
        };

        xhr.send(formData);
    });
});
