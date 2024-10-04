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
    $query = $this->db->prepare('SELECT * FROM events ORDER BY date ASC');
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    $events = [];

    foreach ($result as $item) {
        $date = new DateTime($item["date"]);
        $debut = new DateTime($item["debut"]);
        $end = new DateTime($item["end"]);

        $media = $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null;
        $type = $item["type_id"] ? $this->tm->findOne($item["type_id"]) : null;
        $style1 = $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null;
        $style2 = $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null;

        $event = new Event(
            $item["name"],
            $item["main_description"],
            $item["description"],
            $date,
            $debut,
            $end,
            $item["ticket_price"],
            $item["media_id"],
            $item["type_id"],
            $item["style1_id"],
            $item["style2_id"],
            $item["video_link"],
            $item["ticketing_link"]
        );

        $event->setMedia($media);
        $event->setType($type);
        $event->setStyle1($style1);
        $event->setStyle2($style2);
        $event->setId($item["id"]);
        $events[] = $event;
    }

    return $events;
    }

    public function findOne(int $id): ?Event
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE id = :id');
        $parameters = ["id" => $id];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $date = new DateTime($result["date"]);
            $debut = new DateTime($result["debut"]);
            $end = new DateTime($result["end"]);

            $media = $this->mm->findOne($result["media_id"]);
            $type = $this->tm->findOne($result["type_id"]);
            $style1 = $this->sm->findOne($result["style1_id"]);
            $style2 = $this->sm->findOne($result["style2_id"]);

            $event = new Event(
                $result["name"],
                $result["main_description"],
                $result["description"],
                $date,
                $debut,
                $end,
                $result["ticket_price"],
                $result["media_id"],
                $result["type_id"],
                $result["style1_id"],
                $result["style2_id"],
                $result["video_link"],
                $result["ticketing_link"]
            );

            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
            $event->setId($result["id"]);

            return $event;
        }

        return null;
    }

    public function upcomingEvents(): array
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE date >= CURRENT_DATE ORDER BY date ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $events = [];

        foreach ($result as $item) {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

            $media = $this->mm->findOne($item["media_id"]);
            $type = $this->tm->findOne($item["type_id"]);
            $style1 = $this->sm->findOne($item["style1_id"]);
            $style2 = $this->sm->findOne($item["style2_id"]);

            $event = new Event(
                $item["name"],
                $item["main_description"],
                $item["description"],
                $date,
                $debut,
                $end,
                $item["ticket_price"],
                $item["media_id"],
                $item["type_id"],
                $item["style1_id"],
                $item["style2_id"],
                $item["video_link"],
                $item["ticketing_link"]
            );

            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        return $events;
    }

    public function findLatest(): array
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE date >= CURRENT_DATE ORDER BY date ASC LIMIT 9');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $events = [];
        $numEvents = count($result);

        foreach ($result as $item) {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

            // Vérification des IDs avant d'appeler findOne
            $media = $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null;
            $type = $item["type_id"] ? $this->tm->findOne($item["type_id"]) : null;
            $style1 = $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null;
            $style2 = $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null;

            $event = new Event(
                $item["name"],
                $item["main_description"],
                $item["description"],
                $date,
                $debut,
                $end,
                $item["ticket_price"],
                $item["media_id"],
                $item["type_id"],
                $item["style1_id"],
                $item["style2_id"],
                $item["video_link"],
                $item["ticketing_link"]
            );

            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        if ($numEvents < 9) {
            $remainingCount = 9 - $numEvents;

            $query = $this->db->prepare('
                SELECT * FROM events
                WHERE date < CURRENT_DATE
                ORDER BY date ASC 
                LIMIT :limit
            ');
            $query->bindParam(':limit', $remainingCount, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as $item) {
                $date = new DateTime($item["date"]);
                $debut = new DateTime($item["debut"]);
                $end = new DateTime($item["end"]);

                // Vérification des IDs avant d'appeler findOne
                $media = $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null;
                $type = $item["type_id"] ? $this->tm->findOne($item["type_id"]) : null;
                $style1 = $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null;
                $style2 = $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null;

                $event = new Event(
                    $item["name"],
                    $item["main_description"],
                    $item["description"],
                    $date,
                    $debut,
                    $end,
                    $item["ticket_price"],
                    $item["media_id"],
                    $item["type_id"],
                    $item["style1_id"],
                    $item["style2_id"],
                    $item["video_link"],
                    $item["ticketing_link"]
                );

                $event->setMedia($media);
                $event->setType($type);
                $event->setStyle1($style1);
                $event->setStyle2($style2);
                $event->setId($item["id"]);
                $events[] = $event;
            }
        }

    return $events;
    }

    public function searchEvents(string $query): array
    {
        $query = '%' . $query . '%';
        $sql = 'SELECT * FROM events WHERE name LIKE :query ORDER BY date ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':query', $query, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        
        foreach ($result as $item) {
            // Réutilise ton code pour instancier des objets Event à partir des résultats
            $date = new DateTime($item["date"]);
            $media = $this->mm->findOne($item["media_id"]);
            $type = $this->tm->findOne($item["type_id"]);
            $style1 = $this->sm->findOne($item["style1_id"]);
            $style2 = $this->sm->findOne($item["style2_id"]);

            $event = new Event(
                $item["name"],
                $item["main_description"],
                $item["description"],
                $date,
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
            
            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        return $events;
    }

    public function findByType(string $typeId): array
    {
        // Prépare la requête pour sélectionner les événements par type
        $query = $this->db->prepare('SELECT * FROM events WHERE type_id = :typeId ORDER BY date ASC');
        $query->bindParam(':typeId', $typeId, PDO::PARAM_STR); // Assurez-vous que le type d'ID est correct
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $events = [];

        foreach ($result as $item) {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

            // Vérification des IDs avant d'appeler findOne
            $media = $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null;
            $type = $this->tm->findOne($item["type_id"]);
            $style1 = $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null;
            $style2 = $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null;

            $event = new Event(
                $item["name"],
                $item["main_description"],
                $item["description"],
                $date,
                $debut,
                $end,
                $item["ticket_price"],
                $item["media_id"],
                $item["type_id"],
                $item["style1_id"],
                $item["style2_id"],
                $item["video_link"],
                $item["ticketing_link"]
            );

            $event->setMedia($media);
            $event->setType($type);
            $event->setStyle1($style1);
            $event->setStyle2($style2);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        return $events;
    }

    public function findAllEventsArray(): array
    {
    $query = $this->db->prepare('SELECT * FROM events ORDER BY date ASC');
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    $events = [];

    foreach ($result as $item) {
        $date = new DateTime($item["date"]);
        $debut = new DateTime($item["debut"]);
        $end = new DateTime($item["end"]);

        $media = $item["media_id"] ? $this->mm->findOne($item["media_id"]) : null;
        $type = $item["type_id"] ? $this->tm->findOne($item["type_id"]) : null;
        $style1 = $item["style1_id"] ? $this->sm->findOne($item["style1_id"]) : null;
        $style2 = $item["style2_id"] ? $this->sm->findOne($item["style2_id"]) : null;

        $event = [
            'id' => $item["id"],
            'name' => $item["name"],
            'main_description' => $item["main_description"],
            'description' => $item["description"],
            'date' => $date,
            'debut' => $debut,
            'end' => $end,
            'ticket_price' => $item["ticket_price"],
            'media' => $media,
            'type' => $type,
            'style1' => $style1,
            'style2' => $style2,
            'video_link' => $item["video_link"],
            'ticketing_link' => $item["ticketing_link"]
        ];

        $events[] = $event;
    }

    return $events;
    }

}
