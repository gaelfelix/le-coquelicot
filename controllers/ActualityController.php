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

        $scripts = $this->addScripts([
        ]);

        $this->render("actualites.html.twig", [
            "actualities" => $actualities
        ], $scripts);
    }

    public function actuality(string $actualityId) : void
    {
        $am = new ActualityManager();

        $actuality = $am->findOne(intval($actualityId));

        $scripts = $this->addScripts([
        ]);

        if ($actuality !== null)
        {
            $dateValues = $this->translateDate($actuality->getDate());

            $this->render("actualite.html.twig", [
                "actuality" => $actuality,
                "integralDay" => $dateValues['integralDay'],
            ], $scripts);
        }
        else
        {
            $this->redirect("index.php");
        }
    }

}