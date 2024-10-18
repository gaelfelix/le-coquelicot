<?php

class UserManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct();
    }

    private function createUsersFromArrays(array $result): array
    {
        return array_map([$this, 'createUserFromArray'], $result);
    }

    private function createUserFromArray(array $item): User
    {
        $media = null;
        if ($item["media_id"]) {
            $media = new Media($item["media_url"], $item["media_alt"]);
            $media->setId($item["media_id"]);
        }

        $specialization = null;
        if ($item["specialization_id"]) {
            $specialization = new Specialization($item["specialization_name"], $item["specialization_role"]);
            $specialization->setId($item["specialization_id"]);
        }

        $user = new User(
            $item["first_name"],
            $item["last_name"],
            $item["email"],
            $item["password"],
            $item["role"],
            $specialization,
            $media,
            new DateTime($item["created_at"]),
            $item["structure"]
        );

        $user->setId($item["id"]);
        return $user;
    }

    public function findAll(): array
    {
        $sql = 'SELECT u.*, m.url AS media_url, m.alt AS media_alt, 
                       s.name AS specialization_name, s.role AS specialization_role
                FROM users u
                LEFT JOIN medias m ON u.media_id = m.id
                LEFT JOIN specializations s ON u.specialization_id = s.id
                ORDER BY u.id ASC';
        
        $query = $this->db->query($sql);
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->createUsersFromArrays($result);
    }
    
    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT u.*, m.url AS media_url, m.alt AS media_alt, 
                       s.name AS specialization_name, s.role AS specialization_role
                FROM users u
                LEFT JOIN medias m ON u.media_id = m.id
                LEFT JOIN specializations s ON u.specialization_id = s.id
                WHERE u.email = :email';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $this->createUserFromArray($result) : null;
    }
    
    public function findById(int $id): ?User
    {
        $sql = 'SELECT u.*, m.url AS media_url, m.alt AS media_alt, 
                       s.name AS specialization_name, s.role AS specialization_role
                FROM users u
                LEFT JOIN medias m ON u.media_id = m.id
                LEFT JOIN specializations s ON u.specialization_id = s.id
                WHERE u.id = :id';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $this->createUserFromArray($result) : null;
    }

    public function getUniqueRoles(): array
    {
        $query = $this->db->query('SELECT DISTINCT role FROM users ORDER BY role ASC');
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public function searchUsers(string $query, string $role = 'all'): array
    {
        $searchQuery = '%' . $query . '%';
        $sql = 'SELECT u.*, m.url AS media_url, m.alt AS media_alt, 
                       s.name AS specialization_name, s.role AS specialization_role
                FROM users u
                LEFT JOIN medias m ON u.media_id = m.id
                LEFT JOIN specializations s ON u.specialization_id = s.id
                WHERE (u.first_name LIKE :query OR u.last_name LIKE :query 
                       OR u.email LIKE :query OR u.structure LIKE :query)';
        
        if ($role !== 'all') {
            $sql .= ' AND u.role = :role';
        }
        
        $sql .= ' ORDER BY u.last_name ASC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        
        if ($role !== 'all') {
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        }
    
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $this->createUsersFromArrays($result);
    }

    public function create(User $user): void
    {
        $currentDateTime = date('Y-m-d H:i:s');

        $query = $this->db->prepare(
            'INSERT INTO users (id, first_name, last_name, email, password, role, specialization_id, structure, created_at) 
            VALUES (NULL, :first_name, :last_name, :email, :password, :role, :specialization_id, :structure, :created_at)'
        );

        $parameters = [
            "first_name" => $user->getFirstName(),
            "last_name" => $user->getLastName(),
            "password" => $user->getPassword(),
            "email" => $user->getEmail(),
            "role" => $user->getRole(),
            "specialization_id" => $user->getSpecializationId(),
            "structure" => $user->getStructure(),
            "created_at" => $currentDateTime,
        ];

        $query->execute($parameters);
        $user->setId($this->db->lastInsertId());
    }

    public function update(User $user): void
    {
        $query = $this->db->prepare(
            'UPDATE users SET first_name = :first_name, last_name = :last_name, 
            email = :email, password = :password, role = :role, 
            specialization_id = :specialization_id, structure = :structure 
            WHERE id = :id'
        );

        $parameters = [
            "id" => $user->getId(),
            "first_name" => $user->getFirstName(),
            "last_name" => $user->getLastName(),
            "email" => $user->getEmail(),
            "password" => $user->getPassword(),
            "role" => $user->getRole(),
            "specialization_id" => $user->getSpecializationId(),
            "structure" => $user->getStructure(),
        ];

        $query->execute($parameters);
    }

    public function deleteUser(int $userId): bool
    {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}