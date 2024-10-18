document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.getElementById('type');
    const searchButton = document.querySelector('.search-button');
    const container = document.querySelector('.programmation-events-container');

    // Initially disable the search button
    searchButton.disabled = true;

    // Function to update events based on search or filter
    function updateEvents(url) {
        // Show loading message
        container.innerHTML = '<p class="loading">Chargement des événements...</p>';

        // Fetch events from the server
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(events => {
            // Clear the container
            container.innerHTML = '';

            if (events.length > 0) {
                // Display each event
                events.forEach(event => {
                    const eventHtml = `
                        <div class="event-card-container">
                            <article class="event-card" style="background-image: url('${event.media.url}');">
                                <a href="index.php?route=evenement&id=${event.id}">
                                    <div class="card-block-date mono">
                                        <p class="mono">${event.shortDay}</p>
                                        <p class="mono">${event.number}</p>
                                        <p class="mono">${event.shortMonth}</p>                              
                                    </div>                        
                                    <div class="info-event-container">
                                        <h3 class="mono">${event.name}</h3>
                                        <div class="rect-event-container">
                                            <div class="rect-event type">${event.type.name}</div>
                                            <div class="rect-event style">${event.style1.name}</div>
                                            <div class="rect-event style">${event.style2.name}</div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', eventHtml);
                });
            } else {
                // Display message if no events found
                container.innerHTML = '<p class="search-error">Aucun événement ne correspond à votre recherche.</p>';
            }
        })
        .catch(error => {
            console.error('Error during Ajax request:', error);
            container.innerHTML = '<p class="search-error">Erreur de chargement des événements. Veuillez réessayer.</p>';
        });
    }

    // Listen for changes in the search input
    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const url = `index.php?route=search&q=${encodeURIComponent(query)}`;
        updateEvents(url);
    });

    // Listen for changes in the type select
    typeSelect.addEventListener('change', function() {
        searchInput.value = ''; // Clear the search input
        const type = this.value;
        const url = `index.php?route=filterEvents&type=${encodeURIComponent(type)}`;
        updateEvents(url);
    });
});