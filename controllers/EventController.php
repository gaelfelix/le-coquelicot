<?php

class EventController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function events() : void
    {
        $em = new EventManager();
        $tm = new TypeManager();
    
        $events = $em->findAllEventsArray();
        $types = $tm->findAll();
    
        $translatedEvents = [];
    
        foreach ($events as $event) {
            $dateValues = $this->translateDate($event['date']);
            
            $translatedEvents[] = [
                "event" => $event,
                'shortDay' => $dateValues['shortDay'],
                'number' => $dateValues['number'],
                'shortMonth' => $dateValues['shortMonth'],
            ];

        }

        $scripts = $this->addScripts([
            'assets/js/ajaxEventsSearch.js',
        ]);
    
        $this->render("programmation.html.twig", [
            "events" => $translatedEvents,
            "types" => $types,
        ], $scripts);    
    }

    public function event(string $eventId) : void
    {
        $em = new EventManager();

        $event = $em->findOne(intval($eventId));

        $scripts = $this->addScripts([
            'assets/js/eventYoutubeSource.js',
        ]);

        if ($event !== null)
        {
            $dateValues = $this->translateDate($event->getDate());

            $videoLink = $event->getVideoLink();
            $youtubeId = $this->extractYoutubeId($videoLink);

            $this->render("evenement.html.twig", [
                "event" => $event,
                "integralDay" => $dateValues['integralDay'],
                "shortDay" => $dateValues['shortDay'],
                "number" => $dateValues['number'],
                "shortMonth" => $dateValues['shortMonth'],
                "youtubeId" => $youtubeId,
            ], $scripts);
        }
        else
        {
            $this->redirect("index.php?route=programmation");
        }
    }

    public function search(): void
    {
        if ($this->isAjaxRequest()) {
            $query = $_GET['q'] ?? '';
            $em = new EventManager();
            $events = $em->searchEvents($query);

            // Si des événements sont trouvés, formate les données en JSON
            if ($events) {

                $eventsData = array_map(function($event) {

                    $dateValues = $this->translateDate($event->getDate());

                    return [
                        'id' => $event->getId(),
                        'name' => $event->getName(),
                        'shortDay' => $dateValues['shortDay'],
                        'number' => $dateValues['number'],
                        'shortMonth' => $dateValues['shortMonth'],
                        'media' => [
                            'url' => $event->getMedia()->getUrl(),
                            'alt' => $event->getMedia()->getAlt()
                        ],
                        'type' => ['name' => $event->getType()->getName()],
                        'style1' => ['name' => $event->getStyle1()->getName()],
                        'style2' => ['name' => $event->getStyle2()->getName()],
                    ];
                }, $events);


                // Envoyer la réponse JSON
                header('Content-Type: application/json');
                echo json_encode($eventsData);
            } else {
                // Retourner un JSON vide si aucun événement trouvé
                echo json_encode([]);
            }
            exit;
        }

        // Si ce n'est pas une requête AJAX, redirection vers une autre page
        $this->redirect('index.php');
    }

    public function filterEvents(string $type): void
    {
        try {
            error_log("Filtering events for type: " . $type);
            
            $em = new EventManager();
            
            if ($type === 'all') {
                $events = $em->findAll();
            } elseif ($type === 'upcoming') {
                $events = $em->upcomingEvents();
            } else {
                $events = $em->findByType(intval($type));
            }
            
            error_log("Number of events found: " . count($events));
    
            $eventsData = array_map(function($event) {
                $dateValues = $this->translateDate($event->getDate());
                return [
                    'id' => $event->getId(),
                    'name' => $event->getName(),
                    'shortDay' => $dateValues['shortDay'],
                    'number' => $dateValues['number'],
                    'shortMonth' => $dateValues['shortMonth'],
                    'media' => [
                        'url' => $event->getMedia()->getUrl(),
                        'alt' => $event->getMedia()->getAlt()
                    ],
                    'type' => ['name' => $event->getType()->getName()],
                    'style1' => ['name' => $event->getStyle1()->getName()],
                    'style2' => ['name' => $event->getStyle2()->getName()],
                ];
            }, $events);
    
            header('Content-Type: application/json');
            echo json_encode($eventsData);
        } catch (Exception $e) {
            error_log("Error in filterEvents: " . $e->getMessage());
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    private function extractYoutubeId(string $url): ?string
    {
        if (strpos($url, 'watch?v=') !== false) {
            return substr($url, strpos($url, 'watch?v=') + 8);
        }
        return null;
    }

}