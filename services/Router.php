<?php

class Router
{
    private AuthController $ac;
    private DefaultController $dc;
    private ActualityController $actuc;
    private EventController $ec;
    private TicketingController $tc;
    private NewsletterController $nc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->dc = new DefaultController();
        $this->ec = new EventController();
        $this->actuc = new ActualityController();
        $this->tc = new TicketingController();
        $this->nc = new NewsletterController(); // Ajout du NewsletterController
    }

    public function handleRequest(array $get): void
    {
        // Si aucune route n'est définie, charger la page d'accueil
        if (!isset($get["route"])) {
            $eventId = isset($get['eventId']) ? $get['eventId'] : null;
            $actualityId = isset($get['actualityId']) ? $get['actualityId'] : null;
            $this->dc->accueil($eventId, $actualityId);
            return;
        }

        switch ($get["route"]) {
            case "accueil":
                $eventId = isset($get['eventId']) ? $get['eventId'] : null;
                $actualityId = isset($get['actualityId']) ? $get['actualityId'] : null;
                $this->dc->accueil($eventId, $actualityId);
                break;

            case "inscription":
                $this->ac->register();
                break;

            case "check-register":
                $this->ac->checkRegister();
                break;

            case "connexion":
                $this->ac->login();
                break;

            case "check-login":
                $this->ac->checkLogin();
                break;

            case "deconnexion":
                $this->ac->logout();
                break;

            case "programmation":
                $this->ec->events();
                break;

            case "evenement":
                if (isset($get["id"])) {
                    $this->ec->event($get["id"]);
                } else {
                    $this->dc->accueil();
                }
                break;

            case "actualites":
                $this->actuc->actualities();
                break;

            case "actualite":
                if (isset($get["id"])) {
                    $this->actuc->actuality($get["id"]);
                } else {
                    $this->dc->accueil();
                }
                break;

            case "l-association":
                $this->dc->association();
                break;

            case "billetterie":
                $this->tc->ticketing();
                break;

            case "adherer-faire-un-don":
                $this->dc->adhesionDonate();
                break;

            case "info-contact":
                $this->dc->infoContact();
                break;

            case "espace-perso":
                if (isset($get['user_id']) && isset($_SESSION['user']) && $_SESSION['user']->getId() === intval($get['user_id'])) {
                    $this->ac->espacePerso();
                } else {
                    header("Location: index.php?route=connexion");
                    exit();
                }
                break;

            case 'espace-admin':
                if (isset($_SESSION['user']) && $_SESSION['user']->getRole() === "ADMIN") {
                    $this->ac->espaceAdmin();
                } else {
                    header("Location: index.php?route=connexion");
                    exit();
                }
                break;

            case "artiste-pro":
                $this->dc->artistePro();
                break;

            case 'subscribe-newsletter':
                $this->nc->subscribe();
                break;

            default:
                $this->dc->error404();
                break;
        }
    }
}
