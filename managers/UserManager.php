<?php

class UserManager extends AbstractManager
{

    private MediaManager $mm;
    private SpecializationManager $sm;

    public function __construct()
    {
        parent::__construct();
        $this->mm = new MediaManager();
        $this->sm = new SpecializationManager();
    }

    public function findAll() : array
    {
        $query = $this->db->query('SELECT * FROM users ORDER BY id ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $users = [];
    
        foreach($result as $item) {
            // Récupérer l'objet Media à partir de l'ID
            $media = null;
            if ($item["media_id"]) {
                $media = $this->mm->findOne($item["media_id"]);
            }
    
            // Récupérer l'objet Specialization à partir de l'ID
            $specialization = null;
            if ($item["specialization_id"]) {
                $specialization = $this->sm->findOne($item["specialization_id"]);
            }
    
            // Créer un nouvel utilisateur avec les objets Media et Specialization
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
            $users[] = $user;
        }
    
        return $users;
    }
    
    public function findByEmail(string $email) : ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE email=:email');
        $parameters = ["email" => $email];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        if($result) {
            // Récupérer l'objet Media à partir de l'ID
            $media = null;
            if ($result["media_id"]) {
                $media = $this->mm->findOne($result["media_id"]);
            }
    
            // Récupérer l'objet Specialization à partir de l'ID
            $specialization = null;
            if ($result["specialization_id"]) {
                $specialization = $this->sm->findOne($result["specialization_id"]);
            }
    
            $user = new User(
                $result["first_name"],
                $result["last_name"],
                $result["email"],
                $result["password"],
                $result["role"],
                $specialization,
                $media,
                new DateTime($result["created_at"]),
                $result["structure"]
            );
    
            $user->setId($result["id"]);
            return $user;
        }
    
        return null;
    }    
    public function findById(int $id) : ?User
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE id=:id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        if($result) {
            // Récupérer l'objet Media à partir de l'ID
            $media = null;
            if ($result["media_id"]) {
                $media = $this->mm->findOne($result["media_id"]);
            }
    
            // Récupérer l'objet Specialization à partir de l'ID
            $specialization = null;
            if ($result["specialization_id"]) {
                $specialization = $this->sm->findOne($result["specialization_id"]);
            }
    
            $user = new User(
                $result["first_name"],
                $result["last_name"],
                $result["email"],
                $result["password"],
                $result["role"],
                $specialization,
                $media,
                new DateTime($result["created_at"]),
                $result["structure"]
            );
    
            $user->setId($result["id"]);
            return $user;
        }
    
        return null;
    }

    public function searchUsers(string $query, string $role = 'all') : array
    {
        $query = '%' . $query . '%';
        
        // Préparation de la requête SQL
        $sql = 'SELECT * FROM users WHERE (first_name LIKE :query OR last_name LIKE :query OR email LIKE :query OR structure LIKE :query)';
        
        // Ajout du filtre de rôle si ce n'est pas "all"
        if ($role !== 'all') {
            $sql .= ' AND role = :role';
        }
        
        $sql .= ' ORDER BY last_name ASC';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $query, PDO::PARAM_STR);
        
        // Lier le rôle si nécessaire
        if ($role !== 'all') {
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
        }
    
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $users = [];
        
        foreach ($result as $item) {
            $media = null;
            if ($item["media_id"]) {
                $media = $this->mm->findOne($item["media_id"]);
            }
    
            $specialization = null;
            if ($item["specialization_id"]) {
                $specialization = $this->sm->findOne($item["specialization_id"]);
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
            $users[] = $user;
        }
    
        return $users;
    }
    

    public function create(User $user) : void
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
            "specialization_id" => $user->getSpecializationId(),  // Assurez-vous que l'objet Specialization est géré
            "structure" => $user->getStructure(),
            "created_at" => $currentDateTime,
        ];

        $query->execute($parameters);
        $user->setId($this->db->lastInsertId());
    }

        public function deleteUser(int $userId): bool
    {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $stmt->execute(); // Retourne true si la suppression a réussi
    }
}