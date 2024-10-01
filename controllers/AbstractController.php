<?php

abstract class AbstractController
{
    private \Twig\Environment $twig;

    public function __construct()
    {
        $loader = new \Twig\Loader\FilesystemLoader('templates');
        $this->twig = new \Twig\Environment($loader, [
            'debug' => true,
        ]);
        
        // Ajouter la session comme variable globale
        $this->twig->addGlobal('session', $_SESSION);
        
        // Vérifier les préférences d'accessibilité et les ajouter comme variables globales
        $dyslexiaActive = isset($_SESSION['dyslexia']) && $_SESSION['dyslexia'] == true;
        $lineSpacingActive = isset($_SESSION['lineSpacing']) && $_SESSION['lineSpacing'] == true;
        
        $this->twig->addGlobal('dyslexiaActive', $dyslexiaActive);
        $this->twig->addGlobal('lineSpacingActive', $lineSpacingActive);
        
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    protected function render(string $template, array $data = [], array $scripts = []): void
    {
        $data['scripts'] = $scripts;
        echo $this->twig->render($template, $data);
    }
    
    protected function redirect(string $route): void
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

    protected function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

}