<?php

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Demo\Presentation;
use Ratchet\WebSocket\WsServer;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/Presentation.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Presentation()
        )
    ),
    8080
);

$server->run();