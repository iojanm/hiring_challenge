<?php
ini_set("display_errors", "off");
/**
 * Load composer libraries
 */
require __DIR__ . '/../../vendor/autoload.php';

/**
 * Load .env
 */
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../');
$dotenv->load();

$allowedDomains = explode(',', getenv('ALLOWED_DOMAINS'));
$allowBlankReferrer = getenv('ALLOW_BLANK_REFERRER') || false;
$redisHost = getenv('REDIS_HOST');
$redisPort = getenv('REDIS_PORT');

?>