<?php

class ContactController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sendContact()
    {
        
        $cm = new ContactManager();
        $response = ["success" => false, "message" => ""];

        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';

            // Validation des données
            $errors = $this->validateInput($firstName, $lastName, $email, $phone, $subject, $message);
            if (!empty($errors)) {
                $response["message"] = implode(", ", $errors);
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Vérification du token CSRF
            $tokenManager = new CSRFTokenManager();

            if (isset($_POST["csrf-token"]) && $tokenManager->validateCSRFToken($_POST["csrf-token"])) {
                $contact = new Contact($firstName, $lastName, $email, $phone, $subject, $message);

                // Envoyer le message
                if ($cm->addContact($contact)) {
                    $response["success"] = true;
                    $response["message"] = "Message envoyé avec succès";
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                } else {
                    $response["message"] = "Une erreur est survenue lors de l'envoi du message";
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            } else {
                $response["message"] = "Token CSRF invalide";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            } 
        } else {
            $response["message"] = "Méthode de requête invalide. Veuillez utiliser POST.";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }

    public function validateInput($firstName, $lastName, $email, $phone = null, $subject, $message): array
    {
        $errors = [];
    
        // Fonction pour ajouter des erreurs
        $addError = function($condition, $errorMessage) use (&$errors) {
            if ($condition) {
                $errors[] = $errorMessage;
            }
        };
    
        // Validation du prénom
        $addError(empty($firstName), 'Le prénom est requis');
        $addError(strlen($firstName) < 2 && !empty($firstName), 'Le prénom doit faire au moins 2 caractères');
    
        // Validation du nom
        $addError(empty($lastName), 'Le nom est requis');
        $addError(strlen($lastName) < 2 && !empty($lastName), 'Le nom doit faire au moins 2 caractères');
    
        // Validation de l'email
        $addError(!filter_var($email, FILTER_VALIDATE_EMAIL), 'Email invalide');
    
        // Validation du téléphone
        if ($phone !== null && $phone !== '') {
            $addError(strlen($phone) !== 10, 'Le numéro de téléphone doit faire exactement 10 caractères');
            $addError(!preg_match('/^\d{10}$/', $phone), 'Le numéro de téléphone doit contenir uniquement des chiffres');
        }
    
        // Validation du sujet
        $addError(empty($subject), 'Le sujet est requis');
        $addError(strlen($subject) < 5, 'Le sujet doit faire au moins 5 caractères');
        $addError(strlen($subject) > 255, 'Le sujet doit faire moins de 255 caractères');
    
        // Validation du message
        $addError(empty($message), 'Le message est requis');
        $addError(strlen($message) < 25, 'Le message doit faire au moins 25 caractères');
        $addError(strlen($message) > 2000, 'Le message doit faire moins de 2000 caractères');
    
        return $errors;
    }
    }