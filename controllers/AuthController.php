<?php

class AuthController extends AbstractController
{
    
    public function __construct()
    {
        parent::__construct(); 
    }
    
    public function login() : void
    {
        $this->render("connexion.html.twig", []);
    }

    public function checkLogin() : void
    {

        if(isset($_POST["email"]) && isset($_POST["password"]))
        {
            $tokenManager = new CSRFTokenManager();

            if(isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"]))
            {
                $um = new UserManager();
                $user = $um->findByEmail($_POST["email"]);

                if($user !== null)
                {
                    if(password_verify($_POST["password"], $user->getPassword()))
                    {
                        $_SESSION["user"] = $user->getId();

                        unset($_SESSION["error-message"]);

                        $this->render("accueil.html.twig", []);
                    }
                    else
                    {
                        $_SESSION["error-message"] = "Informations de connexion invalides";
                        $this->render("connexion.html.twig", []);
                    }
                }
                else
                {
                    $_SESSION["error-message"] = "Informations de connexion invalides";
                    $this->render("connexion.html.twig", []);
                }
            }
            else
            {
                $_SESSION["error-message"] = "Invalidité du jeton CSRF";
                $this->render("connexion.html.twig", []);
            }
        }
        else
        {
            $_SESSION["error-message"] = "Veuillez remplir tous les champs";
            $this->render("connexion.html.twig", []);
        }
    }

    public function register() : void
    {
        $this->render("inscription.html.twig", []);
    }

    public function checkRegister() : void
    {
        if(isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["email"])
            && isset($_POST["password"]) && isset($_POST["confirm-password"]))
        {
            $tokenManager = new CSRFTokenManager();
            if(isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"]))
            {
                if($_POST["password"] === $_POST["confirm-password"])
                {
                    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\d\s])[A-Za-z\d^\w\s]{8,}$/';

                    if (preg_match($password_pattern, $_POST["password"]))
                    {
                        $um = new UserManager();
                        $user = $um->findByEmail($_POST["email"]);

                        if($user === null)
                        {
                            $username = htmlspecialchars($_POST["username"]);
                            $email = htmlspecialchars($_POST["email"]);
                            $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
                            $user = new User($username, $email, $password);

                            $um->create($user);

                            $_SESSION["user"] = $user->getId();

                            unset($_SESSION["error-message"]);

                            $this->render("accueil.html.twig", []);
                        }
                        else
                        {
                            $_SESSION["error-message"] = "Cette adresse e-mail est déjà utilisée";
                            $this->render("inscription.html.twig", []);
                        }
                    }
                    else {
                        $_SESSION["error-message"] = "Le mot de passe doit contenir une lettre majuscule, une lettre minuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 8 caractères";
                        $this->render("inscription.html.twig", []);
                    }
                }
                else
                {
                    $_SESSION["error-message"] = "Les mots de passe ne sont pas identiques";
                    $this->render("inscription.html.twig", []);
                }
            }
            else
            {
                $_SESSION["error-message"] = "Invalidité du jeton CSRF";
                $this->render("inscription.html.twig", []);
            }
        }
        else
        {
            $_SESSION["error-message"] = "Veuillez remplir tous les champs";
            $this->render("inscription.html.twig", []);
        }
    }

    public function logout() : void
    {
        session_destroy();

        $this->render("accueil.html.twig", []);
    }
}