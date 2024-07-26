<?php

session_start();

require_once __DIR__ . '/vendor/autoload.php';

// if(!isset($_SESSION["csrf-token"]))
// {
//     $tokenManager = new CSRFTokenManager();
//     $token = $tokenManager->generateCSRFToken();

//     $_SESSION["csrf-token"] = $token;
// }

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$router = new Router();

$router->handleRequest($_GET);