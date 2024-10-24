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
        
        $events = $em->findAllEventsArray();
        $event = $eventId ? $em->findOne(intval($eventId)) : null;
        $actualities = $am->findLatest();
        $actuality = $actualityId ? $am->findOne(intval($actualityId)) : null;

        $translatedEvents = [];

        foreach ($events as $eventItem) {

            $dateValues = $this->translateDate($eventItem['date']);
            
            $translatedEvents[] = [
                "event" => $eventItem,
                'shortDay' => $dateValues['shortDay'],
                'number' => $dateValues['number'],
                'shortMonth' => $dateValues['shortMonth'],
            ];
        }

        $scripts = $this->addScripts([
            'assets/js/glider.js',
            'assets/js/ajaxNewsletter.js'
        ]);

        $this->render("accueil.html.twig", [
            "events" => $translatedEvents,
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
            'assets/js/ajaxContact.js'
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
    
        
        if (isset($_SESSION["user"])) {
            
            $um = new UserManager();
            $user = $um->findById($_SESSION["user"]);
    
            if ($user !== null && ($user->getRole() === 'PRO' ||$user->getRole() === 'ARTISTE')) {
            
                $this->render("artiste-pro.html.twig", [], $scripts);
            } else {
                $this->error404();
            }
        } else {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            $this->redirect("index.php?route=connexion");
            exit();
        }
    }

    public function error404() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("erreur-404.html.twig", [], $scripts);
    }

}