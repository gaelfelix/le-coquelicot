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
            return new Style($result["name"]);
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
}
