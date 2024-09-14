<?php

class Router
{
    private AuthController $ac;
    private DefaultController $dc;
    private ActualityController $actuc;
    private EventController $ec;
    private TicketingController $tc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->dc = new DefaultController();
        $this->ec = new EventController();
        $this->actuc = new ActualityController();
        $this->tc = new TicketingController();
    }

    public function handleRequest(array $get) : void
    {
        if (!isset($get["route"])) {
            $this->dc->accueil();
            return;
        }

        switch ($get["route"]) {
            case "accueil":
                $this->dc->accueil();
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

            default:
                $this->dc->error404();
                break;
        }
    }
}
