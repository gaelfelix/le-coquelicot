<?php

class TicketingController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function ticketing() : void
    {
        $em = new EventManager();

        $events = $em->upcomingEvents();

        $this->render("billetterie.html.twig", ["events" => $events]);
    }

}