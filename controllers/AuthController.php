<?php

class AuthController extends AbstractController
{
    private UserManager $um;
    private SpecializationManager $sm;

    public function __construct()
    {
        parent::__construct();
        $this->um = new UserManager();
        $this->sm = new SpecializationManager();
    }
    
    public function login() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("connexion.html.twig", [], $scripts);
    }

    public function checkLogin() : void
    {
        $scripts = $this->addScripts([]);
    
        if (isset($_POST["email"]) && isset($_POST["password"])) {
            $tokenManager = new CSRFTokenManager();
    
            if (isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"])) {
                $user = $this->um->findByEmail($_POST["email"]);
    
                if ($user !== null) {
                    if (password_verify($_POST["password"], $user->getPassword())) {
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }
    
                        $_SESSION["user"] = $user->getId();
                        unset($_SESSION["error_message"]);
    
                        if ($user->getRole() === "ADMIN") {
                            $this->redirect("index.php?route=espace-admin");
                        } else if ($user->getRole() === "ARTISTE" || $user->getRole() === "PRO") {
                            $this->redirect("index.php?route=artiste-pro");
                        } else {
                            $this->redirect("index.php");
                        }
                    } else {
                        $_SESSION["error_message"] = "Informations de connexion invalides";
                        $this->render("connexion.html.twig", [], $scripts);
                    }
                } else {
                    $_SESSION["error_message"] = "Informations de connexion invalides";
                    $this->render("connexion.html.twig", [], $scripts);
                }
            } else {
                $_SESSION["error_message"] = "Invalidité du jeton CSRF";
                $this->render("connexion.html.twig", [], $scripts);
            }
        } else {
            $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            $this->render("connexion.html.twig", [], $scripts);
        }
    }
    

    public function register() : void
    {
        $scripts = $this->addScripts([
            'assets/js/selectRegister.js',
        ]);

        $this->render("inscription.html.twig", [], $scripts);
    }


    public function checkRegister(): void
    {
        $scripts = $this->addScripts([]);
    
        // Vérifiez si le formulaire a été soumis
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vérifiez que tous les champs requis sont remplis
            if (
                isset($_POST["first-name"]) && 
                isset($_POST["last-name"]) && 
                isset($_POST["email"]) && 
                isset($_POST["password"]) && 
                isset($_POST["confirm-password"]) &&
                isset($_POST["role"]) && 
                isset($_POST["specialization"]) &&
                isset($_POST["structure"]) &&
                isset($_POST["csrf-token"])
            ) {
                $tokenManager = new CSRFTokenManager();
    
                // Validez le jeton CSRF
                if ($tokenManager->validateCSRFToken($_POST["csrf-token"])) {
                    // Vérifiez que les mots de passe correspondent
                    if ($_POST["password"] === $_POST["confirm-password"]) {
                        $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";
    
                        // Vérifiez le format du mot de passe
                        if (preg_match($password_pattern, $_POST["password"])) {
                            // Vérifiez si l'e-mail est déjà utilisé
                            $existingUser = $this->um->findByEmail($_POST["email"]);
                            if ($existingUser === null) {
                                // Récupérez les données du formulaire
                                $firstName = $_POST["first-name"];
                                $lastName = $_POST["last-name"];
                                $email = $_POST["email"];
                                $role = $_POST["role"];
                                $specializationId = $_POST["specialization"];
                                $structure = $_POST["structure"];
                                $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Hashage du mot de passe
                                
                                $specialization = $this->sm->findOne($specializationId);
    
                                // Créez l'utilisateur avec les setters
                                $user = new User(
                                    $firstName,
                                    $lastName,
                                    $email,
                                    $password,
                                    $role,
                                    $specialization,
                                    null, // Média à null
                                    null, // Création à la date actuelle
                                    $structure
                                );
    
                                // Enregistrez l'utilisateur dans la base de données
                                $this->um->create($user);
    
                                // Stockez l'identifiant de l'utilisateur dans la session
                                $_SESSION["user"] = $user->getId();
    
                                // Redirection vers la page d'accueil ou de succès
                                $this->redirect("index.php");
                            } else {
                                $_SESSION["error_message"] = "Cette adresse e-mail est déjà utilisée";
                            }
                        } else {
                            $_SESSION["error_message"] = "Le mot de passe doit contenir une lettre majuscule, une lettre minuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 8 caractères";
                        }
                    } else {
                        $_SESSION["error_message"] = "Les mots de passe ne sont pas identiques";
                    }
                } else {
                    $_SESSION["error_message"] = "Invalidité du jeton CSRF";
                }
            } else {
                $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            }
        }
    
        // Affichez le formulaire d'inscription avec les messages d'erreur
        $this->render("inscription.html.twig", [], $scripts);
    }
    
    
    

    public function espacePerso() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("adherer_faire_un_don.html.twig", [], $scripts);
    }

    public function logout() : void
    {
        session_destroy();

        $this->redirect("index.php");
    }

    public function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user']) && $this->getLoggedInUser() !== null;
    }

    // Vérifie le rôle de l'utilisateur connecté
    public function isUserRole(string $role): bool
    {
        $user = $this->getLoggedInUser();
        return $user !== null && $user->getRole() === $role;
    }

    // Récupère l'utilisateur connecté à partir de l'ID en session
    private function getLoggedInUser(): ?User
    {
        if (isset($_SESSION['user'])) {
            $userId = $_SESSION['user'];
            return $this->um->findById($userId); // Utilise le UserManager pour récupérer l'utilisateur
        }
        return null;
    }
}