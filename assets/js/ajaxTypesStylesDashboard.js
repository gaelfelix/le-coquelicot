document.addEventListener("DOMContentLoaded", function() {
    // Éléments du DOM
    const newTypeInput = document.getElementById('new-type-input');
    const addTypeBtn = document.getElementById('add-type-btn');
    const typesList = document.getElementById('types-list');
    const newStyleInput = document.getElementById('new-style-input');
    const addStyleBtn = document.getElementById('add-style-btn');
    const stylesList = document.getElementById('styles-list');

    addTypeBtn.addEventListener('click', handleAddType);
    typesList.addEventListener('click', handleTypeAction);
    addStyleBtn.addEventListener('click', handleAddStyle);
    stylesList.addEventListener('click', handleStyleAction);

    function handleTypeAction(event) {
        if (event.target.id.startsWith('delete-type-')) {
            const typeId = event.target.closest('li').dataset.id;
            deleteItem('type', typeId);
        }
    }

    function handleStyleAction(event) {
        if (event.target.id.startsWith('delete-style-')) {
            const styleId = event.target.closest('li').dataset.id;
            deleteItem('style', styleId);
        }
    }

    function handleAddType() {
        const typeName = newTypeInput.value.trim();
        if (typeName) {
            addItem('type', typeName);
        } else {
            showMessage("Le nom du type ne peut pas être vide.", 'error');
        }
    }

    function handleAddStyle() {
        const styleName = newStyleInput.value.trim();
        if (styleName) {
            addItem('style', styleName);
        } else {
            showMessage("Le nom du style ne peut pas être vide.", 'error');
        }
    }

    function addItem(itemType, name) {
        const data = JSON.stringify({ name: name });
        
        fetch(`index.php?route=add-${itemType}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const list = itemType === 'type' ? typesList : stylesList;
                const newItem = createListItem(data.id, data.name, itemType);
                list.appendChild(newItem);
                clearInput(itemType);
                showMessage(data.message || `${itemType.charAt(0).toUpperCase() + itemType.slice(1)} ajouté avec succès.`, 'success');
            } else {
                showMessage(data.message || `Erreur lors de l'ajout du ${itemType}`, 'error');
            }
        })
        .catch(error => {
            showMessage(`Une erreur est survenue lors de l'ajout du ${itemType}: ${error.message}`, 'error');
        });
    }

    function deleteItem(itemType, id) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer ce ${itemType} ?`)) {
            fetch(`index.php?route=delete-${itemType}&id=${encodeURIComponent(id)}`, {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    removeListItem(itemType, id);
                }
            })
            .catch(error => {
                showMessage(`Une erreur est survenue lors de la suppression du ${itemType}: ${error.message}`, 'error');
            });
        }
    }

    // Fonctions utilitaires
    function createListItem(id, name, itemType) {
        const li = document.createElement('li');
        li.dataset.id = id;
        li.innerHTML = `
            ${name}
            <button id="delete-${itemType}-${id}">Supprimer</button>
        `;
        return li;
    }

    function clearInput(itemType) {
        if (itemType === 'type') {
            newTypeInput.value = '';
        } else {
            newStyleInput.value = '';
        }
    }

    function removeListItem(itemType, id) {
        const list = itemType === 'type' ? typesList : stylesList;
        const item = list.querySelector(`li[data-id="${id}"]`);
        if (item) {
            item.remove();
        }
    }

    function showMessage(message, type) {
        alert(type.charAt(0).toUpperCase() + type.slice(1) + ': ' + message);
    }
});