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

    public function uploadMedia(string $tmpFilePath, string $alt): ?Media
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $fileInfo = pathinfo($tmpFilePath);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException("Format de fichier non supporté.");
        }

        $newFileName = uniqid() . '.' . $extension;
        $uploadPath = 'path/to/your/upload/directory/' . $newFileName;

        if (move_uploaded_file($tmpFilePath, $uploadPath)) {
            $media = new Media($uploadPath, $alt);
            $this->create($media);
            return $media;
        }

        return null;
    }

    public function create(Media $media): void
    {
        $query = $this->db->prepare("INSERT INTO medias (url, alt) VALUES (:url, :alt)");
        $query->execute([
            'url' => $media->getUrl(),
            'alt' => $media->getAlt()
        ]);
        $media->setId($this->db->lastInsertId());
    }

    public function delete(?int $id): bool
    {
        if ($id === null) {
            return false;
        }
    
        try {
            $media = $this->findOne($id);
            if ($media) {
                $filePath = $media->getUrl();
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
    
                $query = $this->db->prepare("DELETE FROM medias WHERE id = :id");
                return $query->execute(['id' => $id]);
            }
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression du média : " . $e->getMessage());
            return false;
        }
    }

    public function update(Media $media): void
    {
        $query = $this->db->prepare("UPDATE medias SET url = :url, alt = :alt WHERE id = :id");
        $query->execute([
            'id' => $media->getId(),
            'url' => $media->getUrl(),
            'alt' => $media->getAlt()
        ]);
    }

}
