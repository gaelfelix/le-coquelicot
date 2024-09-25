<?php

class MediaManager extends AbstractManager
{
    public function findOne(int $id): ?Media
    {
        $query = $this->db->prepare('SELECT * FROM medias WHERE id = :id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return new Media($result["url"], $result["alt"]);
        }

        return null;
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('SELECT * FROM medias ORDER BY name ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $medias = [];

        foreach ($result as $item) {
            $media = new Media($item["url"], $item["alt"]);
            $media->setId($item["id"]);
            $medias[] = $media;
        }

        return $medias;
    }
}
