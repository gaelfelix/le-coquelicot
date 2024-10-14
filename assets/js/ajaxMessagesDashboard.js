document.addEventListener("DOMContentLoaded", function() {
    const messageTable = document.getElementById('messageTable');
    const modal = document.getElementById('messageModal');
    const closeBtn = modal.querySelector(".close");
    const modalFrom = document.getElementById('modalFrom');
    const modalEmail = document.getElementById('modalEmail');
    const modalPhone = document.getElementById('modalPhone');
    const modalSubject = document.getElementById('modalSubject');
    const modalMessage = document.getElementById('modalMessage');

    function viewMessage(messageId) {
        fetch(`index.php?route=admin-view-message&id=${encodeURIComponent(messageId)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = data.message;
                modalFrom.textContent = `${message.firstName} ${message.lastName}`;
                modalEmail.textContent = message.email;
                modalPhone.textContent = message.phone || 'Non renseigné';
                modalSubject.textContent = message.subject;
                modalMessage.textContent = message.message;
                modal.style.display = "block";

                const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                if (row) {
                    row.classList.remove('unread');
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
        });
    }

    function deleteMessage(messageId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
            fetch(`index.php?route=admin-delete-message&id=${encodeURIComponent(messageId)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = messageTable.querySelector(`tr[data-id="${messageId}"]`);
                    if (row) {
                        row.remove();
                    }
                    alert(data.message);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erreur lors de la requête Ajax:', error);
            });
        }
    }

    messageTable.addEventListener('click', function(event) {
        const target = event.target;
        if (target.classList.contains('view-message')) {
            const messageId = target.closest('tr').dataset.id;
            viewMessage(messageId);
        } else if (target.classList.contains('delete-message')) {
            const messageId = target.closest('tr').dataset.id;
            deleteMessage(messageId);
        }
    });

    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});