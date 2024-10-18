document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector('input[name="q"]');
    const roleSelect = document.querySelector('#role');
    const searchButton = document.querySelector('.search-button');
    const userTableBody = document.querySelector('#userTable tbody');
    searchButton.disabled = true;

    // Update user list based on search or filter
    function updateUsers(url) {
        userTableBody.innerHTML = '<tr><td colspan="8" class="loading">Loading users...</td></tr>';

        fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(users => {
            userTableBody.innerHTML = '';

            if (users.length > 0) {
                users.forEach(user => createUserRow(user));
            } else {
                userTableBody.innerHTML = '<tr><td colspan="8" class="search-error">No users match your search.</td></tr>';
            }
        })
        .catch(error => {
            console.error('AJAX request error:', error);
            userTableBody.innerHTML = '<tr><td colspan="8" class="search-error">Error loading users. Please try again.</td></tr>';
        });
    }

    // Create a row for a user
    function createUserRow(user) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.firstname}</td>
            <td>${user.lastname}</td>
            <td>${user.role}</td>
            <td>${user.structure}</td>
            <td>${user.specialization || ''}</td>
            <td>${user.email}</td>
            <td>
                <a href="index.php?route=admin-delete-user&id=${user.id}">Delete</a>
            </td>
        `;
        userTableBody.appendChild(row);
    }

    // Handle search input
    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        updateUsers(`index.php?route=admin-search-user&q=${encodeURIComponent(query)}`);
    });

    // Handle role selection change
    roleSelect.addEventListener('change', function() {
        searchInput.value = '';
        const selectedRole = roleSelect.value;
        updateUsers(`index.php?route=admin-search-user&role=${encodeURIComponent(selectedRole)}`);
    });

    // Delete a user
    function deleteUser(userId) {
        fetch(`index.php?route=admin-delete-user&id=${encodeURIComponent(userId)}`, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // Refresh user list
                const url = roleSelect.value === 'all'
                    ? `index.php?route=admin-search-user&q=${encodeURIComponent(searchInput.value)}`
                    : `index.php?route=admin-search-user&role=${encodeURIComponent(roleSelect.value)}`;
                updateUsers(url);
            }
        })
        .catch(error => console.error('AJAX request error:', error));
    }

    // Handle delete user clicks
    userTableBody.addEventListener('click', function(event) {
        if (event.target.matches('a[href*="delete"]')) {
            event.preventDefault();
            const userId = event.target.href.split('id=')[1];
            if (confirm("Are you sure you want to delete this user?")) {
                deleteUser(userId);
            }
        }
    });
});