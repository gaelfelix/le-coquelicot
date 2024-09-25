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
            return new Type($result["name"]);
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
}
