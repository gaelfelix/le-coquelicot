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
                'event' => $event,
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

            $this->render("evenement.html.twig", [
                "event" => $event,
                "integralDay" => $dateValues['integralDay'],
                "shortDay" => $dateValues['shortDay'],
                "number" => $dateValues['number'],
                "shortMonth" => $dateValues['shortMonth'],
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
                    return [
                        'id' => $event->getId(),
                        'name' => $event->getName(),
                        'date' => $event->getDate()->format('D d M'),
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
    $em = new EventManager();
    
    if ($type === 'all') {
        $events = $em->findAll();
    } elseif ($type === 'upcoming') {
        $events = $em->upcomingEvents(); // Assurez-vous d'implémenter cette méthode dans EventManager
    } else {
        $events = $em->findByType($type); // Assurez-vous d'implémenter cette méthode dans EventManager
    }

    // Formate les données en JSON pour les envoyer à l'interface
    $eventsData = array_map(function($event) {
        return [
            'id' => $event->getId(),
            'name' => $event->getName(),
            'date' => $event->getDate()->format('D d M'),
            'media' => [
                'url' => $event->getMedia()->getUrl(),
                'alt' => $event->getMedia()->getAlt()
            ],
            'type' => ['name' => $event->getType()->getName()],
            'style1' => ['name' => $event->getStyle1()->getName()],
            'style2' => ['name' => $event->getStyle2()->getName()],
        ];
    }, $events);

    echo json_encode($eventsData);
    exit;
}

}