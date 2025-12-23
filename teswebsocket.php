<!DOCTYPE html>
<html>

<head>
  <title>WebSocket Example</title>
</head>

<body>
  <!-- Input untuk mengetik pesan yang akan dikirim -->
  <input type="text" id="input" placeholder="Type something...">
  <!-- Div untuk menampilkan pesan yang diterima dari server -->
  <div id="output"></div>

  <script>
    let socket;
    let keepAliveInterval;

    function connect() {
      // Membuat koneksi WebSocket ke server
      socket = new WebSocket('wss://asokababystore.com/ws');

      // Ketika koneksi WebSocket berhasil dibuka
      socket.onopen = () => {
        console.log('Connected to WebSocket server');

        // Mengirim pesan keep-alive setiap 30 detik
        // keepAliveInterval = setInterval(() => {
        //  if (socket.readyState === WebSocket.OPEN) {
        //     socket.send(JSON.stringify({ type: 'keep-alive' }));
        //   }
        //  }, 30000); // 30 detik
      };

      // Ketika menerima pesan dari server
      socket.onmessage = (event) => {
        // Jika data yang diterima berupa Blob
        if (event.data instanceof Blob) {
          const reader = new FileReader();
          reader.onload = () => {
            // Menampilkan pesan dalam elemen output
            document.getElementById('output').innerText = reader.result;
          };
          reader.readAsText(event.data);
        } else {
          // Jika data bukan Blob, langsung menampilkannya
          document.getElementById('output').innerText = event.data;
        }
      };

      // Ketika terjadi kesalahan dalam koneksi WebSocket
      socket.onerror = (error) => {
        console.error('WebSocket Error:', error);
      };

      // Ketika koneksi WebSocket ditutup
      socket.onclose = (event) => {
        console.log('WebSocket connection closed:', event);
        // Menghentikan interval keep-alive
        clearInterval(keepAliveInterval);
        // Mencoba menyambungkan ulang setelah 1 detik
        setTimeout(connect, 1000); // 1 detik
      };
    }

    // Menambahkan event listener untuk input
    document.getElementById('input').addEventListener('input', (event) => {
      console.log('Sending message:', event.target.value);
      // Mengirim pesan yang diketik ke server
      socket.send(event.target.value);
    });

    // Memulai koneksi pertama kali
    connect();
  </script>
</body>

</html>

