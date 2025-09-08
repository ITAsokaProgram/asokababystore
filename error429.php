<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Too Many Requests</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .traffic-light {
            position: absolute;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            animation: trafficFlow 3s linear infinite;
        }

        .traffic-light:nth-child(1) {
            background: #ff4757;
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .traffic-light:nth-child(2) {
            background: #ffa502;
            top: 30%;
            right: 10%;
            animation-delay: 0.5s;
        }

        .traffic-light:nth-child(3) {
            background: #ff4757;
            bottom: 20%;
            left: 15%;
            animation-delay: 1s;
        }

        .traffic-light:nth-child(4) {
            background: #ffa502;
            top: 50%;
            right: 20%;
            animation-delay: 1.5s;
        }

        .traffic-light:nth-child(5) {
            background: #ff4757;
            bottom: 40%;
            right: 5%;
            animation-delay: 2s;
        }

        @keyframes trafficFlow {
            0% { opacity: 0.3; transform: scale(1) translateY(0); }
            50% { opacity: 1; transform: scale(1.2) translateY(-10px); }
            100% { opacity: 0.3; transform: scale(1) translateY(0); }
        }

        .error-container {
            text-align: center;
            color: white;
            z-index: 10;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            padding: 60px 40px;
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            max-width: 650px;
            width: 90%;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 8rem;
            font-weight: bold;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ff4757, #ff3742);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shake 2s ease-in-out infinite;
            text-shadow: 0 0 30px rgba(255, 71, 87, 0.5);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 30px;
            animation: stopLight 2s infinite;
            filter: drop-shadow(0 0 20px rgba(255, 71, 87, 0.6));
        }

        @keyframes stopLight {
            0%, 50% { color: #ff4757; text-shadow: 0 0 20px #ff4757; }
            51%, 100% { color: #ffa502; text-shadow: 0 0 20px #ffa502; }
        }

        .error-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 600;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .error-message {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.6;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
        }

        .rate-limit-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .countdown-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .countdown-item {
            background: rgba(255, 71, 87, 0.2);
            border-radius: 15px;
            padding: 20px;
            min-width: 80px;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ff4757;
            text-shadow: 0 0 10px rgba(255, 71, 87, 0.5);
        }

        .countdown-label {
            font-size: 0.9rem;
            margin-top: 5px;
            opacity: 0.8;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff4757, #ffa502);
            border-radius: 10px;
            animation: progressMove 5s ease-in-out infinite;
            box-shadow: 0 0 15px rgba(255, 71, 87, 0.5);
        }

        @keyframes progressMove {
            0% { width: 0%; }
            50% { width: 100%; }
            100% { width: 0%; }
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary {
            background: linear-gradient(45deg, #ff4757, #ff3742);
            color: white;
            box-shadow: 0 10px 25px rgba(255, 71, 87, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 71, 87, 0.4);
            background: linear-gradient(45deg, #ff3742, #ff4757);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        .tech-details {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .tips {
            text-align: left;
            margin-top: 20px;
        }

        .tips h4 {
            color: #ffa502;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .tips ul {
            list-style: none;
            padding-left: 0;
        }

        .tips li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }

        .tips li:before {
            content: "💡";
            position: absolute;
            left: 0;
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-container {
                padding: 40px 30px;
            }
            
            .countdown-container {
                gap: 10px;
            }
            
            .countdown-item {
                min-width: 60px;
                padding: 15px;
            }
            
            .countdown-number {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <div class="traffic-light"></div>
        <div class="traffic-light"></div>
        <div class="traffic-light"></div>
        <div class="traffic-light"></div>
        <div class="traffic-light"></div>
    </div>

    <div class="error-container">
        <div class="error-icon">🚦</div>
        <div class="error-code">429</div>
        <h1 class="error-title">Too Many Requests</h1>
        <p class="error-message">
            Waduh! Sepertinya Anda terlalu bersemangat. Kami telah menerima terlalu banyak permintaan dari alamat IP Anda dalam waktu singkat.
        </p>

        <div class="rate-limit-info">
            <h3 style="color: #ffa502; margin-bottom: 15px;">📊 Informasi Rate Limit</h3>
            <p><strong>Batas Permintaan:</strong> <span id="rate-limit">100</span> per menit</p>
            <p><strong>Permintaan Tersisa:</strong> <span id="remaining-requests">0</span></p>
            <p><strong>Reset Dalam:</strong> <span id="reset-time">60</span> detik</p>
        </div>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <div class="countdown-container">
            <div class="countdown-item">
                <div class="countdown-number" id="minutes">01</div>
                <div class="countdown-label">Menit</div>
            </div>
            <div class="countdown-item">
                <div class="countdown-number" id="seconds">00</div>
                <div class="countdown-label">Detik</div>
            </div>
        </div>

        <div class="tips">
            <h4>💡 Tips untuk Menghindari Error Ini:</h4>
            <ul>
                <li>Tunggu beberapa saat sebelum mencoba lagi</li>
                <li>Kurangi frekuensi permintaan Anda</li>
                <li>Gunakan caching untuk mengurangi request</li>
                <li>Pertimbangkan untuk upgrade ke paket premium</li>
            </ul>
        </div>

        <div class="action-buttons">
            <a href="/" class="btn btn-primary">
                🏠 Kembali ke Beranda
            </a>
            <button class="btn btn-secondary" id="retry-btn" disabled onclick="handleRetry()">
                ⏳ Tunggu <span id="retry-countdown">60</span>s
            </button>
        </div>

        <div class="tech-details">
            <p><strong>Error Code:</strong> HTTP 429 - Too Many Requests</p>
            <p><strong>Time:</strong> <span id="current-time"></span></p>
            <p><strong>IP Address:</strong> <span id="client-ip">xxx.xxx.xxx.xxx</span></p>
            <p><strong>Request ID:</strong> <span id="request-id"></span></p>
        </div>
    </div>

    <script>
        let countdownTime = 60; // seconds
        let remainingRequests = 0;
        
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Generate random request ID
        function generateRequestId() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }

        // Generate random IP
        function generateRandomIP() {
            return `${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`;
        }

        // Update countdown
        function updateCountdown() {
            const minutes = Math.floor(countdownTime / 60);
            const seconds = countdownTime % 60;
            
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            document.getElementById('reset-time').textContent = countdownTime;
            document.getElementById('retry-countdown').textContent = countdownTime;
            
            if (countdownTime > 0) {
                countdownTime--;
                setTimeout(updateCountdown, 1000);
            } else {
                // Enable retry button
                const retryBtn = document.getElementById('retry-btn');
                retryBtn.disabled = false;
                retryBtn.innerHTML = '🔄 Coba Lagi';
                retryBtn.classList.remove('btn-secondary');
                retryBtn.classList.add('btn-primary');
            }
        }

        // Handle retry
        function handleRetry() {
            // Simulate checking if requests are available
            const newRemainingRequests = Math.floor(Math.random() * 50) + 50;
            document.getElementById('remaining-requests').textContent = newRemainingRequests;
            
            if (newRemainingRequests > 0) {
                // Redirect to home or reload
                window.location.href = '/';
            } else {
                // Reset countdown
                countdownTime = 60;
                const retryBtn = document.getElementById('retry-btn');
                retryBtn.disabled = true;
                retryBtn.innerHTML = '⏳ Tunggu <span id="retry-countdown">60</span>s';
                retryBtn.classList.remove('btn-primary');
                retryBtn.classList.add('btn-secondary');
                updateCountdown();
            }
        }

        // Simulate remaining requests decreasing
        function updateRemainingRequests() {
            const current = parseInt(document.getElementById('remaining-requests').textContent);
            if (current < 100) {
                const increase = Math.floor(Math.random() * 3) + 1;
                document.getElementById('remaining-requests').textContent = Math.min(100, current + increase);
            }
        }

        // Initialize
        updateTime();
        updateCountdown();
        setInterval(updateTime, 1000);
        setInterval(updateRemainingRequests, 2000);
        
        document.getElementById('request-id').textContent = generateRequestId();
        document.getElementById('client-ip').textContent = generateRandomIP();

        // Add interactive traffic light effect
        document.addEventListener('mousemove', function(e) {
            const lights = document.querySelectorAll('.traffic-light');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            lights.forEach((light, index) => {
                const speed = (index + 1) * 0.3;
                const xPos = (x - 0.5) * speed * 15;
                const yPos = (y - 0.5) * speed * 15;
                light.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });

        // Add click animation to error code
        document.querySelector('.error-code').addEventListener('click', function() {
            this.style.animation = 'none';
            setTimeout(() => {
                this.style.animation = 'shake 2s ease-in-out infinite';
            }, 100);
        });
    </script>
</body>
</html>