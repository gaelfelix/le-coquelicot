document.addEventListener("DOMContentLoaded", function() {
    const messageTable = document.getElementById('messageTable');
    const modal = document.getElementById('messageModal');
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
                modalPhone.textContent = message.phone || 'Not provided';
                modalSubject.textContent = message.subject;
                modalMessage.textContent = message.message;
                modal.style.display = "block";

                // Mark message as read
                const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                if (row) row.classList.remove('unread');
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('AJAX request error:', error));
    }

    // Delete a message
    function deleteMessage(messageId) {
        if (confirm("Are you sure you want to delete this message?")) {
            fetch(`index.php?route=admin-delete-message&id=${encodeURIComponent(messageId)}`, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove message row from table
                    const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                    if (row) row.remove();
                }
                alert(data.message);
            })
            .catch(error => console.error('AJAX request error:', error));
        }
    }

    // Handle message table clicks
    messageTable.addEventListener('click', function(event) {
        if (event.target.classList.contains('view-message')) {
            viewMessage(event.target.closest('tr').dataset.id);
        } else if (event.target.classList.contains('delete-message')) {
            deleteMessage(event.target.closest('tr').dataset.id);
        }
    });

    // Close modal
    closeBtn.addEventListener('click', () => modal.style.display = "none");
    window.addEventListener('click', (event) => {
        if (event.target == modal) modal.style.display = "none";
    });
});