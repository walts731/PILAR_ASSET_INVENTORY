<?php
// Set time limit to unlimited
set_time_limit(0);

// Set the IP and port for the WebSocket server
$host = '127.0.0.1';
$port = 8080;

// Create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Set the socket options
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

// Bind the socket to the address and port
if (!socket_bind($socket, $host, $port)) {
    die("Could not bind to $host:$port - " . socket_strerror(socket_last_error()) . "\n");
}

// Start listening for connections
socket_listen($socket);

echo "WebSocket server started on $host:$port\n";

// Array to store client connections
$clients = [];

// Main server loop
while (true) {
    // Prepare array of sockets to check for new connections and messages
    $read = array_merge([$socket], $clients);
    $write = $except = null;
    
    // Wait for activity on any of the sockets
    if (socket_select($read, $write, $except, null) === false) {
        echo "Socket select failed: " . socket_strerror(socket_last_error()) . "\n";
        continue;
    }
    
    // Check for new connection
    if (in_array($socket, $read)) {
        $newClient = socket_accept($socket);
        if ($newClient) {
            // Perform WebSocket handshake
            $headers = socket_read($newClient, 1024);
            if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
                $key = base64_encode(pack(
                    'H*',
                    sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')
                ));
                
                $headers = "HTTP/1.1 101 Switching Protocols\r\n" .
                          "Upgrade: websocket\r\n" .
                          "Connection: Upgrade\r\n" .
                          "Sec-WebSocket-Accept: $key\r\n\r\n";
                
                socket_write($newClient, $headers, strlen($headers));
                
                // Add new client to the clients array
                $clients[] = $newClient;
                
                echo "New client connected. Total clients: " . count($clients) . "\n";
                
                // Send welcome message
                $welcomeMsg = json_encode([
                    'type' => 'system',
                    'message' => 'Connected to WebSocket server'
                ]);
                
                $welcomeMsg = encodeWebSocketFrame($welcomeMsg);
                @socket_write($newClient, $welcomeMsg, strlen($welcomeMsg));
            } else {
                // Not a WebSocket connection
                socket_close($newClient);
            }
        }
        
        // Remove the listening socket from the read array
        $key = array_search($socket, $read);
        if ($key !== false) {
            unset($read[$key]);
        }
    }
    
    // Check for incoming messages from clients
    foreach ($read as $client) {
        $data = @socket_read($client, 1024, PHP_BINARY_READ);
        
        if ($data === false || $data === '') {
            // Client disconnected
            $key = array_search($client, $clients);
            if ($key !== false) {
                unset($clients[$key]);
                echo "Client disconnected. Total clients: " . count($clients) . "\n";
            }
            socket_close($client);
            continue;
        }
        
        // Decode WebSocket frame
        $decoded = decodeWebSocketFrame($data);
        if ($decoded !== '') {
            $message = json_decode($decoded, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                // Handle the message
                echo "Received: " . print_r($message, true) . "\n";
                
                // Echo the message back to the client
                $response = [
                    'type' => 'echo',
                    'message' => 'You said: ' . ($message['message'] ?? ''),
                    'timestamp' => date('Y-m-d H:i:s')
                ];
                
                $response = json_encode($response);
                $response = encodeWebSocketFrame($response);
                @socket_write($client, $response, strlen($response));
            }
        }
    }
}

// Function to decode WebSocket frame
function decodeWebSocketFrame($data) {
    $unmaskedPayload = '';
    $decodedData = [];
    
    // Get the first byte and check if it's the final frame
    $firstByteBinary = sprintf('%08b', ord($data[0]));
    $secondByteBinary = sprintf('%08b', ord($data[1]));
    
    $isMasked = ($secondByteBinary[0] == '1') ? true : false;
    $payloadLength = ord($data[1]) & 127;
    
    // Unmask the payload if needed
    if ($isMasked) {
        $mask = substr($data, 2, 4);
        $payload = substr($data, 6);
        
        for ($i = 0; $i < strlen($payload); $i++) {
            $unmaskedPayload .= $payload[$i] ^ $mask[$i % 4];
        }
    } else {
        $unmaskedPayload = substr($data, 2);
    }
    
    return $unmaskedPayload;
}

// Function to encode WebSocket frame
function encodeWebSocketFrame($payload) {
    $frame = [];
    $payloadLength = strlen($payload);
    
    // First byte: FIN (1), RSV1-3 (0), opcode (1 = text)
    $frame[0] = 0x81; 
    
    // Second byte: Mask (0), payload length
    if ($payloadLength <= 125) {
        $frame[1] = $payloadLength;
    } elseif ($payloadLength <= 65535) {
        $frame[1] = 126;
        $frame[2] = ($payloadLength >> 8) & 0xFF;
        $frame[3] = $payloadLength & 0xFF;
    } else {
        $frame[1] = 127;
        // 64-bit length (we'll use 32-bit for simplicity)
        for ($i = 0; $i < 8; $i++) {
            $frame[$i + 2] = ($payloadLength >> (8 * (7 - $i))) & 0xFF;
        }
    }
    
    // Convert frame header to string
    $frameStr = '';
    foreach ($frame as $byte) {
        $frameStr .= chr($byte);
    }
    
    // Add payload
    $frameStr .= $payload;
    
    return $frameStr;
}
