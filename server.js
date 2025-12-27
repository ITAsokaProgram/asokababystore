const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');
const server = https.createServer({
  cert: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.crt'), 
  key: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.key'),  
  ca: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.ca-bundle') 
});
const wss = new WebSocket.Server({ server });
wss.on('connection', (ws) => {
  console.log('New connection established'); 
  ws.isAlive = true;
  ws.on('pong', () => {
    ws.isAlive = true; 
  });
  ws.on('message', (message) => {
    console.log('Received message:', message);
    wss.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message.toString()); 
      }
    });
  });
  ws.on('close', (code, reason) => {
    console.log(`Connection closed. Code: ${code}, Reason: ${reason || 'No reason'}`);
  });
  ws.on('error', (error) => {
    console.error('WebSocket error:', error);
  });
  ws.send('Test');
});
const interval = setInterval(() => {
  wss.clients.forEach((ws) => {
    if (!ws.isAlive) return ws.terminate(); 
    ws.isAlive = false;
    ws.ping(null, false, true); 
  });
}, 3600000); 
wss.on('close', () => {
  clearInterval(interval); 
});
server.listen(8081, 'asokababystore.com', () => {
  console.log('WebSocket server is running on wss://asokababystore.com:8080');
});
server.on('error', (error) => {
  console.error('Server error:', error);
});
