<?php

class ActualityManager extends AbstractManager
{
    private MediaManager $mm;

    public function __construct()
    {
        parent::__construct();
        $this->mm = new MediaManager();
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('
            SELECT a.*, m.url AS media_url, m.alt AS media_alt
            FROM actualities a
            LEFT JOIN medias m ON a.media_id = m.id
            ORDER BY a.date DESC
        ');
        $query->execute();
        return $this->createActualitiesFromArrays($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findLatest(): array
    {
        $query = $this->db->prepare('
            SELECT a.*, m.url AS media_url, m.alt AS media_alt
            FROM actualities a
            LEFT JOIN medias m ON a.media_id = m.id
            ORDER BY a.date DESC
            LIMIT 2
        ');
        $query->execute();
        return $this->createActualitiesFromArrays($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findOne(int $id): ?Actuality
    {
        $query = $this->db->prepare('
            SELECT a.*, m.url AS media_url, m.alt AS media_alt
            FROM actualities a
            LEFT JOIN medias m ON a.media_id = m.id
            WHERE a.id = :id
        ');
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->createActualityFromArray($result) : null;
    }

    public function searchActualities(string $query): array
    {
        $searchQuery = '%' . $query . '%';
        $sql = '
            SELECT a.*, m.url AS media_url, m.alt AS media_alt
            FROM actualities a
            LEFT JOIN medias m ON a.media_id = m.id
            WHERE a.title LIKE :query
            ORDER BY a.date DESC
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        $stmt->execute();

        return $this->createActualitiesFromArrays($stmt->fetchAll(PDO::FETCH_ASSOC));
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

    public function update(array $actualityData): void
    {
        $this->db->beginTransaction();
    
        try {
            $actuality = $this->findOne($actualityData['id']);
            if (!$actuality) {
                throw new Exception("Actualité non trouvée.");
            }
    
            $fields = [
                'title', 'date', 'content', 'media_id'
            ];
    
            $updateFields = [];
            $parameters = ['id' => $actualityData['id']];
    
            foreach ($fields as $field) {
                if (array_key_exists($field, $actualityData)) {
                    $updateFields[] = "$field = :$field";
                    $parameters[$field] = $actualityData[$field] !== '' ? $actualityData[$field] : null;
                }
            }


            // Si media_id n'est pas fourni, on garde l'ancien
            if (!isset($actualityData['media_id'])) {
                $updateFields[] = "media_id = :media_id";
                $parameters['media_id'] = null;
            }
    
            if (empty($updateFields)) {
                // Aucun champ à mettre à jour
                return;
            }
    
            $query = $this->db->prepare(
                'UPDATE actualities SET ' . implode(', ', $updateFields) . ' WHERE id = :id'
            );
    
            $result = $query->execute($parameters);
            if (!$result) {
                throw new Exception("Échec de la mise à jour de l'actualité.");
            }
    
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la mise à jour de l'actualité : " . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $actualityId): bool
    {
        $this->db->beginTransaction();

        try {
            $actuality = $this->findOne($actualityId);
            if (!$actuality) {
                throw new Exception("Actualité non trouvée.");
            }

            if ($actuality->getMedia()) {
                $media = $actuality->getMedia();
                $filePath = $media->getUrl();
                if (file_exists($filePath)) {
                    if (!unlink($filePath)) {
                        throw new Exception("Impossible de supprimer le fichier image.");
                    }
                }
                if (!$this->mm->delete($media->getId())) {
                    throw new Exception("Impossible de supprimer l'entrée média de la base de données.");
                }
            }

            $query = $this->db->prepare('DELETE FROM actualities WHERE id = :id');
            $result = $query->execute(['id' => $actualityId]);

            if (!$result) {
                throw new Exception("Impossible de supprimer l'actualité de la base de données.");
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la suppression de l'actualité : " . $e->getMessage());
            throw $e;
        }
    }

    private function createActualitiesFromArrays(array $result): array
    {
        $actualities = [];
        foreach ($result as $item) {
            $actualities[] = $this->createActualityFromArray($item);
        }
        return $actualities;
    }

    private function createActualityFromArray(array $item): Actuality
    {
        $date = new DateTime($item["date"]);
        $media = null;
        if ($item['media_id']) {
            $media = new Media($item['media_url'], $item['media_alt']);
            $media->setId($item['media_id']);
        }

        $actuality = new Actuality(
            $item["title"],
            $date,
            $item["content"],
            $item["media_id"]
        );

        $actuality->setId($item["id"]);
        $actuality->setMedia($media);

        return $actuality;
    }
}