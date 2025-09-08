const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');

// Membuat server HTTPS menggunakan sertifikat SSL
const server = https.createServer({
  cert: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.crt'), // Membaca sertifikat SSL
  key: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.key'),  // Membaca kunci privat SSL
  ca: fs.readFileSync('/etc/apache2/ssl/asokababystore_com.ca-bundle') // Membaca bundle sertifikat SSL
});

// Membuat server WebSocket yang menggunakan server HTTPS
const wss = new WebSocket.Server({ server });

// Event listener untuk koneksi baru
wss.on('connection', (ws) => {
  console.log('New connection established'); // Log ketika koneksi baru terhubung

  // Menandai koneksi sebagai hidup
  ws.isAlive = true;
  ws.on('pong', () => {
    ws.isAlive = true; // Menandai koneksi sebagai hidup saat menerima pong
  });

  // Event listener untuk menerima pesan dari klien
  ws.on('message', (message) => {
    console.log('Received message:', message);
    // Menyebarkan pesan ke semua klien
    wss.clients.forEach((client) => {
      if (client.readyState === WebSocket.OPEN) {
        client.send(message.toString()); // Pastikan pesan adalah string
      }
    });
  });

  // Event listener untuk koneksi yang ditutup
  ws.on('close', (code, reason) => {
    console.log(`Connection closed. Code: ${code}, Reason: ${reason || 'No reason'}`);
  });

  // Event listener untuk kesalahan WebSocket
  ws.on('error', (error) => {
    console.error('WebSocket error:', error);
  });

  // Mengirim pesan sambutan kepada klien yang baru terhubung
  ws.send('Welcome to WebSocket server');
});

// Interval untuk memeriksa koneksi hidup dan mengirim ping
const interval = setInterval(() => {
  wss.clients.forEach((ws) => {
    if (!ws.isAlive) return ws.terminate(); // Jika koneksi mati, putuskan koneksi

    ws.isAlive = false;
    ws.ping(null, false, true); // Kirim ping untuk memeriksa koneksi hidup
  });
//}, 60000); // Setiap 60 detik
}, 3600000); // Setiap 1 jam

// Event listener untuk penutupan server WebSocket
wss.on('close', () => {
  clearInterval(interval); // Hentikan interval ketika server ditutup
});

// Memulai server mendengarkan pada port 8080 dan hostname asokababystore.com
server.listen(8080, 'asokababystore.com', () => {
  console.log('WebSocket server is running on wss://asokababystore.com:8080');
});

// Event listener untuk kesalahan pada server
server.on('error', (error) => {
  console.error('Server error:', error);
});
