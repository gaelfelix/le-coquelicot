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
        newIframe.width = "560";
        newIframe.height = "315";
        newIframe.frameBorder = "0";
        newIframe.allowFullscreen = true;
        newIframe.src = "https://www.youtube-nocookie.com/embed/" + videoId; // Utiliser l'ID de la div

        // Ajouter la nouvelle iframe au conteneur
        iframeContainer.appendChild(newIframe);
    } else {
        console.error("Aucun ID de vidéo trouvé.");
    }
});