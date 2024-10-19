// Combined initialization function for Tarteaucitron and HelloAsso
function initTarteaucitronAndHelloAsso() {
    // Check if Tarteaucitron is defined
    if (typeof tarteaucitron === 'undefined') {
        // If Tarteaucitron is not yet loaded, retry in 100ms
        setTimeout(initTarteaucitronAndHelloAsso, 100);
        return;
    }

    // Tarteaucitron configuration for cookie management
    tarteaucitron.init({
        "privacyUrl": "", /* Privacy policy URL */
        "hashtag": "#tarteaucitron", /* Open the panel with this hashtag */
        "cookieName": "tarteaucitron", /* Cookie name */
        "orientation": "middle", /* Banner position (top - bottom) */
        "groupServices": false, /* Group services by category */
        "showAlertSmall": false, /* Show the small bottomright banner */
        "cookieslist": false, /* Show the cookie list */
        "closePopup": false, /* Show a close X on the banner */
        "showIcon": true, /* Show cookie icon to manage cookies */
        "iconPosition": "BottomRight", /* Position of the icon */
        "adblocker": false, /* Show a message if an adblocker is detected */
        "DenyAllCta" : true, /* Show the deny all button */
        "AcceptAllCta" : true, /* Show the accept all button */
        "highPrivacy": true, /* Disable auto consent */
        "handleBrowserDNTRequest": false, /* Handle Browser DNT request */
        "removeCredit": false, /* Remove credit link */
        "moreInfoLink": true, /* Show more info link */
        "useExternalCss": false, /* Use external css file */
        "mandatory": true, /* Show a message about mandatory cookies */
        "cssPath": "/assets/js/tarteaucitron/css/tarteaucitron.css",
        "js": "/assets/js/tarteaucitron/tarteaucitron.js",
        "lang": "/assets/js/tarteaucitron/lang/tarteaucitron.fr.js",
    });

    // HelloAsso service definition
    tarteaucitron.services.helloasso = {
        "key": "helloasso",
        "type": "other",
        "name": "HelloAsso",
        "uri": "https://www.helloasso.com/confidentialite",
        "needConsent": true,
        "cookies": ['_helloasso_session'],
        "js": function () {
            "use strict";
            let widgets = document.querySelectorAll('.tac_helloasso');
            widgets.forEach(function(widget) {
                if (!widget.querySelector('iframe')) {
                    let iframe = document.createElement('iframe');
                    iframe.src = widget.getAttribute('data-url');
                    iframe.width = widget.getAttribute('width') || '100%';
                    iframe.height = widget.getAttribute('height') || '800px';
                    iframe.style.border = 'none';
                    iframe.sandbox = 'allow-scripts allow-same-origin allow-forms';
                    widget.appendChild(iframe);
                }
            });
        },
        "fallback": function () {
            "use strict";
            let id = 'helloasso';
            tarteaucitron.fallback(['tac_helloasso'], tarteaucitron.engage(id));
        }
    };

    // Add HelloAsso service to Tarteaucitron
    (tarteaucitron.job = tarteaucitron.job || []).push('helloasso');

    // Debug function
    function debugTarteaucitron() {
        let widgets = document.querySelectorAll('.tac_helloasso');
    }

    // Listen for Tarteaucitron loaded event
    document.addEventListener('tarteaucitron_loaded', function () {
        debugTarteaucitron();
    });

    // Listen for service allowed event
    document.addEventListener('tarteaucitron_service_allowed', function (event) {
        if (event.detail === 'helloasso') {
            tarteaucitron.services.helloasso.js();
        }
    });

    // Initial call to debug function
    debugTarteaucitron();
}

// Execute the initialization function
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTarteaucitronAndHelloAsso);
} else {
    initTarteaucitronAndHelloAsso();
}