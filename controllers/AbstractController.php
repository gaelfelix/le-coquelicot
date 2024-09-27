<?php

abstract class AbstractController
{
    
    private \Twig\Environment $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $twig = new \Twig\Environment($loader,[
            'debug' => true,
        ]);
        
        $twig->addGlobal('session', $_SESSION);
        $twig->addExtension(new \Twig\Extension\DebugExtension());

        $this->twig = $twig;
    }

    protected function render(string $template, array $data = [], array $scripts = []) : void
    {
        $data['scripts'] = $scripts;
    
        echo $this->twig->render($template, $data);
    }
    
    protected function redirect(string $route) : void
    {
        header("Location: $route");
    }

    protected function addScripts(array $scripts): array
    {
        return array_merge($this->getDefaultScripts(), $scripts);
    }

    protected function getDefaultScripts(): array
    {
        return [
            'assets/js/global.js',
        ];
    }
}