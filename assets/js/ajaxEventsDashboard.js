document.addEventListener("DOMContentLoaded", function() {
    // Éléments du DOM
    const errorMessage = document.getElementById('error-message');
    const closeError = document.getElementById('close-error');
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.querySelector('#type');
    const searchButton = document.querySelector('.search-button');
    const eventTableBody = document.querySelector('#eventTable tbody');
    const fileInput = document.querySelector('input[name="media"]');
    const eventForm = document.querySelector('#event-form');

    // Configuration initiale
    searchButton.disabled = true;
    setupFileInput();
    setupErrorHandling();
    setupEventListeners();

    // Fonctions principales
    function setupFileInput() {
        const fileNameDisplay = document.createElement('span');
        fileInput.parentNode.insertBefore(fileNameDisplay, fileInput.nextSibling);

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = ' ' + e.target.files[0].name;
                clearCurrentImage();
            } else {
                fileNameDisplay.textContent = '';
            }
        });
    }

    function setupErrorHandling() {
        if (closeError) {
            closeError.addEventListener('click', clearErrorMessage);
        }
    }

    function setupEventListeners() {
        eventTableBody.addEventListener('click', handleEventTableClick);
        searchInput.addEventListener('input', handleSearch);
        typeSelect.addEventListener('change', handleTypeChange);
    }

    function editEvent(eventId) {
        console.log("Édition de l'événement avec l'ID:", eventId);
        fetch(`index.php?route=get-event-data&id=${encodeURIComponent(eventId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fillEventForm(data.data);
                eventForm.scrollIntoView({ behavior: 'smooth' });
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showErrorMessage('Une erreur est survenue lors de la récupération des données de l\'événement.');
        });
    }

    function fillEventForm(data) {
        // Remplir les champs texte et input
        ['name', 'date', 'debut', 'end', 'ticket_price', 'video_link', 'ticketing_link'].forEach(field => {
            document.querySelector(`input[name="${field}"]`).value = data[field];
        });

        ['main_description', 'description'].forEach(field => {
            document.querySelector(`textarea[name="${field}"]`).value = data[field];
        });

        document.querySelector('#event-id').value = data.id;

        // Remplir les champs select
        ['type_id', 'style1_id', 'style2_id'].forEach(field => {
            document.querySelector(`select[name="${field}"]`).value = data[field];
        });

        // Afficher l'image actuelle
        updateCurrentImage(data);

        // Modifier le formulaire pour la mise à jour
        eventForm.action = `index.php?route=admin-update-event`;
        document.querySelector('#event-form button[type="submit"]').textContent = 'Modifier l\'événement';
        fileInput.removeAttribute('required');

        // Mettre à jour le champ caché media_id
        updateMediaIdField(data.media_id);
    }

    function updateCurrentImage(data) {
        const currentImage = document.getElementById('current-image');
        if (data.media_url) {
            currentImage.innerHTML = `<img src="${data.media_url}" alt="${data.media_alt}" style="max-width: 100px;">`;
            let imageName = data.media_url.split('/').pop().split('.').shift();
            document.querySelector('input[name="image_name"]').value = imageName;
            document.querySelector('input[name="alt-img"]').value = data.media_alt;
        } else {
            currentImage.innerHTML = 'Aucune image';
        }
    }

    function updateMediaIdField(mediaId) {
        let mediaIdInput = document.querySelector('input[name="media_id"]');
        if (!mediaIdInput) {
            mediaIdInput = document.createElement('input');
            mediaIdInput.type = 'hidden';
            mediaIdInput.name = 'media_id';
            eventForm.appendChild(mediaIdInput);
        }
        mediaIdInput.value = mediaId;
    }

    function deleteEvent(eventId) {
        console.log("Tentative de suppression de l'événement ID:", eventId);
        fetch(`index.php?route=admin-delete-event&id=${encodeURIComponent(eventId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            console.log("Données reçues:", data);
            if (data.success) {
                showSuccessMessage(data.message);
                removeEventRow(eventId);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
            showErrorMessage('Une erreur est survenue lors de la suppression de l\'événement.');
        });
    }

    // Fonctions utilitaires
    function clearCurrentImage() {
        const currentImage = document.getElementById('current-image');
        if (currentImage) {
            currentImage.innerHTML = '';
        }
    }

    function clearErrorMessage() {
        errorMessage.style.display = 'none';
        fetch('index.php?route=clear-error-message', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error("Erreur lors de la suppression du message d'erreur");
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
    }

    function handleEventTableClick(event) {
        if (event.target.matches('a.edit-event')) {
            event.preventDefault();
            const eventId = event.target.getAttribute('data-id');
            if (eventId) {
                editEvent(eventId);
            } else {
                console.error("ID de l'événement non trouvé");
            }
        } else if (event.target.matches('a[href*="delete"]')) {
            event.preventDefault();
            const eventId = event.target.href.split('id=')[1];
            if (confirm("Êtes-vous sûr de vouloir supprimer cet évènement ?")) {
                deleteEvent(eventId);
            }
        }
    }

    function handleSearch() {
        const query = searchInput.value;
        updateEvents(`index.php?route=admin-search-event&q=${encodeURIComponent(query)}`);
    }

    function handleTypeChange() {
        searchInput.value = '';
        const selectedType = typeSelect.value;
        updateEvents(`index.php?route=admin-search-event&type=${encodeURIComponent(selectedType)}`);
    }

    function removeEventRow(eventId) {
        const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
        if (row) {
            row.remove();
        } else {
            console.error("Ligne de l'événement non trouvée dans le tableau");
        }
    }

    function updateEvents(url) {
        // Implémentez cette fonction pour mettre à jour la liste des événements
        console.log("Mise à jour des événements avec l'URL:", url);
    }

    function showErrorMessage(message) {
        alert(message);
    }

    function showSuccessMessage(message) {
        alert(message);
    }
});