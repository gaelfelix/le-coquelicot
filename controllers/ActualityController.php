<?php

class ActualityController extends AbstractController
{
    public function __construct()
    {
        parent::__construct(); 
    }

    public function actualities() : void
    {
        $am = new ActualityManager();

        $actualities = $am->findAll();

        $this->render("actualites.html.twig", ["actualities" => $actualities]);
    }

    public function actuality(string $actualityId) : void
    {
        $am = new ActualityManager();

        $actuality = $am->findOne(intval($actualityId));

        if ($actuality !== null)
        {
        $this->render("actualite.html.twig", ["actuality" => $actuality]);
        }
        else
        {
            $this->redirect("index.php");
        }
    }

}