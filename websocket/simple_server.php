<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SimpleWebSocketServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "WebSocket server started on port 8080\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Received message from %d: %s%s', $from->resourceId, $msg, "\n");
        
        // Echo the message back to the client
        $from->send('Server received: ' . $msg);
        
        // Broadcast to all connected clients
        foreach ($this->clients as $client) {
            if ($client !== $from) {
                $client->send("User {$from->resourceId} said: " . $msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Run the server application through the WebSocket protocol on port 8080
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new SimpleWebSocketServer()
        )
    ),
    8080,
    '0.0.0.0' // Listen on all interfaces
);

$server->run();
