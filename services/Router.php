<?php

class Router
{
    private AuthController $ac;
    private DefaultController $dc;
    private ActualityController $actuc;
    private EventController $ec;
    private NewsletterController $nc;
    private ContactController $cc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->dc = new DefaultController();
        $this->ec = new EventController();
        $this->actuc = new ActualityController();
        $this->nc = new NewsletterController();
        $this->cc = new ContactController();
    }

    public function handleRequest(array $get, array $post): void
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
                
            case "search":
                $this->ec->search();
                break;
            
            case "filterEvents":
                if (isset($get['type'])) {
                    $this->ec->filterEvents($get['type']);
                } else {
                    // Gérer le cas où aucun type n'est spécifié
                    http_response_code(400);
                    echo json_encode(["success" => false, "message" => "Type non spécifié."]);
                }
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

            case "adherer-faire-un-don":
                $this->dc->adhesionDonate();
                break;

            case "info-contact":
                $this->dc->infoContact();
                break;

            case "artiste-pro":
                if ($this->ac->isUserLoggedIn()) {
                    if ($this->ac->isUserRole("USER")) {
                        $this->dc->artistePro();
                    } else {
                        header("Location: index.php?route=connexion");
                        exit();
                    }
                } else {
                    header("Location: index.php?route=connexion");
                    exit();
                }
                break;
            
            case 'espace-admin':
                if ($this->ac->isUserLoggedIn()) {
                    if ($this->ac->isUserRole("ADMIN")) {
                        $this->ac->espaceAdmin();
                    } else {
                        header("Location: index.php?route=connexion");
                        exit();
                    }
                } else {
                    header("Location: index.php?route=connexion");
                    exit();
                }
                break;

            case 'inscription-newsletter': // Mise à jour de la route pour la requête AJAX
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->nc->subscribe(); // Appeler la méthode subscribe sans paramètres
                } else {
                    http_response_code(405);
                    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
                }
                break;
            
            case 'envoi-message': // Mise à jour de la route pour la requête AJAX
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->cc->sendContact(); // Appeler la méthode sendContact sans paramètres
                } else {
                    http_response_code(405);
                    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
                }
                break;

            default:
                $this->dc->error404();
                break;
        }
    }
}
