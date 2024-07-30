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

                        unset($_SESSION["error_message"]);

                        $this->redirect("index.php");
                    }
                    else
                    {
                        $_SESSION["error_message"] = "Informations de connexion invalides";
                        $this->render("connexion.html.twig", []);
                    }
                }
                else
                {
                    $_SESSION["error_message"] = "Informations de connexion invalides";
                    $this->render("connexion.html.twig", []);
                }
            }
            else
            {
                $_SESSION["error_message"] = "Invalidité du jeton CSRF";
                $this->render("connexion.html.twig", []);
            }
        }
        else
        {
            $_SESSION["error_message"] = "Veuillez remplir tous les champs";
            $this->render("connexion.html.twig", []);
        }
    }

    public function register() : void
    {
        $this->render("inscription.html.twig", []);
    }

    public function checkRegister() : void
    {
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
                        $um = new UserManager();
                        $user = $um->findByEmail($_POST["email"]);

                        if($user === null)
                        {
                            $firstName = $_POST["first-name"];
                            $lastName = $_POST["last-name"];
                            $email = $_POST["email"];
                            $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

                            $user = new User($firstName, $lastName, $email, $password);
                            $um->create($user);

                            $_SESSION["user"] = $user->getId();

                            unset($_SESSION["error_message"]);

                            $this->redirect("index.php");
                        }
                        else
                        {
                            $_SESSION["error_message"] = "Cette adresse e-mail est déjà utilisée";
                            $this->render("inscription.html.twig", []);
                        }
                    }
                    else {
                        $_SESSION["error_message"] = "Le mot de passe doit contenir une lettre majuscule, une lettre minuscule, un chiffre, un caractère spécial et avoir une longueur minimale de 8 caractères";
                        $this->render("inscription.html.twig", []);
                    }
                }
                else
                {
                    $_SESSION["error_message"] = "Les mots de passe ne sont pas identiques";
                    $this->render("inscription.html.twig", []);
                }
            }
            else
            {
                $_SESSION["error_message"] = "Invalidité du jeton CSRF";
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

        $this->redirect("index.php");
    }
}