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

        $events = $em->findAll();

        $this->render("programmation.html.twig", ["events" => $events]);
    }

    public function event(string $eventId) : void
    {
        $em = new EventManager();

        $event = $em->findOne(intval($eventId));

        var_dump($event->getTicketPrice());

        if ($event !== null)
        {
        $this->render("evenement.html.twig", ["event" => $event]);
        }
        else
        {
            $this->redirect("index.php?route=programmation");
        }
    }

}