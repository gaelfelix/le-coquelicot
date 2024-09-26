<?php

class NewsletterManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    // Méthode pour ajouter un abonné
    public function addSubscriber(Newsletter $newsletter): bool
    {
        $query = $this->db->prepare("
            INSERT INTO newsletter_subscribers (first_name, last_name, email, created_at)
            VALUES (:firstName, :lastName, :email, :createdAt)
        ");
        $query->bindValue(':firstName', $newsletter->getFirstName());
        $query->bindValue(':lastName', $newsletter->getLastName());
        $query->bindValue(':email', $newsletter->getEmail());
        $query->bindValue(':createdAt', $newsletter->getCreatedAt()->format('Y-m-d H:i:s'));

        return $query->execute();
    }

    // Méthode pour trouver un abonné par email
    public function findByEmail(string $email): ?Newsletter
    {
        $query = $this->db->prepare("SELECT * FROM newsletter_subscribers WHERE email = :email");
        $query->bindValue(':email', $email);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Newsletter(
                $result['first_name'],
                $result['last_name'],
                $result['email'],
                new DateTime($result['created_at'])
            );
        }

        return null;
    }
}
