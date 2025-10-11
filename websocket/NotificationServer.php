<?php
namespace App\WebSocket;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

class NotificationServer implements MessageComponentInterface {
    protected $clients;
    protected $users = [];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        // Handle different types of messages
        switch ($data['type']) {
            case 'auth':
                // Store user ID with connection
                $this->users[$data['userId']] = $from;
                echo "User {$data['userId']} connected.\n";
                break;
                
            // Add more message types as needed
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove user from users array
        if ($userId = array_search($conn, $this->users)) {
            unset($this->users[$userId]);
            echo "User {$userId} disconnected.\n";
        }
        
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Send notification to a specific user
     */
    public function sendToUser($userId, $notification) {
        if (isset($this->users[$userId])) {
            $this->users[$userId]->send(json_encode($notification));
            return true;
        }
        return false;
    }
    
    /**
     * Broadcast notification to all connected clients
     */
    public function broadcast($notification) {
        foreach ($this->clients as $client) {
            $client->send(json_encode($notification));
        }
    }
}

// This part is for running the server directly (for development)
if (php_sapi_name() === 'cli') {
    require __DIR__ . '/../vendor/autoload.php';
    
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new NotificationServer()
            )
        ),
        8080 // WebSocket server port
    );

    echo "WebSocket server started on port 8080\n";
    $server->run();
}
