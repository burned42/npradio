<?php

use NPRadio\DataFetcher\HttpDomFetcher;
use NPRadio\Stream\MetaRadio;

require_once '../vendor/autoload.php';

$uri = trim($_SERVER['REQUEST_URI'], '/');
$parts = explode('/', $uri);
if (count($parts) != 2) {
    http_response_code(400);
    exit;
}

$radioName = $parts[0];
$streamName = $parts[1];

$domFetcher = new HttpDomFetcher();
$metaRadio = new MetaRadio($domFetcher);
$info = $metaRadio->getInfo($radioName, $streamName);

echo json_encode($info->getAsArray());
