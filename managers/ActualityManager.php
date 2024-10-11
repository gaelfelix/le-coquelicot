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
                $media ? $media->getId() : null
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

            $actuality = new Actuality(
                $item["title"],
                $date,
                $item["content"],
                $media ? $media->getId() : null
            );

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

            $actuality = new Actuality($result["title"],
            $date,
            $result["content"],
            $media ? $media->getId() : null
        );

            $actuality->setMedia($media);
            $actuality->setId($result["id"]);
            
            return $actuality;
        }

        return null;
    }

    public function searchActualities(string $query): array
    {
        $query = '%' . $query . '%';
        $sql = 'SELECT * FROM actus WHERE name LIKE :query ORDER BY date ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $query, PDO::PARAM_STR);

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->$result;
    }

    public function create (array $actualityData) : ?Actuality
    {
        $this->db->beginTransaction();

        try {
            if (isset($actualityData['media_tmp_path']) && isset($actualityData['media_path'])) {
                if (!move_uploaded_file($actualityData['media_tmp_path'], $actualityData['media_path'])) {
                    throw new Exception("Erreur lors de l'upload du fichier.");
                }
                $media = new Media($actualityData['media_path'], $actualityData['alt-img']);
                $this->mm->create($media);
            } else {
                throw new Exception("Informations de média manquantes.");
            }

            $actuality = new Actuality(
                $actualityData['title'],
                new DateTime($actualityData['date']),
                $actualityData['content'],
                $media->getId()
            );

            $query = $this->db->prepare('
                INSERT INTO actualities (title, date, content, media_id)
                VALUES (:title, :date, :content, :media_id)'
            );

            $parameters = [
                "title" => $actuality->getTitle(),
                "date" => $actuality->getDate()->format('Y-m-d'),
                "content" => $actuality->getContent(),
                "media_id" => $media->getId()
            ];
            
            $query->execute($parameters);

            $actuality->setId($this->db->lastInsertId());
            $actuality->setMedia($media);

            $this->db->commit();
            return $actuality;

        } catch (Exception $e) {
            $this->db->rollBack();
            
            if (isset($actualityData['media_path']) && file_exists($actualityData['media_path'])) {
                unlink($actualityData['media_path']);
            }
            error_log("Erreur lors de la création de l'événement : " . $e->getMessage());
            throw $e;
        }
    }
}