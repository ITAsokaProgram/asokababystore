const http = require('http');
const WebSocket = require('ws');

const mainWssUrl = 'wss://asokababystore.com:8080';

const httpServer = http.createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/notify') {
        let body = '';

        req.on('data', chunk => {
            body += chunk.toString(); 
        });

        req.on('end', () => {
            console.log(`[HTTP Bridge] Received notification from PHP: ${body}`);
            
            try {
                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ status: 'notification_received_by_bridge' }));
            } catch (responseError) {
                console.error('[HTTP Bridge] Error sending immediate response:', responseError);
            }
            
            try {
                forwardNotificationToWebSocket(body);
            } catch (forwardError) {
                console.error('[HTTP Bridge] Error processing notification after response:', forwardError);
            }
        });

    } else {
        res.writeHead(404, { 'Content-Type': 'text/plain' });
        res.end('Not Found');
    }
}); 

function forwardNotificationToWebSocket(message) {
    const wsClient = new WebSocket(mainWssUrl, {
        rejectUnauthorized: false 
    });

    wsClient.on('open', () => {
        console.log('[WS Client] Connected to main WebSocket server.');
        wsClient.send(message);
        console.log('[WS Client] Message forwarded.');
        wsClient.close();
    });

    wsClient.on('close', () => {
        console.log('[WS Client] Connection to main WebSocket server closed.');
    });

    wsClient.on('error', (error) => {
        console.error('[WS Client] Error connecting to main WebSocket server:', error.message);
    });
}

const PORT = 8081;
const HOST = '127.0.0.1'; 
httpServer.listen(PORT, HOST, () => {
    console.log(`HTTP Bridge server is running and listening for PHP notifications on http://${HOST}:${PORT}`);
});