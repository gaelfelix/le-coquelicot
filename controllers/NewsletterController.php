<?php

class NewsletterController
{
    private NewsletterManager $newsletterManager;

    public function __construct()
    {
        $this->newsletterManager = new NewsletterManager();
    }

    // Méthode pour gérer l'inscription à la newsletter
    public function subscribe(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $email = $_POST['email'] ?? '';

            // Validation basique
            if (!empty($firstName) && !empty($lastName) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Vérifier si l'email existe déjà
                if ($this->newsletterManager->findByEmail($email) === null) {
                    $newsletter = new Newsletter($firstName, $lastName, $email);

                    // Ajouter l'abonné
                    if ($this->newsletterManager->addSubscriber($newsletter)) {
                        // Redirection ou message de succès
                        header('Location: index.php?route=newsletter-success');
                        exit();
                    } else {
                        echo "Erreur lors de l'inscription.";
                    }
                } else {
                    echo "Cet email est déjà inscrit à la newsletter.";
                }
            } else {
                echo "Veuillez remplir tous les champs correctement.";
            }
        }
    }
}
