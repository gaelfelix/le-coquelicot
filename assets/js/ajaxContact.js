document.addEventListener("DOMContentLoaded", function() {
    const errorMessage = document.getElementById('error-message');
    const closeError = document.getElementById('close-error');
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.querySelector('#type');
    const searchButton = document.querySelector('.search-button');
    const eventTableBody = document.querySelector('#eventTable tbody');
    const fileInput = document.querySelector('input[name="media"]');
    const eventForm = document.querySelector('#event-form');

    // Initial setup
    searchButton.disabled = true;
    setupFileInput();
    setupErrorHandling();
    setupEventListeners();

    // Main functions
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
            console.error('Error:', error);
            showErrorMessage('Une erreur est survenue lors de la récupération des données de l\'événement.');
        });
    }

    function fillEventForm(data) {
        // Fill text and input fields
        ['name', 'date', 'debut', 'end', 'ticket_price', 'video_link', 'ticketing_link'].forEach(field => {
            document.querySelector(`input[name="${field}"]`).value = data[field];
        });

        ['main_description', 'description'].forEach(field => {
            document.querySelector(`textarea[name="${field}"]`).value = data[field];
        });

        document.querySelector('#event-id').value = data.id;

        // Fill select fields
        ['type_id', 'style1_id', 'style2_id'].forEach(field => {
            document.querySelector(`select[name="${field}"]`).value = data[field];
        });

        // Display current image
        updateCurrentImage(data);

        // Modify form for update
        eventForm.action = `index.php?route=admin-update-event`;
        document.querySelector('#event-form button[type="submit"]').textContent = 'Modifier l\'événement';
        fileInput.removeAttribute('required');

        // Update hidden media_id field
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
        fetch(`index.php?route=admin-delete-event&id=${encodeURIComponent(eventId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                removeEventRow(eventId);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Error during Ajax request:', error);
            showErrorMessage('Une erreur est survenue lors de la suppression de l\'événement.');
        });
    }

    // Utility functions
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
                console.error("Error while clearing error message");
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function handleEventTableClick(event) {
        if (event.target.matches('a.edit-event')) {
            event.preventDefault();
            const eventId = event.target.getAttribute('data-id');
            if (eventId) {
                editEvent(eventId);
            } else {
                console.error("Event ID not found");
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
            console.error("Event row not found in the table");
        }
    }

    function updateEvents(url) {
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            eventTableBody.innerHTML = '';
            data.forEach(event => {
                const row = createEventRow(event);
                eventTableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error updating events:', error);
            showErrorMessage('Une erreur est survenue lors de la mise à jour des événements.');
        });
    }

    function createEventRow(event) {
        const row = document.createElement('tr');
        row.setAttribute('data-event-id', event.id);
        row.innerHTML = `
            <td>${event.shortDay} ${event.number} ${event.shortMonth}</td>
            <td>${event.name}</td>
            <td>${event.debut}</td>
            <td>${event.end}</td>
            <td>${event.ticket_price}</td>
            <td>${event.type.name}</td>
            <td>${event.style1.name}</td>
            <td>${event.style2.name}</td>
            <td>
                <a href="index.php?route=evenement&id=${event.id}" aria-label="Voir l'événement ${event.name}">Voir</a>
                <a href="#" data-id="${event.id}" class="edit-event" aria-label="Modifier l'événement ${event.name}">Modifier</a>
                <a href="index.php?route=admin-delete-event&id=${event.id}" aria-label="Supprimer l'événement ${event.name}">Supprimer</a>
            </td>
        `;
        return row;
    }

    function showErrorMessage(message) {
        alert(message);
    }

    function showSuccessMessage(message) {
        alert(message);
    }
});