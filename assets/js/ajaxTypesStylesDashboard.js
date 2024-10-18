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
            showMessage("Type name cannot be empty.", 'error');
        }
    }

    // Handle adding a new style
    function handleAddStyle() {
        const styleName = newStyleInput.value.trim();
        if (styleName) {
            addItem('style', styleName);
        } else {
            showMessage("Style name cannot be empty.", 'error');
        }
    }

    // Add a new item (type or style)
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
                showMessage(data.message || `${itemType.charAt(0).toUpperCase() + itemType.slice(1)} added successfully.`, 'success');
            } else {
                showMessage(data.message || `Error adding ${itemType}`, 'error');
            }
        })
        .catch(error => {
            showMessage(`An error occurred while adding the ${itemType}: ${error.message}`, 'error');
        });
    }

    // Delete an item (type or style)
    function deleteItem(itemType, id) {
        if (confirm(`Are you sure you want to delete this ${itemType}?`)) {
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
                showMessage(`An error occurred while deleting the ${itemType}: ${error.message}`, 'error');
            });
        }
    }

    // Create a new list item
    function createListItem(id, name, itemType) {
        const li = document.createElement('li');
        li.dataset.id = id;
        li.innerHTML = `
            ${name}
            <button id="delete-${itemType}-${id}">Delete</button>
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
        alert(type.charAt(0).toUpperCase() + type.slice(1) + ': ' + message);
    }
});