<?php

class DefaultController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function accueil() : void
    {
        $em = new EventManager();
        $am = new ActualityManager();

        $events = $em->findLatest();
        $event = null;
        $actualities = $am->findLatest();

        $this->render("accueil.html.twig", ["events" => $events, "event" => $event, "actualities" => $actualities]);
    }

    public function association() : void
    {
        $this->render("association.html.twig", []);
    }

    public function infoContact() : void
    {
        $this->render("info_contact.html.twig", []);
    }

    public function adhesionDonate() : void
    {
        $this->render("adherer_faire_un_don.html.twig", []);
    }

    public function artistePro() : void
    {
        $this->render("artiste-pro.html.twig", []);
    }

    public function error404() : void
    {
        $this->render("erreur-404.html.twig", []);
    }

}