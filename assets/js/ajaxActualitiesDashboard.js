document.addEventListener("DOMContentLoaded", function() {
    const errorMessage = document.getElementById('error-message');
    const closeError = document.getElementById('close-error');
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.querySelector('#type');
    const searchButton = document.querySelector('.search-button');
    const actualityTableBody = document.querySelector('#actualityTable tbody');
    const fileInput = document.querySelector('input[name="media"]');
    const actualityForm = document.querySelector('#actuality-form');

    // Initial setup
    searchButton.disabled = true;
    setupFileInput();
    setupErrorHandling();
    setupEventListeners();

    // Setup file input display
    function setupFileInput() {
        const fileNameDisplay = document.createElement('p');
        fileNameDisplay.classList.add('file-name-display');
        fileInput.parentNode.insertBefore(fileNameDisplay, fileInput.nextSibling);
        fileInput.addEventListener('change', (e) => {
            fileNameDisplay.textContent = e.target.files.length > 0 ? ' ' + e.target.files[0].name : '';
            if (e.target.files.length > 0) clearCurrentImage();
        });
    }

    // Setup error message handling
    function setupErrorHandling() {
        if (closeError) closeError.addEventListener('click', clearErrorMessage);
    }

    // Setup event listeners
    function setupEventListeners() {
        actualityTableBody.addEventListener('click', handleActualityTableClick);
        searchInput.addEventListener('input', handleSearch);
        if (typeSelect) typeSelect.addEventListener('change', handleTypeChange);
    }

    // Edit actuality
    function editActuality(actualityId) {
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
            console.error('Error:', error);
            showErrorMessage('An error occurred while retrieving actuality data.');
        });
    }

    // Fill actuality form with data
    function fillActualityForm(data) {
        ['title', 'date'].forEach(field => {
            document.querySelector(`input[name="${field}"]`).value = data[field];
        });
        document.querySelector('textarea[name="content"]').value = data.content;
        document.getElementById('actuality-id').value = data.id;
        updateCurrentImage(data);
        actualityForm.action = `index.php?route=admin-update-actuality`;
        document.querySelector('#actuality-form button[type="submit"]').textContent = 'Modify Actuality';
        fileInput.removeAttribute('required');
        updateMediaIdField(data.media_id);
    }

    // Update current image display
    function updateCurrentImage(data) {
        const currentImage = document.getElementById('current-image');
        if (data.media_url) {
            currentImage.innerHTML = `<img src="${data.media_url}" alt="${data.media_alt}" style="max-width: 100px;">`;
            let imageName = data.media_url.split('/').pop().split('.').shift();
            document.querySelector('input[name="image_name"]').value = imageName;
            document.querySelector('input[name="alt-img"]').value = data.media_alt;
        } else {
            currentImage.innerHTML = 'No image';
        }
    }

    // Update hidden media_id field
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

    // Delete actuality
    function deleteActuality(actualityId) {
        fetch(`index.php?route=admin-delete-actuality&id=${encodeURIComponent(actualityId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage(data.message);
                removeActualityRow(actualityId);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch(error => {
            console.error('AJAX request error:', error);
            showErrorMessage('An error occurred while deleting the actuality.');
        });
    }

    // Clear current image
    function clearCurrentImage() {
        const currentImage = document.getElementById('current-image');
        if (currentImage) currentImage.innerHTML = '';
    }

    // Clear error message
    function clearErrorMessage() {
        errorMessage.style.display = 'none';
        fetch('index.php?route=clear-error-message', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) console.error("Error while clearing error message");
        })
        .catch(error => console.error('Error:', error));
    }

    // Handle actuality table clicks
    function handleActualityTableClick(event) {
        if (event.target.matches('a.edit-actuality')) {
            event.preventDefault();
            const actualityId = event.target.getAttribute('data-id');
            if (actualityId) {
                editActuality(actualityId);
            } else {
                console.error("Actuality ID not found");
            }
        } else if (event.target.matches('a[href*="delete"]')) {
            event.preventDefault();
            const actualityId = event.target.href.split('id=')[1];
            if (confirm("Êtes vous sûr de vouloir supprimer cette actualité ?")) {
                deleteActuality(actualityId);
            }
        }
    }

    // Handle search input
    function handleSearch() {
        const query = searchInput.value;
        updateActualities(`index.php?route=admin-search-actuality&q=${encodeURIComponent(query)}`);
    }

    // Handle type change
    function handleTypeChange() {
        searchInput.value = '';
        const selectedType = typeSelect.value;
        updateActualities(`index.php?route=admin-search-actuality&type=${encodeURIComponent(selectedType)}`);
    }

    // Remove actuality row from table
    function removeActualityRow(actualityId) {
        const row = document.querySelector(`tr[data-actuality-id="${actualityId}"]`);
        if (row) {
            row.remove();
        } else {
            console.error("Actuality row not found in table");
        }
    }

    // Update actualities list
    function updateActualities(url) {
        actualityTableBody.innerHTML = '<tr><td colspan="3" class="loading">Loading actualities...</td></tr>';
        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => updateActualitiesTable(data))
        .catch(error => {
            console.error('Search error:', error);
            showErrorMessage('An error occurred while searching for actualities.');
        });
    }

    // Update actualities table with data
    function updateActualitiesTable(actualities) {
        actualityTableBody.innerHTML = '';
        if (actualities.length === 0) {
            actualityTableBody.innerHTML = '<tr><td colspan="3">No actualities found.</td></tr>';
            return;
        }
        actualities.forEach(actuality => {
            const row = `
                <tr data-actuality-id="${actuality.id}">
                    <td>${actuality.date}</td>
                    <td>${actuality.title}</td>
                    <td>
                        <a href="index.php?route=actualite&id=${actuality.id}">View</a>
                        <a href="#" data-id="${actuality.id}" class="edit-actuality">Edit</a>
                        <a href="index.php?route=admin-delete-actuality&id=${actuality.id}">Delete</a>
                    </td>
                </tr>
            `;
            actualityTableBody.insertAdjacentHTML('beforeend', row);
        });
    }

    // Show error message
    function showErrorMessage(message) {
        if (errorMessage) {
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        } else {
            alert(message);
        }
    }

    // Show success message
    function showSuccessMessage(message) {
        alert(message);
    }
});