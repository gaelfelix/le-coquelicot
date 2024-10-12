document.addEventListener("DOMContentLoaded", function() {
    // Éléments du DOM
    const errorMessage = document.getElementById('error-message');
    const closeError = document.getElementById('close-error');
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.querySelector('#type');
    const searchButton = document.querySelector('.search-button');
    const actualityTableBody = document.querySelector('#actualityTable tbody');
    const fileInput = document.querySelector('input[name="media"]');
    const actualityForm = document.querySelector('#actuality-form');

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
        actualityTableBody.addEventListener('click', handleActualityTableClick);
        searchInput.addEventListener('input', handleSearch);
        if (typeSelect) {
            typeSelect.addEventListener('change', handleTypeChange);
        }
    }

    function editActuality(actualityId) {
        console.log("Édition de l'actualité avec l'ID:", actualityId);
        fetch(`index.php?route=get-actuality-data&id=${encodeURIComponent(actualityId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fillActualityForm(data.data);
                actualityForm.scrollIntoView({ behavior: 'smooth' });
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showErrorMessage('Une erreur est survenue lors de la récupération des données de l\'actualité.');
        });
    }

    function fillActualityForm(data) {
        // Remplir les champs texte et input
        ['title', 'date'].forEach(field => {
            document.querySelector(`input[name="${field}"]`).value = data[field];
        });

        document.querySelector('textarea[name="content"]').value = data.content;
        document.getElementById('actuality-id').value = data.id;

        // Afficher l'image actuelle
        updateCurrentImage(data);

        // Modifier le formulaire pour la mise à jour
        actualityForm.action = `index.php?route=admin-update-actuality`;
        document.querySelector('#actuality-form button[type="submit"]').textContent = 'Modifier l\'actualité';
        fileInput.removeAttribute('required');

        // Mettre à jour le champ caché media_id
        updateMediaIdField(data.media_id);
        console.log("Mise à jour du champ media_id avec la valeur:", data.media_id);
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
            actualityForm.appendChild(mediaIdInput);
        }
        mediaIdInput.value = mediaId;
    }

    function deleteActuality(actualityId) {
        console.log("Tentative de suppression de l'actualité ID:", actualityId);
        fetch(`index.php?route=admin-delete-actuality&id=${encodeURIComponent(actualityId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            console.log("Données reçues:", data);
            if (data.success) {
                showSuccessMessage(data.message);
                removeActualityRow(actualityId);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
            showErrorMessage('Une erreur est survenue lors de la suppression de l\'actualité.');
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

    function handleActualityTableClick(event) {
        if (event.target.matches('a.edit-actuality')) {
            event.preventDefault();
            const actualityId = event.target.getAttribute('data-id');
            if (actualityId) {
                editActuality(actualityId);
            } else {
                console.error("ID de l'actualité non trouvé");
            }
        } else if (event.target.matches('a[href*="delete"]')) {
            event.preventDefault();
            const actualityId = event.target.href.split('id=')[1];
            if (confirm("Êtes-vous sûr de vouloir supprimer cette actualité ?")) {
                deleteActuality(actualityId);
            }
        }
    }

    function handleSearch() {
        const query = searchInput.value;
        updateActualities(`index.php?route=admin-search-actuality&q=${encodeURIComponent(query)}`);
    }

    function handleTypeChange() {
        searchInput.value = '';
        const selectedType = typeSelect.value;
        updateActualities(`index.php?route=admin-search-actuality&type=${encodeURIComponent(selectedType)}`);
    }

    function removeActualityRow(actualityId) {
        const row = document.querySelector(`tr[data-actuality-id="${actualityId}"]`);
        if (row) {
            row.remove();
        } else {
            console.error("Ligne de l'actualité non trouvée dans le tableau");
        }
    }

    function updateActualities(url) {
        actualityTableBody.innerHTML = '<tr><td colspan="3" class="loading">Chargement des actualités...</td></tr>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            updateActualitiesTable(data);
        })
        .catch(error => {
            console.error('Erreur lors de la recherche:', error);
            showErrorMessage('Une erreur est survenue lors de la recherche des actualités.');
        });
    }

    function updateActualitiesTable(actualities) {
        actualityTableBody.innerHTML = '';
        if (actualities.length === 0) {
            actualityTableBody.innerHTML = '<tr><td colspan="3">Aucune actualité trouvée.</td></tr>';
            return;
        }
        actualities.forEach(actuality => {
            const row = `
                <tr data-actuality-id="${actuality.id}">
                    <td>${actuality.date}</td>
                    <td>${actuality.title}</td>
                    <td>
                        <a href="index.php?route=actualite&id=${actuality.id}">Voir</a>
                        <a href="#" data-id="${actuality.id}" class="edit-actuality">Modifier</a>
                        <a href="index.php?route=admin-delete-actuality&id=${actuality.id}">Supprimer</a>
                    </td>
                </tr>
            `;
            actualityTableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    function showErrorMessage(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        } else {
            alert(message);
        }
    }

    function showSuccessMessage(message) {
        alert(message);
    }
});