document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.getElementById('type');
    const searchButton = document.querySelector('.search-button');
    searchButton.disabled = true; // Désactive le bouton
    
    // Fonction pour mettre à jour les événements affichés
    function updateEvents(url) {
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'  // Important pour la détection Ajax côté serveur
            }
        })
        .then(response => {
            return response.json();
        })
        .then(events => {
            const container = document.querySelector('.programmation-events-container');
            container.innerHTML = ''; // Vider le container avant d'ajouter les résultats

            if (events.length > 0) {
                events.forEach(event => {
                    const eventHtml = `
                        <div class="event-card-container">
                            <article class="event-card" style="background-image: url('${event.media.url}');" alt="${event.media.alt}">
                                <a href="index.php?route=evenement&id=${event.id}">
                                    <div class="card-block-date mono">
                                        <time datetime="${event.date}">${event.date}</time>
                                    </div>                        
                                    <div class="info-event-container">
                                        <h3 class="mono no-toggle-dyslexia no-toggle-line-spacing">${event.name}</h3>
                                        <div class="rect-event-container">
                                            <div class="rect-event type no-toggle-dyslexia no-toggle-line-spacing">${event.type.name}</div>
                                            <div class="rect-event style no-toggle-dyslexia no-toggle-line-spacing">${event.style1.name}</div>
                                            <div class="rect-event style no-toggle-dyslexia no-toggle-line-spacing">${event.style2.name}</div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', eventHtml);
                });
            } else {
                container.innerHTML = '<p class="search-error">Aucun événement ne correspond a votre recherche.</p>';
            }
        })
        .catch(error => console.error('Erreur lors de la requête Ajax:', error));
    }

    // Écouteur d'événements pour le champ de recherche
    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const url = `index.php?route=search&q=${encodeURIComponent(query)}`;
        updateEvents(url);
    });

    // Écouteur d'événements pour le select
    typeSelect.addEventListener('change', function() {
        const type = this.value;
        const url = `index.php?route=filterEvents&type=${encodeURIComponent(type)}`;
        updateEvents(url);
    });
});
