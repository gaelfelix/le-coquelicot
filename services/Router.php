<?php

class Router
{
    //private AuthController $ac;
    private DefaultController $dc;
    private ActualityController $actuc;
    private EventController $ec;
    private TicketingController $tc;


    public function __construct()
    {
        //$this->ac = new AuthController();
        $this->dc = new DefaultController();
        $this->ec = new EventController();
        $this->actuc = new ActualityController();
        $this->tc = new TicketingController();
    }
    public function handleRequest(array $get) : void
    {
        if(!isset($get["route"]))
        {
            $this->dc->home();
        }
        else if(isset($get["route"]) && $get["route"] === "carousel")
        {
            $this->dc->carousel();
        }
        else if(isset($get["route"]) && $get["route"] === "programmation")
        {
            $this->ec->events();
        }
        else if(isset($get["route"]) && $get["route"] === "actualites")
        {
            $this->actuc->actualities();
        }
        else if(isset($get["route"]) && $get["route"] === "actualite")
        {
            if(isset($get["actuality_id"]))
            {
                $this->actuc->actuality($get["actuality_id"]);
            }
            else
            {
                $this->dc->home();
            }
        }
        else if(isset($get["route"]) && $get["route"] === "l-association")
        {
            $this->dc->association();
        }

        else if(isset($get["route"]) && $get["route"] === "billetterie")
        {
            $this->tc->ticketing();
        }
        else if(isset($get["route"]) && $get["route"] === "adherer-faire-un-don")
        {
            $this->dc->adhesionDonate();
        }
        else if(isset($get["route"]) && $get["route"] === "info-contact")
        {
            $this->dc->infoContact();
        }
        else
        {
            $this->dc->home();
        }
    }
}