<?php

class EventManager extends AbstractManager
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function findAll() : array
    {
        $query = $this->db->prepare('SELECT * FROM events ORDER BY date ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $events = [];

        foreach($result as $item)
        {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

            $event = new Event($item["name"], $item["main_description"], $item["description"], $date, $debut, $end, $item["ticket_price"], $item["media_id"], $item["type_id"], $item["style1_id"], $item["style2_id"], $item["video_link"]);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        return $events;
    }

    public function upcomingEvents() : array
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE date >= CURRENT_DATE ORDER BY date ASC');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        $events = [];

        foreach($result as $item)
        {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

            $event = new Event($item["name"], $item["main_description"], $item["description"], $date, $debut, $end, $item["ticket_price"], $item["media_id"], $item["type_id"], $item["style1_id"], $item["style2_id"], $item["video_link"]);
            $event->setId($item["id"]);
            $events[] = $event;
        }

        return $events;
    }

    public function findLatest() : array
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE date >= CURRENT_DATE ORDER BY date ASC LIMIT 9');
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        $events = [];
        $numEvents = count($result);
        
        foreach($result as $item)
        {
            $date = new DateTime($item["date"]);
            $debut = new DateTime($item["debut"]);
            $end = new DateTime($item["end"]);

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
                $item["video_link"] 
            );
            $event->setId($item["id"]);
            $events[] = $event;
        }

        if ($numEvents < 9) {
            $remainingCount = 9 - $numEvents;
            
            $query = $this->db->prepare('
                SELECT * FROM events 
                WHERE date < CURRENT_DATE 
                ORDER BY date DESC 
                LIMIT :limit
            ');
            $query->bindParam(':limit', $remainingCount, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($result as $item)
            {
                $date = new DateTime($item["date"]);
                $debut = new DateTime($item["debut"]);
                $end = new DateTime($item["end"]);
    
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
                    $item["video_link"]
                );
                $event->setId($item["id"]);
                $events[] = $event;
            }
        }

        return $events;
    }

    public function findOne(int $id) : ? Event
    {
        $query = $this->db->prepare('SELECT * FROM events WHERE id=:id');
        $parameters = [
            "id" => $id
        ];
        $query->execute($parameters);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result)
        {
            $date = new DateTime($result["date"]);
            $debut = new DateTime($result["debut"]);
            $end = new DateTime($result["end"]);

            $event = new Event($result["name"], $result["main_description"], $result["description"], $date, $debut, $end, $result["ticket_price"], $result["media_id"], $result["type_id"], $result["style1_id"], $result["style2_id"], $result["video_link"]);
            $event->setId($result["id"]);

            return $event;
        }

        return null;
    }

}

