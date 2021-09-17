<?php
namespace Demo;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Predis;
use RedisClient\RedisClient;

class Presentation implements MessageComponentInterface {
    protected $clients;
    protected $redis;
    protected $subscribedChannels = array();

    public function __construct() {
        $this->clients = new \SplObjectStorage;
//        $this->redis = new Predis\Client([
//            'scheme' => 'tcp',
//            'host' => '127.0.0.1',
//            'port' => 6379
//        ]);
        $this->redis = new RedisClient();
//        $this->redis->connect("127.0.0.1",6379);
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        var_dump($conn->httpRequest->getUri()->getQuery() );
        $queryParam = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryParam, $queryArray);

        if(isset($queryArray['channel'])){
            if(!array_key_exists($queryArray['channel'], $this->subscribedChannels)){
                $this->subscribedChannels[$queryArray['channel']] = [$conn];
            }
            array_push($this->subscribedChannels[$queryArray['channel']], $conn);
        }
        echo $queryArray['channel'];

//        $this->subscribeChannel($queryArray['channel']);
        $this->redis->subscribe(['channel_1'],function($type, $channel, $message) {
            // This function will be called on subscribe and on message
            if ($type === 'subscribe') {
                // Note, if $type === 'subscribe'
                // then $channel = <channel-name>
                // and $message = <count of subsribers>
                echo 'Subscribed to channel <', $channel, '>', PHP_EOL;
            } elseif ($type === 'message') {
                echo 'Message <', $message, '> from channel <', $channel, '>', PHP_EOL;
                if ($message === 'quit') {
                    // return <false> for unsubscribe and exit
                    return false;
                }
            }
            // return <true> for to wait next message
            return true;
        });     //Callback is the callback function name
//        $this->clients->attach($conn);
        $conn->send("Established");
        echo "New connection! ({$conn->resourceId}) Established \n";
    }

    public function subscribeChannel($channel){





//        $l = $this->redis->pubSubLoop();
//        $dl = new Predis\PubSub\DispatcherLoop($l);
//
//        $dl->attachCallback($channel, function ($payload) {
//            echo "Got $payload on chan1.", PHP_EOL;
//        });
//
//        $dl->defaultCallback(function ($msg) {
//            echo "Received a message on $msg->channel.", PHP_EOL;
//        });
//
//        $l->subscribe($channel);
//
//        $l->run();




//        $pubsub = $this->redis->pubSubLoop();
//        $pubsub->subscribe($channel);
//
//        foreach ($pubsub as $message) {
//            switch ($message->kind) {
//                case 'subscribe':
//                    echo "Subscribed to {$message->channel}", PHP_EOL;
//                    break;
//
//                case 'message':
//                        echo "Received the following message from {$message->channel}:",
//                        PHP_EOL, "  {$message->payload}", PHP_EOL, PHP_EOL;
//
//                    if (array_key_exists($message->channel, $this->subscribedChannels)) {
//                        echo "Exist";
//                        $connectionList = $this->subscribedChannels[$message->channel];
//                        foreach ($connectionList as $conn)
//                            $conn->send($message->payload);
//                    }
//                    break;
//            }
//        }

        echo "Out";


//        if (!array_key_exists($channel, $this->subscribedChannels)) {
//            return;
//        }
//
//        $connectionList = $this->subscribedChannels[$channel];
//        foreach ($connectionList as $conn)
//            $conn->send($msg);
    }

    public function subscribedMessages($redis, $channel, $msg){
        echo $channel;
        if (!array_key_exists($channel, $this->subscribedChannels)) {
            return;
        }

        $connectionList = $this->subscribedChannels[$channel];
        foreach ($connectionList as $conn)
            $conn->send($msg);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
//        $numRecv = count($this->clients) - 1;
////        var_dump($msg);
//        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
//            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
        echo $msg;
        $updatesData = json_decode($msg, true);
        var_dump($this->subscribedChannels);
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($updatesData['channel'], $this->subscribedChannels)) {
            return;
        }

//        $connectionList = $this->subscribedChannels[$updatesData['channel']];
        $this->redis->publish($updatesData['channel'], $msg);

//        foreach ($connectionList as $client) {
//            if ($from !== $client) {
//                // The sender is not the receiver, send to each client connected
//                $client->send($msg);
//            }
//        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}