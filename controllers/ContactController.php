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

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $firstName = $_POST['firstName'] ?? '';
                $lastName = $_POST['lastName'] ?? '';
                $email = $_POST['email'] ?? '';
                $phone = !empty($_POST['phone']) ? $_POST['phone'] : null;
                $subject = $_POST['subject'] ?? '';
                $message = $_POST['message'] ?? '';

                $errors = $this->validateInput($firstName, $lastName, $email, $subject, $message, $phone);
                if (!empty($errors)) {
                    $response["message"] = implode(", ", $errors);
                } else {
                    $this->validateCsrfToken();
                    
                    $contact = new Contact($firstName, $lastName, $email, $phone, $subject, $message);

                    if ($cm->addContact($contact)) {
                        $response["success"] = true;
                        $response["message"] = "Message envoyé avec succès";
                    } else {
                        $response["message"] = "Une erreur est survenue lors de l'envoi du message";
                    }
                }
            } else {
                $response["message"] = "Méthode de requête invalide. Veuillez utiliser POST.";
            }
        } catch (Exception $e) {
            error_log("Erreur dans sendContact: " . $e->getMessage());
            $response["message"] = "Une erreur inattendue s'est produite.";
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    public function validateInput($firstName, $lastName, $email, $subject, $message, $phone = null): array
    {
        $errors = [];
    
        $addError = function($condition, $errorMessage) use (&$errors) {
            if ($condition) {
                $errors[] = $errorMessage;
            }
        };
    
        $addError(empty($firstName), 'Le prénom est requis');
        $addError(strlen($firstName) < 2 && !empty($firstName), 'Le prénom doit faire au moins 2 caractères');
    
        $addError(empty($lastName), 'Le nom est requis');
        $addError(strlen($lastName) < 2 && !empty($lastName), 'Le nom doit faire au moins 2 caractères');
    
        $addError(!filter_var($email, FILTER_VALIDATE_EMAIL), 'Email invalide');
    
        if (!empty($phone)) {
            $addError(strlen($phone) !== 10, 'Le numéro de téléphone doit faire exactement 10 caractères');
            $addError(!preg_match('/^\d{10}$/', $phone), 'Le numéro de téléphone doit contenir uniquement des chiffres');
        }
    
        $addError(empty($subject), 'Le sujet est requis');
        $addError(strlen($subject) < 5, 'Le sujet doit faire au moins 5 caractères');
        $addError(strlen($subject) > 255, 'Le sujet doit faire moins de 255 caractères');
    
        $addError(empty($message), 'Le message est requis');
        $addError(strlen($message) < 25, 'Le message doit faire au moins 25 caractères');
        $addError(strlen($message) > 2000, 'Le message doit faire moins de 2000 caractères');
    
        return $errors;
    }
}