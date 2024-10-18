<?php
class NewsletterController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    // Méthode pour gérer l'inscription à la newsletter
    public function subscribe(): void
    {
        $nm = new NewsletterManager();
        $response = ["success" => false, "message" => ""];

        // Vérifier que la requête est en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $email = $_POST['email'] ?? '';

            // Validation des données
            $errors = $this->validateInput($firstName, $lastName, $email);
            if (!empty($errors)) {
                $response["message"] = implode(", ", $errors);
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            try {
                $this->validateCsrfToken();
                
                // Vérifier si l'email existe déjà
                if ($nm->findByEmail($email) === null) {
                    $newsletter = new Newsletter($firstName, $lastName, $email);

                    // Ajouter l'abonné
                    if ($nm->addSubscriber($newsletter)) {
                        $response["success"] = true;
                        $response["message"] = "Inscription réussie !";
                    } else {
                        $response["message"] = "Erreur lors de l'inscription.";
                    }
                } else {
                    $response["message"] = "Cet email est déjà inscrit à la newsletter.";
                }
            } catch (Exception $e) {
                $response["message"] = $e->getMessage();
            }
        } else {
            // Requête non POST
            $response["message"] = "Méthode de requête invalide. Veuillez utiliser POST.";
        }

        // Envoyer la réponse finale en JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Méthode pour valider les données du formulaire
    private function validateInput(string $firstName, string $lastName, string $email): array
    {
        $errors = [];

        if (empty($firstName)) {
            $errors[] = 'Le prénom est requis';
        }

        if (strlen($firstName) < 2) {
            $errors[] = 'Le prénom doit faire au moins 2 caractères';
        }

        if (empty($lastName)) {
            $errors[] = 'Le nom est requis';
        }

        if (strlen($lastName) < 2) {
            $errors[] = 'Le nom doit faire au moins 2 caractères';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        return $errors;
    }
}