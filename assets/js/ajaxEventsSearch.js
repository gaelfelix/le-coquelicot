document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector('input[name="q"]');
    const typeSelect = document.getElementById('type');
    const searchButton = document.querySelector('.search-button');
    const container = document.querySelector('.programmation-events-container');
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

    // Fonction pour mettre à jour les événements affichés
    function updateEvents(url) {
        container.innerHTML = '<p class="loading">Chargement des événements...</p>'; // Message de chargement

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'  // Important pour la détection Ajax côté serveur
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(events => {
            container.innerHTML = ''; // Vider le container avant d'ajouter les résultats

            if (events.length > 0) {
                events.forEach(event => {
                    // Utiliser htmlspecialchars pour échapper les données
                    const eventHtml = `
                        <div class="event-card-container">
                            <article class="event-card" style="background-image: url('${htmlspecialchars(event.media.url)}');">
                                <a href="index.php?route=evenement&id=${htmlspecialchars(event.id)}">
                                    <div class="card-block-date mono">
                                        <p class="mono no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.shortDay)}</p>
                                        <p class="mono no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.number)}</p>
                                        <p class="mono no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.shortMonth)}</p>                              
                                    </div>                        
                                    <div class="info-event-container">
                                        <h3 class="mono no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.name)}</h3>
                                        <div class="rect-event-container">
                                            <div class="rect-event type no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.type.name)}</div>
                                            <div class="rect-event style no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.style1.name)}</div>
                                            <div class="rect-event style no-toggle-dyslexia no-toggle-line-spacing">${htmlspecialchars(event.style2.name)}</div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', eventHtml);
                });
            } else {
                container.innerHTML = '<p class="search-error">Aucun événement ne correspond à votre recherche.</p>';
            }
        })
        .catch(error => {
            console.error('Erreur lors de la requête Ajax:', error);
            container.innerHTML = '<p class="search-error">Erreur de chargement des événements. Veuillez réessayer.</p>';
        });
    }

    // Écouteur d'événements pour le champ de recherche
    searchInput.addEventListener('input', function() {
        const query = searchInput.value;
        const url = `index.php?route=search&q=${encodeURIComponent(query)}`;
        updateEvents(url);
    });

    // Écouteur d'événements pour le select
    typeSelect.addEventListener('change', function() {
        searchInput.value = ''; // Vider le champ de recherche
        const type = this.value;
        const url = `index.php?route=filterEvents&type=${encodeURIComponent(type)}`;
        updateEvents(url);
    });
});
