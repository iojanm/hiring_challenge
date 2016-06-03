<?php

include ("config/config.php");
include ("class/responseHandler.php");
include ("class/chatAPI.php");
include ("factory/redisFactory.php");
include ("service/sessionService.php");


// Response Instance
$response = new responseHandler();
$response->setAllowedDomains($allowedDomains);
$response->setAllowBlankReferer($allowBlankReferrer);

$onErrorHook = function ($errors) use ($response) {
    $lastError = array_pop($errors);
    $response->dispatchError($lastError['code'], $lastError['message']);
};

// Getting Redis Client Instance
$redisClient = new redisFactory($redisHost, $redisPort, $onErrorHook);

// Getting sessionService Instance - Injecting Dependencies 
$sessionService = new sessionService($redisClient, $onErrorHook);

// Getting ChatAPI Instace - Injecting Dependencies 
$chatAPI = new chatAPI($redisClient, $sessionService, $onErrorHook);

// Getting FriendsOnline
$friendsOnline = $chatAPI->getFriendsOnline();

// Setting Response Content
$response->setBody($friendsOnline);

// Dispatching Response
$response->dispatch();
