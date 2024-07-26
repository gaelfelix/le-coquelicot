<?php

class DefaultController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function home() : void
    {
        $em = new EventManager();
        $am = new ActualityManager;

        $events = $em->findLatest();
        $actualities = $am->findLatest();

        $this->render("accueil.html.twig", ["events" => $events, "actualities" => $actualities]);
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

    public function carousel() : void
    {
        $em = new EventManager();

        $events = $em->findLatest();
        $this->render("carousel.html.twig", ["events" => $events]);
    }
}