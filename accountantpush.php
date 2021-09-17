<?php


$entryData = array(
    'topic' => "TestTopic",
    'title'    => "Test Message"
);

// This is our new stuff
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");

$socket->send(json_encode($entryData));
