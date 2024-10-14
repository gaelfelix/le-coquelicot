<?php

class StyleManager extends AbstractManager
{
    public function findOne(int $id): ?Style
    {
        $query = $this->db->prepare('SELECT * FROM styles WHERE id = :id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);
    
        if ($result) {
            $style = new Style($result["name"]);
            $style->setId($result["id"]);
            return $style;
        }
    
        return null;
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('SELECT * FROM styles ORDER BY name ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $styles = [];

        foreach ($result as $item) {
            $style = new Style($item["name"]);
            $style->setId($item["id"]);
            $styles[] = $style;
        }

        return $styles;
    }

    public function create(Style $style): void
    {
        $query = $this->db->prepare("INSERT INTO styles (name) VALUES (:name)");
        $query->execute(['name' => $style->getName()]);
        $style->setId($this->db->lastInsertId());
    }

    public function delete(int $id): bool
    {
        $query = $this->db->prepare("DELETE FROM styles WHERE id = :id");
        $result = $query->execute(['id' => $id]);
        return $result;
    }
}
