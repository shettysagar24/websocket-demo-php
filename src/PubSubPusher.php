<?php

namespace Demo;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\ConnectionInterface;

Class PubSubPusher implements WampServerInterface{
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();

    function onOpen(ConnectionInterface $conn)
    {
        // TODO: Implement onOpen() method.
//        var_dump($conn);
    }

    function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
        echo "Connection Closed by ::".$conn->resourceId;
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // TODO: Implement onCall() method.
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();

    }

    function onSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onSubscribe() method.
        //Authentication & authorization
//        $conn->close();

        //
        echo "Topic Subscribed to::".$topic." by connection ::". $conn->resourceId;
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        // TODO: Implement onPublish() method.
        var_dump($event);
        echo "Message Received on Topic: ". $topic ." Event:".$event;
        $topic->broadcast($event);
//        $conn->close();
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function OnAccountantUpdate($updates) {
        echo $updates;
        $updatesData = json_decode($updates, true);
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($updatesData['topic'], $this->subscribedTopics)) {
            return;
        }

        $topic = $this->subscribedTopics[$updatesData['topic']];

        // re-send the data to all the clients subscribed to that topic
        $topic->broadcast($updates);
    }
}