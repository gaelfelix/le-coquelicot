<?php

// This part of code is used to initialize the session and check if it has a CSRF token
// If it doesn't have one, it generates one and stores it in the session
// The CSRF token is used to protect against CSRF attacks
// The token is stored in the session and is verified in the controllers

session_start();

require_once __DIR__ . '/vendor/autoload.php';

if(!isset($_SESSION["csrf_token"]))
    {
        $tokenManager = new CSRFTokenManager();
        $token = $tokenManager->generateCSRFToken();

        $_SESSION["csrf_token"] = $token;
    }

// Load the environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();



// Initialize the router
$router = new Router();

// Handle the request
$router->handleRequest($_GET, $_POST);