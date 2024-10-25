document.addEventListener("DOMContentLoaded", function () {
    const iframeContainer = document.querySelector(".iframe-container");
    
    // Vérifier si le conteneur existe
    if (!iframeContainer) {
        return;
    }
    
    const videoId = iframeContainer.id;

    // Supprimer l'iframe existante si elle existe
    const existingIframe = iframeContainer.querySelector("iframe");
    if (existingIframe) {
        existingIframe.remove();
    }

    // Vérifie que videoId n'est pas vide et n'est pas "null"
    if (videoId && videoId !== "null") {
        // Créer une nouvelle iframe
        const newIframe = document.createElement("iframe");
        newIframe.allowFullscreen = true;
        newIframe.src = "https://www.youtube-nocookie.com/embed/" + videoId;
        newIframe.style.border = "none";
        newIframe.classList.add("responsive-iframe");
        iframeContainer.appendChild(newIframe);
    } else {
        // Optionnel : cacher ou supprimer le conteneur si pas de vidéo
        iframeContainer.style.display = 'none';
        // ou
        // iframeContainer.remove();
    }
});