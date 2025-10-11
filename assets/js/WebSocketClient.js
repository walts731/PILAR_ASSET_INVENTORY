class WebSocketClient {
    constructor(url) {
        this.url = url || `ws://${window.location.hostname}:8080`;
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 1000; // Start with 1 second delay
        this.messageHandlers = new Map();
        this.connectionHandlers = new Map();
        this.connected = false;
        this.userId = null;
    }

    connect(userId) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            console.log('WebSocket already connected');
            return;
        }

        this.userId = userId;
        this.socket = new WebSocket(this.url);

        this.socket.onopen = (event) => {
            console.log('WebSocket connected');
            this.connected = true;
            this.reconnectAttempts = 0;
            
            // Authenticate the user
            this.send({
                type: 'auth',
                userId: this.userId
            });
            
            // Notify all connection handlers
            this.connectionHandlers.forEach(handler => handler(true));
        };

        this.socket.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);
                this.handleMessage(message);
            } catch (error) {
                console.error('Error parsing WebSocket message:', error);
            }
        };

        this.socket.onclose = (event) => {
            console.log('WebSocket disconnected');
            this.connected = false;
            
            // Notify all connection handlers
            this.connectionHandlers.forEach(handler => handler(false));
            
            // Attempt to reconnect
            this.attemptReconnect();
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
            
            console.log(`Attempting to reconnect (${this.reconnectAttempts}/${this.maxReconnectAttempts}) in ${delay}ms`);
            
            setTimeout(() => {
                this.connect(this.userId);
            }, delay);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    disconnect() {
        if (this.socket) {
            this.socket.close();
            this.socket = null;
            this.connected = false;
        }
    }

    send(message) {
        if (this.connected && this.socket) {
            this.socket.send(JSON.stringify(message));
            return true;
        } else {
            console.warn('WebSocket not connected, message not sent:', message);
            return false;
        }
    }

    on(event, handler) {
        if (event === 'connect' || event === 'disconnect') {
            this.connectionHandlers.set(handler, (isConnected) => {
                if ((event === 'connect' && isConnected) || (event === 'disconnect' && !isConnected)) {
                    handler();
                }
            });
        } else {
            if (!this.messageHandlers.has(event)) {
                this.messageHandlers.set(event, new Set());
            }
            this.messageHandlers.get(event).add(handler);
        }
    }

    off(event, handler) {
        if (event === 'connect' || event === 'disconnect') {
            this.connectionHandlers.delete(handler);
        } else if (this.messageHandlers.has(event)) {
            this.messageHandlers.get(event).delete(handler);
        }
    }

    handleMessage(message) {
        if (!message.type) return;
        
        // Call specific handlers for this message type
        if (this.messageHandlers.has(message.type)) {
            this.messageHandlers.get(message.type).forEach(handler => {
                try {
                    handler(message);
                } catch (error) {
                    console.error(`Error in ${message.type} handler:`, error);
                }
            });
        }
        
        // Call general message handlers
        if (this.messageHandlers.has('*')) {
            this.messageHandlers.get('*').forEach(handler => {
                try {
                    handler(message);
                } catch (error) {
                    console.error('Error in general message handler:', error);
                }
            });
        }
    }

    isConnected() {
        return this.connected;
    }
}

// Create a singleton instance
const webSocketClient = new WebSocketClient();

export default webSocketClient;
