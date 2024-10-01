<?php

class DefaultController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function accueil(string $eventId = null, string $actualityId = null) : void
    {
        $em = new EventManager();
        $am = new ActualityManager();
        
        $events = $em->findLatest();
        $event = $eventId ? $em->findOne(intval($eventId)) : null;
        $actualities = $am->findLatest();
        $actuality = $actualityId ? $am->findOne(intval($actualityId)) : null;

        $scripts = $this->addScripts([
            'assets/js/glider.js',
            'assets/js/ajaxNewsletter.js'
        ]);

        $this->render("accueil.html.twig", [
            "events" => $events,
            "event" => $event,
            "actualities" => $actualities,
            "actuality" => $actuality,
        ], $scripts);
    }

    public function association() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("association.html.twig", [], $scripts);
    }

    public function infoContact() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("info_contact.html.twig", [], $scripts);
    }

    public function adhesionDonate() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("adherer_faire_un_don.html.twig", [], $scripts);
    }

    public function artistePro() : void
    {
        $scripts = $this->addScripts([
        ]);
        
        $this->render("artiste-pro.html.twig", [], $scripts);
    }

    public function error404() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("erreur-404.html.twig", [], $scripts);
    }

}