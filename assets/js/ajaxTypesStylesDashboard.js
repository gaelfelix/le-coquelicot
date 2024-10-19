document.addEventListener("DOMContentLoaded", function() {
    const newTypeInput = document.getElementById('new-type-input');
    const addTypeBtn = document.getElementById('add-type-btn');
    const typesList = document.getElementById('types-list');
    const newStyleInput = document.getElementById('new-style-input');
    const addStyleBtn = document.getElementById('add-style-btn');
    const stylesList = document.getElementById('styles-list');

    // Event listeners
    addTypeBtn.addEventListener('click', handleAddType);
    typesList.addEventListener('click', handleTypeAction);
    addStyleBtn.addEventListener('click', handleAddStyle);
    stylesList.addEventListener('click', handleStyleAction);

    // Handle type actions (delete)
    function handleTypeAction(event) {
        if (event.target.id.startsWith('delete-type-')) {
            const typeId = event.target.closest('li').dataset.id;
            deleteItem('type', typeId);
        }
    }

    // Handle style actions (delete)
    function handleStyleAction(event) {
        if (event.target.id.startsWith('delete-style-')) {
            const styleId = event.target.closest('li').dataset.id;
            deleteItem('style', styleId);
        }
    }

    // Handle adding a new type
    function handleAddType() {
        const typeName = newTypeInput.value.trim();
        if (typeName) {
            addItem('type', typeName);
        } else {
            showMessage("Le nom du type ne peut pas être vide.", 'error');
        }
    }

    // Handle adding a new style
    function handleAddStyle() {
        const styleName = newStyleInput.value.trim();
        if (styleName) {
            addItem('style', styleName);
        } else {
            showMessage("Le nom du style ne peut pas être vide.", 'error');
        }
    }

    // Add a new item (type or style)
    function addItem(itemType, name) {
        const data = JSON.stringify({ name: name });
        
        fetch(`index.php?route=admin-add-${itemType}`, {
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
                showMessage(data.message || `${itemType === 'type' ? 'Type' : 'Style'} ajouté avec succès.`, 'success');
            } else {
                showMessage(data.message || `Erreur lors de l'ajout du ${itemType === 'type' ? 'type' : 'style'}`, 'error');
            }
        })
        .catch(error => {
            showMessage(`Une erreur s'est produite lors de l'ajout du ${itemType === 'type' ? 'type' : 'style'} : ${error.message}`, 'error');
        });
    }

    // Delete an item (type or style)
    function deleteItem(itemType, id) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer ce ${itemType === 'type' ? 'type' : 'style'} ?`)) {
            fetch(`index.php?route=admin-delete-${itemType}&id=${encodeURIComponent(id)}`, {
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
                showMessage(`Une erreur s'est produite lors de la suppression du ${itemType === 'type' ? 'type' : 'style'} : ${error.message}`, 'error');
            });
        }
    }

    // Create a new list item
    function createListItem(id, name, itemType) {
        const li = document.createElement('li');
        li.dataset.id = id;
        li.innerHTML = `
            ${name}
            <button id="delete-${itemType}-${id}">Supprimer</button>
        `;
        return li;
    }

    // Clear input field
    function clearInput(itemType) {
        if (itemType === 'type') {
            newTypeInput.value = '';
        } else {
            newStyleInput.value = '';
        }
    }

    // Remove list item
    function removeListItem(itemType, id) {
        const list = itemType === 'type' ? typesList : stylesList;
        const item = list.querySelector(`li[data-id="${id}"]`);
        if (item) {
            item.remove();
        }
    }

    // Show message (success or error)
    function showMessage(message, type) {
        alert((type === 'success' ? 'Succès' : 'Erreur') + ' : ' + message);
    }
});