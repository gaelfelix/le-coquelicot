<?php

class Router
{
    private $routes = [
        'admin-types-styles' => ['DashboardController', 'adminTypesStyles'],
        'add-type' => ['DashboardController', 'addType'],
        'delete-type' => ['DashboardController', 'deleteType'],
        'add-style' => ['DashboardController', 'addStyle'],
        'delete-style' => ['DashboardController', 'deleteStyle'],
        'accueil' => ['DefaultController', 'accueil'],
        'inscription' => ['AuthController', 'register'],
        'connexion' => ['AuthController', 'login'],
        'deconnexion' => ['AuthController', 'logout'],
        'programmation' => ['EventController', 'events'],
        'evenement' => ['EventController', 'event'],
        'actualites' => ['ActualityController', 'actualities'],
        'actualite' => ['ActualityController', 'actuality'],
        'l-association' => ['DefaultController', 'association'],
        'adherer-faire-un-don' => ['DefaultController', 'adhesionDonate'],
        'info-contact' => ['DefaultController', 'infoContact'],
        'artiste-pro' => ['DefaultController', 'artistePro'],
        'espace-admin' => ['DashboardController', 'adminDashboard'],
        'admin-utilisateurs' => ['DashboardController', 'adminUsers'],
        'admin-evenements' => ['DashboardController', 'adminEvents'],
        'admin-actualites' => ['DashboardController', 'adminActualities'],
    ];

    private $controllers = [];

    public function __construct()
    {
        $this->controllers = [
            'AuthController' => new AuthController(),
            'DefaultController' => new DefaultController(),
            'EventController' => new EventController(),
            'ActualityController' => new ActualityController(),
            'DashboardController' => new DashboardController(),
        ];
    }

    public function handleRequest(array $get, array $post): void
    {
        $route = $get['route'] ?? 'accueil';

        if (isset($this->routes[$route])) {
            [$controllerName, $methodName] = $this->routes[$route];
            
            if (isset($this->controllers[$controllerName])) {
                $controller = $this->controllers[$controllerName];
                
                if (method_exists($controller, $methodName)) {
                    // Vérifier les autorisations si nécessaire
                    if ($this->checkAuthorization($route)) {
                        $controller->$methodName($get, $post);
                    } else {
                        header("Location: index.php?route=connexion");
                        exit();
                    }
                } else {
                    $this->notFound();
                }
            } else {
                $this->notFound();
            }
        } else {
            $this->notFound();
        }
    }

    private function checkAuthorization($route): bool
    {
        $authController = $this->controllers['AuthController'];
        
        $restrictedRoutes = [
            'artiste-pro' => ['ARTISTE', 'PRO'],
            'espace-admin' => 'ADMIN',
            'admin-utilisateurs' => 'ADMIN',
            'admin-evenements' => 'ADMIN',
            'admin-actualites' => 'ADMIN',
            'admin-types-styles' => 'ADMIN',
            'add-type' => 'ADMIN',
            'delete-type' => 'ADMIN',
            'add-style' => 'ADMIN',
            'delete-style' => 'ADMIN',
        ];

        if (isset($restrictedRoutes[$route])) {
            $requiredRole = $restrictedRoutes[$route];
            return $authController->isUserLoggedIn() && $authController->isUserRole($requiredRole);
        }

        return true;
    }

    private function notFound(): void
    {
        $this->controllers['DefaultController']->error404();
    }
}