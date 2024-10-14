<?php

class TypeManager extends AbstractManager
{
    public function findOne(int $id): ?Type
    {
        $query = $this->db->prepare('SELECT * FROM types WHERE id = :id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $type = new Type($result["name"]);
            $type->setId($result["id"]);
            return $type;
        }
    
        return null;
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('SELECT * FROM types ORDER BY name ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $types = [];

        foreach ($result as $item) {
            $type = new Type($item["name"]);
            $type->setId($item["id"]);
            $types[] = $type;
        }

        return $types;
    }

    public function create(Type $type): void
    {
        $query = $this->db->prepare("INSERT INTO types (name) VALUES (:name)");
        $query->execute(['name' => $type->getName()]);
        $type->setId($this->db->lastInsertId());
    }


    public function delete(int $id): bool
    {
        $query = $this->db->prepare("DELETE FROM types WHERE id = :id");
        $result = $query->execute(['id' => $id]);
        return $result;
    }
}
