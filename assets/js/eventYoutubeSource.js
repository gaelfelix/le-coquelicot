document.addEventListener("DOMContentLoaded", function () {
    const iframeContainer = document.querySelector(".iframe-container");
    const videoId = iframeContainer.id;

    // Remove existing iframe if present
    const existingIframe = iframeContainer.querySelector("iframe");
    if (existingIframe) existingIframe.remove();

    // Create and append new iframe if video ID exists
    if (videoId) {
        const newIframe = document.createElement("iframe");
        newIframe.allowFullscreen = true;
        newIframe.src = `https://www.youtube-nocookie.com/embed/${videoId}`;
        newIframe.style.border = "none";
        newIframe.classList.add("responsive-iframe");
        iframeContainer.appendChild(newIframe);
    } else {
        console.error("No video ID found.");
    }
});