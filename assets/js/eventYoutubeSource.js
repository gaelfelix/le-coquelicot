document.addEventListener("DOMContentLoaded", function () {
    const iframeContainer = document.querySelector(".iframe-container"); // Sélectionner le conteneur par classe
    const videoId = iframeContainer.id; // Récupérer l'ID de la div

    // Supprimer l'iframe existante si elle existe
    const existingIframe = iframeContainer.querySelector("iframe");
    if (existingIframe) {
        existingIframe.remove();
    }

    // Vérifie que videoId n'est pas vide
    if (videoId) {
        // Créer une nouvelle iframe
        const newIframe = document.createElement("iframe");
        newIframe.allowFullscreen = true;
        newIframe.src = "https://www.youtube-nocookie.com/embed/" + videoId; // Utiliser l'ID de la div
        newIframe.style.border = "none"; // Supprimer la bordure par défaut

        // Ajouter la classe responsive pour gérer la taille via le CSS
        newIframe.classList.add("responsive-iframe");

        // Ajouter la nouvelle iframe au conteneur
        iframeContainer.appendChild(newIframe);
    } else {
        console.error("Aucun ID de vidéo trouvé.");
    }
});