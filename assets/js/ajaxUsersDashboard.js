document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector('input[name="q"]');
    const roleSelect = document.querySelector('#role');
    const searchButton = document.querySelector('.search-button');
    const userTableBody = document.querySelector('#userTable tbody');
    searchButton.disabled = true;

    // Fonction pour échapper les caractères spéciaux
    function htmlspecialchars(str) {
        if (typeof str !== 'string') {
            return str;
        }
        return str.replace(/&/g, '&amp;')
                  .replace(/</g, '&lt;')
                  .replace(/>/g, '&gt;')
                  .replace(/"/g, '&quot;')
                  .replace(/'/g, '&#39;');
    }

    // Fonction pour mettre à jour les utilisateurs affichés
    function updateUsers(url) {
        userTableBody.innerHTML = '<tr><td colspan="8" class="loading">Chargement des utilisateurs...</td></tr>'; // Message de chargement

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'  // Important pour la détection Ajax côté serveur
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau : ' + response.statusText);
            }
            return response.json();
        })
        .then(users => {
            userTableBody.innerHTML = ''; // Vider le tableau avant d'ajouter les résultats

            if (users.length > 0) {
                users.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${htmlspecialchars(user.id)}</td>
                        <td>${htmlspecialchars(user.firstname)}</td>
                        <td>${htmlspecialchars(user.lastname)}</td>
                        <td>${htmlspecialchars(user.role)}</td>
                        <td>${htmlspecialchars(user.structure)}</td>
                        <td>${user.specialization ? htmlspecialchars(user.specialization) : ''}</td>
                        <td>${htmlspecialchars(user.email)}</td>
                        <td>
                            <a href="index.php?route=admin-delete-user&id=${htmlspecialchars(user.id)}">Supprimer</a>
                        </td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                userTableBody.innerHTML = '<tr><td colspan="8" class="search-error">Aucun utilisateur ne correspond à votre recherche.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
            userTableBody.innerHTML = '<tr><td colspan="8" class="search-error">Erreur de chargement des utilisateurs. Veuillez réessayer.</td></tr>';
        });
    }

    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const url = `index.php?route=admin-search-user&q=${encodeURIComponent(query)}`;
        updateUsers(url);
    });


    roleSelect.addEventListener('change', function() {
        searchInput.value = '';
        const selectedRole = roleSelect.value;
        const url = `index.php?route=admin-search-user&role=${encodeURIComponent(selectedRole)}`;
        updateUsers(url);
    });


    function deleteUser(userId) {
        const url = `index.php?route=admin-delete-user&id=${encodeURIComponent(userId)}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'  // Important pour la détection Ajax côté serveur
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau : ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message); // Affichez un message de succès
                // Appel à la fonction updateUsers avec la dernière requête utilisée
                const currentQuery = searchInput.value;
                const currentRole = roleSelect.value;
                let url;
    
                if (currentRole === 'all') {
                    url = `index.php?route=admin-search-user&q=${encodeURIComponent(currentQuery)}`;
                } else {
                    url = `index.php?route=admin-search-user&role=${encodeURIComponent(currentRole)}`;
                }
    
                updateUsers(url); // Recharger les utilisateurs
            } else {
                alert(data.message); // Affichez un message d'erreur
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
        });
    }

    // Ajoutez un écouteur d'événements sur les liens de suppression
    userTableBody.addEventListener('click', function(event) {
        if (event.target.matches('a[href*="delete"]')) {
            event.preventDefault(); // Empêche le lien de se comporter normalement
            const userId = event.target.href.split('id=')[1]; // Récupère l'ID de l'utilisateur à partir de l'URL
            const confirmation = confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?");
            if (confirmation) {
                deleteUser(userId);
            }
        }
    });
});
