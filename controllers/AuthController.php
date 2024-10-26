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
        $scripts = $this->addScripts(['assets/js/passwordEye.js']);

        $this->render("connexion.html.twig", [], $scripts);
    }

    public function checkLogin() : void
    {
        $scripts = $this->addScripts([]);
    
        if (isset($_POST["email"]) && isset($_POST["password"])) {
            try {
                $this->validateCsrfToken();
                
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
            } catch (Exception $e) {
                $_SESSION["error_message"] = $e->getMessage();
                $this->render("connexion.html.twig", [], $scripts);
            }
        } else {
            $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            $this->render("connexion.html.twig", [], $scripts);
        }
    }

    public function register() : void
    {
        $scripts = $this->addScripts(['assets/js/selectRegister.js', 'assets/js/passwordEye.js']);
        $this->render("inscription.html.twig", [], $scripts);
    }

    public function checkRegister(): void
    {
        $scripts = $this->addScripts([]);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (
                isset($_POST["first-name"]) && 
                isset($_POST["last-name"]) && 
                isset($_POST["email"]) && 
                isset($_POST["password"]) && 
                isset($_POST["confirm-password"]) &&
                isset($_POST["role"]) && 
                isset($_POST["specialization"]) &&
                isset($_POST["structure"])
            ) {
                try {
                    $this->validateCsrfToken();
    
                    if ($_POST["password"] === $_POST["confirm-password"]) {
                        $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";
    
                        if (preg_match($password_pattern, $_POST["password"])) {
                            $existingUser = $this->um->findByEmail($_POST["email"]);
                            if ($existingUser === null) {
                                $firstName = $_POST["first-name"];
                                $lastName = $_POST["last-name"];
                                $email = $_POST["email"];
                                $role = $_POST["role"];
                                $specializationId = $_POST["specialization"];
                                $structure = $_POST["structure"];
                                $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
                                
                                $specialization = $this->sm->findOne($specializationId);
    
                                $user = new User(
                                    $firstName,
                                    $lastName,
                                    $email,
                                    $password,
                                    $role,
                                    $specialization,
                                    null,
                                    null,
                                    $structure
                                );
    
                                $this->um->create($user);
    
                                $_SESSION["user"] = $user->getId();
    
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
                } catch (Exception $e) {
                    $_SESSION["error_message"] = $e->getMessage();
                }
            } else {
                $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            }
        }
    
        $this->render("inscription.html.twig", [], $scripts);
    }

    public function espacePerso() : void
    {
        $scripts = $this->addScripts([]);
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

    public function isUserRole(string $role): bool
    {
        $user = $this->getLoggedInUser();
        return $user !== null && $user->getRole() === $role;
    }

    private function getLoggedInUser(): ?User
    {
        if (isset($_SESSION['user'])) {
            $userId = $_SESSION['user'];
            return $this->um->findById($userId);
        }
        return null;
    }
}