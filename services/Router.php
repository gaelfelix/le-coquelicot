<?php

class Router
{
    private AuthController $ac;
    private DefaultController $dc;
    private ActualityController $actuc;
    private EventController $ec;
    private NewsletterController $nc;
    private ContactController $cc;
    private DashboardController $dashc;

    public function __construct()
    {
        $this->ac = new AuthController();
        $this->dc = new DefaultController();
        $this->ec = new EventController();
        $this->actuc = new ActualityController();
        $this->nc = new NewsletterController();
        $this->cc = new ContactController();
        $this->dashc = new DashboardController();
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
                $this->handleUserRoute(function() {
                    $this->dc->artistePro();
                });
                break;
            
            case 'espace-admin':
                $this->handleAdminRoute(function() {
                    $this->dashc->adminDashboard();
                });
                break;

            case 'admin-utilisateurs':
                $this->handleAdminRoute(function() {
                    $this->dashc->adminUsers();
                });
                break;
            
            case 'admin-search-user':
                $this->handleAdminRoute(function() {
                    $this->dashc->searchUsers();
                });
                break;
            
            case 'admin-delete-user':
                $this->handleAdminRoute(function() {
                    $this->dashc->deleteUser();
                });
                break;
                            
            case 'admin-evenements':
                $this->handleAdminRoute(function() {
                    $this->dashc->adminEvents();
                });
                break;
            
            case 'admin-search-event':
                $this->handleAdminRoute(function() {
                    $this->dashc->searchEvents();
                });
                break;

            case 'admin-create-event':
                $this->handleAdminRoute(function() {
                    $this->dashc->createEvent();
                });
                break;
            
            case 'admin-update-event':
                $this->handleAdminRoute(function() {
                    $this->dashc->updateEvent();
                });
                break;

            case 'get-event-data':
                $this->handleAdminRoute(function() {
                    $this->dashc->getEventData();
                });
                break;
                    
            case 'admin-delete-event':
                $this->handleAdminRoute(function() {
                    $this->dashc->deleteEvent();
                });
                break;
                        
            case 'admin-actualites':
                $this->handleAdminRoute(function() {
                    $this->dashc->adminActualities();
                });
                break;

            case 'admin-search-actuality':
                $this->handleAdminRoute(function() {
                    $this->dashc->searchActualities();
                });
                break;

            case 'admin-create-actuality':
                $this->handleAdminRoute(function() {
                    $this->dashc->createActuality();
                });
                break;
            
            case 'admin-update-actuality':
                $this->handleAdminRoute(function() {
                    $this->dashc->updateActuality();
                });
                break;

            case 'get-actuality-data':
                $this->handleAdminRoute(function() {
                    $this->dashc->getActualityData();
                });
                break;

            case 'admin-delete-actuality':
                $this->handleAdminRoute(function() {
                    $this->dashc->deleteActuality();
                });
                break;

            case 'admin-types-styles':
                $this->handleAdminRoute(function() {
                    $this->dashc->adminTypesStyles();
                });
                break;

            case 'add-type':
                $this->handleAdminRoute(function() {
                    $this->dashc->addType();
                });
                break;

            case 'delete-type':
                $this->handleAdminRoute(function() {
                    $this->dashc->deleteType();
                });
                break;

            case 'add-style':
                $this->handleAdminRoute(function() {
                    $this->dashc->addStyle();
                });
                break;

            case 'delete-style':
                $this->handleAdminRoute(function() {
                    $this->dashc->deleteStyle();
                });
                break;

            case 'inscription-newsletter':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->nc->subscribe();
                } else {
                    http_response_code(405);
                    echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
                }
                break;

            case 'clear-error-message':
                $this->handleAdminRoute(function() {
                    $this->dashc->clearErrorMessage();
                });
                break;
            
            case 'envoi-message':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->cc->sendContact();
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

    private function handleAdminRoute(callable $callback): void
    {
        if ($this->ac->isUserLoggedIn()) {
            if ($this->ac->isUserRole("ADMIN")) {
                $callback();
            } else {
                header("Location: index.php?route=connexion");
                exit();
            }
        } else {
            header("Location: index.php?route=connexion");
            exit();
        }
    }

    private function handleUserRoute(callable $callback): void
    {
        if ($this->ac->isUserLoggedIn()) {
            if ($this->ac->isUserRole("ARTISTE") || $this->ac->isUserRole("PRO")) {
                $callback();
            } else {
                header("Location: index.php?route=connexion");
                exit();
            }
        } else {
            header("Location: index.php?route=connexion");
            exit();
        }
    }
}