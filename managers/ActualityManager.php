<?php

class ActualityManager extends AbstractManager
{
    private MediaManager $mm;

    public function __construct()
    {
        parent::__construct();
        $this->mm = new MediaManager();
    }

    public function findAll() : array
    {
        $query = $this->db->prepare('SELECT * FROM actualities ORDER BY date DESC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $actualities = [];

        foreach ($result as $item)
        {
            $date = new DateTime($item["date"]);

            $media = $item['media_id'] ? $this->mm->findOne($item['media_id']) : null;

            $actuality = new Actuality(
                $item["title"],
                $date,
                $item["content"],
                $media
            );

            $actuality->setMedia($media);
            $actuality->setId($item["id"]);

            $actualities[] = $actuality;
        }

        return $actualities;
    }

    public function findLatest() : array
    {
        $query = $this->db->prepare('SELECT * FROM actualities ORDER BY date DESC LIMIT 2');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $actualities = [];

        foreach ($result as $item)
        {
            $date = new DateTime($item["date"]);
            $media = $item['media_id'] ? $this->mm->findOne($item['media_id']) : null;

            $actuality = new Actuality($item["title"], $date, $item["content"], $media);

            $actuality->setMedia($media);
            $actuality->setId($item["id"]);
            
            $actualities[] = $actuality;
        }

        return $actualities;
    }

    public function findOne(int $id) : ?Actuality
    {
        $query = $this->db->prepare('SELECT * FROM actualities WHERE id=:id');
        $parameters = [
            "id" => $id
        ];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result)
        {
            $date = new DateTime($result["date"]);
            $media = $result['media_id'] ? $this->mm->findOne($result['media_id']) : null;

            $actuality = new Actuality($result["title"], $date, $result["content"], $media);

            $actuality->setMedia($media);
            $actuality->setId($result["id"]);
            
            return $actuality;
        }

        return null;
    }
}