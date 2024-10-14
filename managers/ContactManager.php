<?php

class ContactManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    public function findAll(): array
    {
        $query = $this->db->query("SELECT * FROM contact ORDER BY created_at DESC");
        $contacts = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($contactData) {
            return $this->createContactFromArray($contactData);
        }, $contacts);
    }

    public function findOne(int $id): ?Contact
    {
        $query = $this->db->prepare("SELECT * FROM contact WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        
        $contactData = $query->fetch(PDO::FETCH_ASSOC);
        
        if ($contactData) {
            return $this->createContactFromArray($contactData);
        }
        
        return null;
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
    
    public function delete(int $id): bool
    {
        $query = $this->db->prepare("DELETE FROM contact WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    public function markAsRead(int $id): bool
    {
        $query = $this->db->prepare("UPDATE contact SET `read` = 1 WHERE id = :id");
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        return $query->execute();
    }

    private function createContactFromArray(array $contactData): Contact
    {
        $contact = new Contact(
            $contactData['first_name'],
            $contactData['last_name'],
            $contactData['email'],
            $contactData['phone'],
            $contactData['subject'],
            $contactData['message'],
            (bool)$contactData['read'],
            new DateTime($contactData['created_at'])
        );
        $contact->setId($contactData['id']);
        return $contact;
    }
    
}
