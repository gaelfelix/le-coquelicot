<?php

class ActualityManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function findAll() : array
    {
        $query = $this->db->prepare('SELECT * FROM actualities ORDER BY date ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $actualities = [];

        foreach($result as $item)
        {
            $date = new DateTime($item["date"]);

            $actuality = new Actuality($item["title"], $date, $item["content"], $item["media_id"]);
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

        foreach($result as $item)
        {
            $date = new DateTime($item["date"]);

            $actuality = new Actuality($item["title"], $date, $item["content"], $item["media_id"]);
            $actuality->setId($item["id"]);
            $actualities[] = $actuality;
        }

        return $actualities;
    }

    public function findOne(int $id) : ? Actuality
    {
        $query = $this->db->prepare('SELECT * FROM actualities WHERE id=:id');
        $parameters = [
            "id" => $id
        ];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result)
        {
            $date = new DateTime($result["date"]);

            $actuality = new Actuality($result["title"], $date, $result["content"], $result["media_id"]);
            $actuality->setId($result["id"]);

            return $actuality;
        }

        return null;
    }

}

