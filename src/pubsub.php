<?php

use Demo\PubSubPusher;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/src/PubSubPusher.php';

//$server = new \Ratchet\App('localhost', 8080);
//$server->route('/pubsub', new PubSubPusher);
//$server->run();


$loop   = React\EventLoop\Factory::create();
$pusher = new PubSubPusher;


$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($pusher, 'OnAccountantUpdate'));



// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server('0.0.0.0:8080', $loop); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);

$loop->run();