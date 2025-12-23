<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sedang Maintenance</title>
    <link rel="icon" type="image/png" href="/images/logo1.png" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(to right, #ec4899, #9333ea);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .icon {
            width: 96px;
            height: 96px;
            margin: 0 auto 32px;
            animation: bounce 1s infinite;
        }

        .icon svg {
            width: 100%;
            height: 100%;
            stroke: white;
            fill: none;
            stroke-width: 2;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-10px);
            }

            50% {
                transform: translateY(0);
            }
        }

        .card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        h1 {
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin-bottom: 16px;
        }

        .divider {
            width: 96px;
            height: 4px;
            background: white;
            margin: 0 auto 24px;
            border-radius: 9999px;
        }

        .description {
            font-size: 1.125rem;
            color: white;
            line-height: 1.75;
            margin-bottom: 32px;
        }

        .loading {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 32px;
        }

        .dot {
            width: 12px;
            height: 12px;
            background: white;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .contact {
            color: white;
            font-size: 0.875rem;
        }

        .contact a {
            font-weight: 600;
            text-decoration: underline;
            color: white;
            transition: color 0.3s;
        }

        .contact a:hover {
            color: #fbcfe8;
        }

        .footer {
            color: white;
            font-size: 0.875rem;
            margin-top: 32px;
            opacity: 0.8;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .card {
                padding: 32px 24px;
            }

            .description {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>

        <div class="card">
            <h1>Sedang Maintenance</h1>
            <div class="divider"></div>
            <p class="description">
                Kami sedang melakukan perbaikan untuk meningkatkan pengalaman Anda.
            </p>


        </div>

        <p class="footer">
            © Asoka Baby Store - Terima kasih atas kesabaran Anda
        </p>
    </div>
</body>

</html>