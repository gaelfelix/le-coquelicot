<?php

class DashboardController extends AbstractController
{
    private UserManager $um;
    private EventManager $em;
    private ActualityManager $am;

    public function __construct()
    {
        parent::__construct();
        $this->um = new UserManager();
        $this->em = new EventManager();
        $this->am = new ActualityManager();
    }

    public function adminDashboard() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("admin/espace-admin.html.twig", [], $scripts);
    }

    public function adminUsers() : void
    {
        $scripts = $this->addScripts([
            'assets/js/ajaxUsersDashboard.js',
        ]);

        $users = $this->um->findAll();
        $this->render("admin/admin-utilisateurs.html.twig", ["users" => $users], $scripts);
    }

    public function searchUsers() : void
    {
        if ($this->isAjaxRequest()) {
            $query = $_GET['q'] ?? ''; 
            $role = $_GET['role'] ?? 'all'; // Récupère le rôle du paramètre
    
            $users = $this->um->searchUsers($query, $role);
            
            $userArray = array_map(function($user) {
                return [
                    'id' => $user->getId(),
                    'firstname' => $user->getFirstName(),
                    'lastname' => $user->getLastName(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                    'structure' => $user->getStructure(),
                    'specialization' => $user->getSpecialization() ? $user->getSpecialization()->getName() : null
                ];
            }, $users);
    
            header('Content-Type: application/json');
            echo json_encode($userArray);
            exit;
        
        } else {
            error_log("La requête n'est pas AJAX");
        }
    }

    public function deleteUser() : void
    {
        if ($this->isAjaxRequest()) {
            $userId = $_GET['id'] ?? null;

            if ($userId && is_numeric($userId)) {
                $result = $this->um->deleteUser((int)$userId);

                // Répondre au client
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression de l\'utilisateur.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID utilisateur invalide.']);
            }
            
            header('Content-Type: application/json');
            exit;
        } else {
            error_log("La requête n'est pas AJAX");
        }
    }

    public function adminEvents() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("admin/admin-evenements.html.twig", [], $scripts);
    }

    public function adminActualities() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("admin/admin-actualites.html.twig", [], $scripts);
    }

}