<?php

session_start();
/* unset($_SESSION["error_message"]); */

require_once __DIR__ . '/vendor/autoload.php';

if(!isset($_SESSION["csrf_token"]))
    {
        $tokenManager = new CSRFTokenManager();
        $token = $tokenManager->generateCSRFToken();

        $_SESSION["csrf_token"] = $token;
    }

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new Router();

$router->handleRequest($_GET);