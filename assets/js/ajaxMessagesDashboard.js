document.addEventListener("DOMContentLoaded", function() {
    const messageTable = document.getElementById('messageTable');
    const modal = document.getElementById('message-modal');
    const closeBtn = modal.querySelector(".close");
    const modalFrom = document.getElementById('modalFrom');
    const modalEmail = document.getElementById('modalEmail');
    const modalPhone = document.getElementById('modalPhone');
    const modalSubject = document.getElementById('modalSubject');
    const modalMessage = document.getElementById('modalMessage');

    // View message details
    function viewMessage(messageId) {
        fetch(`index.php?route=admin-view-message&id=${encodeURIComponent(messageId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal with message data
                const message = data.message;
                modalFrom.textContent = `${message.firstName} ${message.lastName}`;
                modalEmail.textContent = message.email;
                modalPhone.textContent = message.phone || 'Non fourni';
                modalSubject.textContent = message.subject;
                modalMessage.textContent = message.message;
                modal.style.display = "block";

                // Update UI to show message as read
                const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                if (row) {
                    row.classList.remove('unread');
                    const markUnreadLink = row.querySelector('.mark-unread');
                    if (markUnreadLink) {
                        markUnreadLink.classList.remove('hidden');
                    }
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('AJAX request error:', error));
    }

    // Mark message as unread
    function markAsUnread(messageId) {
        fetch(`index.php?route=admin-mark-unread&id=${encodeURIComponent(messageId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                if (row) {
                    row.classList.add('unread');
                    const markUnreadLink = row.querySelector('.mark-unread');
                    if (markUnreadLink) {
                        markUnreadLink.classList.add('hidden');
                    }
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('AJAX request error:', error));
    }

    // Delete message
    function deleteMessage(messageId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
            fetch(`index.php?route=admin-delete-message&id=${encodeURIComponent(messageId)}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                    if (row) row.remove();
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('AJAX request error:', error));
        }
    }

    // Handle message table clicks
    messageTable.addEventListener('click', function(event) {
        event.preventDefault();
        const target = event.target;
        const messageId = target.closest('tr')?.dataset.id;

        if (!messageId) return;

        if (target.matches('a.view-message')) {
            viewMessage(messageId);
        } else if (target.matches('a.mark-unread')) {
            markAsUnread(messageId);
        } else if (target.matches('a.delete-message')) {
            deleteMessage(messageId);
        }
    });

    // Close modal
    closeBtn.addEventListener('click', () => modal.style.display = "none");
    window.addEventListener('click', (event) => {
        if (event.target == modal) modal.style.display = "none";
    });
});