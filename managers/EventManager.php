<?php

class EventManager extends AbstractManager
{
    private MediaManager $mm;
    private TypeManager $tm;
    private StyleManager $sm;

    public function __construct()
    {
        parent::__construct();
        $this->mm = new MediaManager();
        $this->tm = new TypeManager();
        $this->sm = new StyleManager();
    }

    public function findAll(): array
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            ORDER BY e.date ASC
        ');
        $query->execute();
        return $this->createEventObjects($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findOne(int $id): ?Event
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.id = :id
        ');
        $query->execute(['id' => $id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        return $result ? $this->createEventObject($result) : null;
    }

    public function upcomingEvents(): array
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.date >= CURRENT_DATE
            ORDER BY e.date ASC
        ');
        $query->execute();
        return $this->createEventObjects($query->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findLatest(): array
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.date >= CURRENT_DATE
            ORDER BY e.date ASC
            LIMIT 9
        ');
        $query->execute();
        $events = $this->createEventObjects($query->fetchAll(PDO::FETCH_ASSOC));

        if (count($events) < 9) {
            $remainingCount = 9 - count($events);
            $query = $this->db->prepare('
                SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                       t.name AS type_name,
                       s1.name AS style1_name, s2.name AS style2_name
                FROM events e
                LEFT JOIN medias m ON e.media_id = m.id
                LEFT JOIN types t ON e.type_id = t.id
                LEFT JOIN styles s1 ON e.style1_id = s1.id
                LEFT JOIN styles s2 ON e.style2_id = s2.id
                WHERE e.date < CURRENT_DATE
                ORDER BY e.date DESC 
                LIMIT :limit
            ');
            $query->bindParam(':limit', $remainingCount, PDO::PARAM_INT);
            $query->execute();
            $events = array_merge($events, $this->createEventObjects($query->fetchAll(PDO::FETCH_ASSOC)));
        }

        return $events;
    }

    public function searchEvents(string $query, string $type = 'all'): array
    {
        $searchQuery = '%' . $query . '%';
        $sql = '
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.name LIKE :query
        ';
        if ($type !== 'all') {
            $sql .= ' AND e.type_id = :type';
        }
        $sql .= ' ORDER BY e.date ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $searchQuery, PDO::PARAM_STR);
        if ($type !== 'all') {
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        }
        $stmt->execute();
        return $this->createEventObjects($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findByType(int $typeId): array
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.type_id = :typeId
            ORDER BY e.date ASC
        ');
        $query->bindParam(':typeId', $typeId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $this->createEventObjects($result);
    }

    public function findByStyle(int $styleId): array
    {
        $query = $this->db->prepare('
            SELECT e.*, m.url AS media_url, m.alt AS media_alt,
                   t.name AS type_name,
                   s1.name AS style1_name, s2.name AS style2_name
            FROM events e
            LEFT JOIN medias m ON e.media_id = m.id
            LEFT JOIN types t ON e.type_id = t.id
            LEFT JOIN styles s1 ON e.style1_id = s1.id
            LEFT JOIN styles s2 ON e.style2_id = s2.id
            WHERE e.style1_id = :styleId OR e.style2_id = :styleId
            ORDER BY e.date ASC
        ');
        $query->bindParam(':styleId', $styleId, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $this->createEventObjects($result);
    }

    public function findAllEventsArray(): array
    {
        $query = $this->db->prepare('SELECT * FROM events ORDER BY date ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $this->createEventArrays($result);
    }

    public function create(array $eventData): ?Event
    {
        $this->db->beginTransaction();
    
        try {
            // Vérifier l'existence des styles et du type
            $type = $this->tm->findOne($eventData['type_id']);
            $style1 = $this->sm->findOne($eventData['style1_id']);
            $style2 = $this->sm->findOne($eventData['style2_id']);
    
            if (!$type || !$style1 || !$style2) {
                throw new Exception("Type ou style non trouvé.");
            }
    
            // Créer le média
            if (isset($eventData['media_tmp_path']) && isset($eventData['media_path'])) {
                if (!move_uploaded_file($eventData['media_tmp_path'], $eventData['media_path'])) {
                    throw new Exception("Erreur lors de l'upload du fichier.");
                }
                $media = new Media($eventData['media_path'], $eventData['alt-img']);
                $this->mm->create($media);
            } else {
                throw new Exception("Informations de média manquantes.");
            }
    
            $event = new Event(
                $eventData['name'],
                $eventData['main_description'],
                $eventData['description'],
                new DateTime($eventData['date']),
                new DateTime($eventData['debut']),
                new DateTime($eventData['end']),
                (float)$eventData['ticket_price'],
                $media->getId(),
                $type->getId(),
                $style1->getId(),
                $style2->getId(),
                $eventData['video_link'] ?? null,
                $eventData['ticketing_link']
            );
    
            $query = $this->db->prepare(
                'INSERT INTO events (name, main_description, description, date, debut, end, ticket_price, media_id, type_id, style1_id, style2_id, video_link, ticketing_link)
                VALUES (:name, :main_description, :description, :date, :debut, :end, :ticket_price, :media_id, :type_id, :style1_id, :style2_id, :video_link, :ticketing_link)'
            );
        
            $parameters = [
                "name" => $event->getName(),
                "main_description" => $event->getMainDescription(),
                "description" => $event->getDescription(),
                "date" => $event->getDate()->format('Y-m-d'),
                "debut" => $event->getDebut()->format('H:i:s'),
                "end" => $event->getEnd()->format('H:i:s'),
                "ticket_price" => $event->getTicketPrice(),
                "media_id" => $media->getId(),
                "type_id" => $type->getId(),
                "style1_id" => $style1->getId(),
                "style2_id" => $style2->getId(),
                "video_link" => $event->getVideoLink(),
                "ticketing_link" => $event->getTicketingLink(),
            ];
        
            $query->execute($parameters);

            $event->setId($this->db->lastInsertId());
            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
    
            $this->db->commit();
            return $event;

        } catch (Exception $e) {
            $this->db->rollBack();

            if (isset($eventData['media_path']) && file_exists($eventData['media_path'])) {
                unlink($eventData['media_path']);
            }
            error_log("Erreur lors de la création de l'événement : " . $e->getMessage());
            throw $e;
        }
    }

    public function update(array $eventData): void
    {
        $this->db->beginTransaction();
    
        try {
            $event = $this->findOne($eventData['id']);
            if (!$event) {
                throw new Exception("Événement non trouvé.");
            }
    
            $fields = [
                'name', 'main_description', 'description', 'date', 'debut', 'end', 
                'ticket_price', 'type_id', 'style1_id', 'style2_id', 'video_link', 
                'ticketing_link', 'media_id'
            ];
    
            $updateFields = [];
            $parameters = ['id' => $eventData['id']];
    
            foreach ($fields as $field) {
                if (array_key_exists($field, $eventData)) {
                    $updateFields[] = "$field = :$field";
                    $parameters[$field] = $eventData[$field] !== '' ? $eventData[$field] : null;
                }
            }
    
            // Gérer spécifiquement media_id
            if (!isset($eventData['media_id'])) {
                $updateFields[] = "media_id = :media_id";
                $parameters['media_id'] = null;
            }
    
            if (empty($updateFields)) {
                // Aucun champ à mettre à jour
                return;
            }
    
            $query = $this->db->prepare(
                'UPDATE events SET ' . implode(', ', $updateFields) . ' WHERE id = :id'
            );
    
            $result = $query->execute($parameters);
            if (!$result) {
                throw new Exception("Échec de la mise à jour de l'événement.");
            }
    
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la mise à jour de l'événement : " . $e->getMessage());
            throw $e;
        }
    }


    public function delete(int $eventId): bool
    {
        $this->db->beginTransaction();
    
        try {
            $event = $this->findOne($eventId);
            if (!$event) {
                throw new Exception("Événement non trouvé.");
            }
    
            if ($event->getMedia()) {
                $media = $event->getMedia();
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
    
            $query = $this->db->prepare('DELETE FROM events WHERE id = :id');
            $result = $query->execute(['id' => $eventId]);
    
            if (!$result) {
                throw new Exception("Impossible de supprimer l'événement de la base de données.");
            }
    
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la suppression de l'événement : " . $e->getMessage());
            throw $e;
        }
    }

    private function createEventObjects(array $results): array
    {
        $events = [];
        foreach ($results as $item) {
            $events[] = $this->createEventObject($item);
        }
        return $events;
    }

    private function createEventObject(array $item): Event
    {
        $event = new Event(
            $item["name"],
            $item["main_description"],
            $item["description"],
            new DateTime($item["date"]),
            new DateTime($item["debut"]),
            new DateTime($item["end"]),
            $item["ticket_price"],
            $item["media_id"],
            $item["type_id"],
            $item["style1_id"],
            $item["style2_id"],
            $item["video_link"],
            $item["ticketing_link"]
        );

        $event->setId($item["id"]);

        if ($item["media_id"]) {
            $media = new Media($item["media_url"], $item["media_alt"]);
            $media->setId($item["media_id"]);
            $event->setMedia($media);
        }

        if ($item["type_id"]) {
            $type = new Type($item["type_name"]);
            $type->setId($item["type_id"]);
            $event->setType($type);
        }

        if ($item["style1_id"]) {
            $style1 = new Style($item["style1_name"]);
            $style1->setId($item["style1_id"]);
            $event->setStyle1($style1);
        }

        if ($item["style2_id"]) {
            $style2 = new Style($item["style2_name"]);
            $style2->setId($item["style2_id"]);
            $event->setStyle2($style2);
        }

        return $event;
    }

    private function createEventArrays(array $results): array
    {
        $events = [];
        foreach ($results as $item) {
            $events[] = $this->createEventArray($item);
        }
        return $events;
    }

    private function createEventArray(array $item): array
    {
        return [
            'id' => $item["id"],
            'name' => $item["name"],
            'main_description' => $item["main_description"],
            'description' => $item["description"],
            'date' => new DateTime($item["date"]),
            'debut' => new DateTime($item["debut"]),
            'end' => new DateTime($item["end"]),
            'ticket_price' => $item["ticket_price"],
            'media' => $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null,
            'type' => $item["type_id"] ? $this->tm->findOne($item["type_id"]) : null,
            'style1' => $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null,
            'style2' => $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null,
            'video_link' => $item["video_link"],
            'ticketing_link' => $item["ticketing_link"]
        ];
    }
}