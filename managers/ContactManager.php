<?php

class ContactManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addContact(Contact $contact): bool
    {

        $query = $this->db->prepare("
            INSERT INTO contact (first_name, last_name, email, phone, subject, message, `read`, created_at)
            VALUES (:firstName, :lastName, :email, :phone, :subject, :message, :read, :createdAt)
        ");

        error_log("Inserting contact: " . json_encode([
            'firstName' => $contact->getFirstName(),
            'lastName' => $contact->getLastName(),
            'email' => $contact->getEmail(),
            'phone' => $contact->getPhone(),
            'subject' => $contact->getSubject(),
            'message' => $contact->getMessage(),
            'read' => $contact->isRead(),
            'createdAt' => $contact->getCreatedAt()->format('Y-m-d H:i:s'),
        ]));

        $query->bindValue(':firstName', $contact->getFirstName());
        $query->bindValue(':lastName', $contact->getLastName());
        $query->bindValue(':email', $contact->getEmail());
        
        $phoneValue = $contact->getPhone() ? substr(trim($contact->getPhone()), 0, 10) : null;
        $query->bindValue(':phone', $phoneValue); 
        
        $query->bindValue(':subject', $contact->getSubject());
        $query->bindValue(':message', $contact->getMessage());
        $query->bindValue(':read', $contact->isRead() ? 1 : 0); 
        $query->bindValue(':createdAt', $contact->getCreatedAt()->format('Y-m-d H:i:s'));

        return $query->execute();
    }
}
