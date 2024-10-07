<?php

class AuthController extends AbstractController
{
    private UserManager $um;

    public function __construct()
    {
        parent::__construct();
        $this->um = new UserManager();
    }
    
    public function login() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("connexion.html.twig", [], $scripts);
    }

    public function checkLogin() : void
    {
        $scripts = $this->addScripts([
        ]);

        if(isset($_POST["email"]) && isset($_POST["password"]))
        {
            $tokenManager = new CSRFTokenManager();

            if(isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"]))
            {
                $user = $this->um->findByEmail($_POST["email"]);

                if($user !== null)
                {
                    
                    if(password_verify($_POST["password"], $user->getPassword()))
                    {
                        
                        if (session_status() == PHP_SESSION_NONE) {
                            session_start();
                        }

                        $_SESSION["user"] = $user->getId();

                        unset($_SESSION["error_message"]);

                        $this->redirect("index.php");
                    }
                    else
                    {
                        $_SESSION["error_message"] = "Informations de connexion invalides";
                        $this->render("connexion.html.twig", [], $scripts);
                    }
                }
                else
                {
                    $_SESSION["error_message"] = "Informations de connexion invalides";
                    $this->render("connexion.html.twig", [], $scripts);
                }
            }
            else
            {
                $_SESSION["error_message"] = "Invalidité du jeton CSRF";
                $this->render("connexion.html.twig", [], $scripts);
            }
        }
        else
        {
            $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            $this->render("connexion.html.twig", [], $scripts);
        }
    }

    public function register() : void
    {
        $scripts = $this->addScripts([
        ]);

        $this->render("inscription.html.twig", [], $scripts);
    }


    public function checkRegister() : void
    {
        $scripts = $this->addScripts([
        ]);
        
        if(isset($_POST["first-name"]) && isset($_POST["last-name"]) && isset($_POST["email"])
            && isset($_POST["password"]) && isset($_POST["confirm-password"]))
        {
            $tokenManager = new CSRFTokenManager();

            if(isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"]))
            {
                if($_POST["password"] === $_POST["confirm-password"])
                {
                    $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";

                    if (preg_match($password_pattern, $_POST["password"]))
                    {

                        $user = $this->um->findByEmail($_POST["email"]);

                        if($user === null)
                        {
                            $firstName = $_POST["first-name"];
                            $lastName = $_POST["last-name"];
                            $email = $_POST["email"];
                            $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

                            $user = new User($firstName, $lastName, $email, $password);
                            $this->um->create($user);

                            $_SESSION["user"] = $user->getId();

                            unset($_SESSION["error_message"]);

                            $this->redirect("index.php");
                        }
                        else
                        {
                            $_SESSION["error_message"] = "Cette adresse e-mail est déjà utilisée";
                            $this->render("inscription.html.twig", [], $scripts);
                        }
                    }
                    else {
                        $_SESSION["error_message"] = "Le mot de passe doit contenir une lettre majuscule, une lettre minuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 8 caractères";
                        $this->render("inscription.html.twig", [], $scripts);
                    }
                }
                else
                {
                    $_SESSION["error_message"] = "Les mots de passe ne sont pas identiques";
                    $this->render("inscription.html.twig", [], $scripts);
                }
            }
            else
            {
                $_SESSION["error_message"] = "Invalidité du jeton CSRF";
                $this->render("inscription.html.twig", [], $scripts);
            }
        }
        else
        {
            $_SESSION["error-message"] = "Veuillez remplir tous les champs";
            $this->render("inscription.html.twig", [], $scripts);
        }
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