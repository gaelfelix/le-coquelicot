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
        
        $this->twig->addGlobal('session', $_SESSION);
        
        $dyslexiaActive = isset($_SESSION['dyslexia']) && $_SESSION['dyslexia'] == true;
        $lineSpacingActive = isset($_SESSION['lineSpacing']) && $_SESSION['lineSpacing'] == true;
        
        $this->twig->addGlobal('dyslexiaActive', $dyslexiaActive);
        $this->twig->addGlobal('lineSpacingActive', $lineSpacingActive);
        
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    protected function render(string $template, array $data = [], array $scripts = []): void
    {
        $data['scripts'] = $scripts;
        $data['isUserLoggedIn'] = $this->isUserLoggedIn();
        $data['isAdmin'] = $this->isUserRole('ADMIN');
        echo $this->twig->render($template, $data);
    }
    
    protected function redirect(string $route): void
    {
        header("Location: $route");
    }

    protected function validateCsrfToken(): void
    {
        $tokenManager = new CSRFTokenManager();
        $token = null;
    
        // Vérifie si le jeton est dans $POST (formulaires HTML)
        if (isset($_POST["csrf-token"])) {
            $token = $_POST["csrf-token"];
        }
        // Vérifie si le jeton est dans le HTTP (requêtes AJAX)
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
    
        // Erreur si pas de jeton
        if (!$token || !$tokenManager->validateCSRFToken($token)) {
            throw new Exception("Invalidité du jeton CSRF");
        }
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

    protected function isUserLoggedIn(): bool
    {
        return isset($_SESSION['user']);
    }

    protected function isUserRole(string $role): bool
{
    if ($this->isUserLoggedIn()) {
        $userId = $_SESSION['user'];

        $um = new UserManager();
        $user = $um->findById($userId);

        if ($user && $user->getRole() === $role) {
            return true;
        }
    }

    // Si aucune condition n'est remplie, retourner false
    return false;
}
    
    protected function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function translateDate(DateTime $date): array {
        $days = [
            "Monday" => "Lundi",
            "Tuesday" => "Mardi",
            "Wednesday" => "Mercredi",
            "Thursday" => "Jeudi",
            "Friday" => "Vendredi",
            "Saturday" => "Samedi",
            "Sunday" => "Dimanche",
        ];

        $months = [
            "January" => "Janvier",
            "February" => "Fevrier",
            "March" => "Mars",
            "April" => "Avril",
            "May" => "Mai",
            "June" => "Juin",
            "July" => "Juillet",
            "August" => "Août",
            "September" => "Septembre",
            "October" => "Octobre",
            "November" => "Novembre",
            "December" => "Decembre",
        ];

        $day = $days[$date->format('l')];
        $number = $date->format('d');
        $month = $months[$date->format('F')];
        $year = $date->format('Y');

        $shortDay = substr($day, 0, 3);
        $shortMonth = substr($month, 0, 3);
        $integralDay = "{$day} {$number} {$month} {$year}";

        return [
            'shortDay' => $shortDay,
            'shortMonth' => $shortMonth,
            'number' => $number,
            'integralDay' => $integralDay,
        ];
    }

}