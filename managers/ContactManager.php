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
        try {
            $query = $this->db->prepare("
                INSERT INTO contact (first_name, last_name, email, phone, subject, message, `read`, created_at)
                VALUES (:firstName, :lastName, :email, :phone, :subject, :message, :read, :createdAt)
            ");
    
            $phoneValue = $contact->getPhone() ? substr(trim($contact->getPhone()), 0, 10) : null;
    
            $params = [
                ':firstName' => $contact->getFirstName(),
                ':lastName' => $contact->getLastName(),
                ':email' => $contact->getEmail(),
                ':phone' => $phoneValue,
                ':subject' => $contact->getSubject(),
                ':message' => $contact->getMessage(),
                ':read' => $contact->isRead() ? 1 : 0,
                ':createdAt' => $contact->getCreatedAt()->format('Y-m-d H:i:s')
            ];
    
            foreach ($params as $key => $value) {
                $query->bindValue($key, $value);
            }
    
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans addContact: " . $e->getMessage());
            return false;
        }
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

    public function markAsUnread(int $id): bool
    {
        try {
            $query = $this->db->prepare("UPDATE contact SET `read` = 0 WHERE id = :id");
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            return $query->execute();
        } catch (PDOException $e) {
            error_log("Erreur PDO dans markAsUnread: " . $e->getMessage());
            return false;
        }
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
