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

            // Validation basique
            if (!empty($firstName) && !empty($lastName) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            } else {
                $response["message"] = "Veuillez remplir tous les champs correctement.";
            }
        } else {
            $response["message"] = "Méthode de requête invalide. Veuillez utiliser POST.";
        }

        // Envoyer la réponse en JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }
}