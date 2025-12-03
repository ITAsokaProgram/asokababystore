<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Struk Transaksi</title>
    <link rel="stylesheet" href="../../../src/output2.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" href="../../../public/images/logo1.png">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>

<body class="bg-gray-100 py-10">
    <div class="max-w-md mx-auto">
        <div class="flex justify-between mb-2 px-4">
            <button id="print" type="button"
                class="bg-gray-600 rounded text-sm shadow px-4 py-1 text-white hover:bg-gray-700 transition">Print
                PDF</button>
        </div>

        <div id="struk-container" class="p-4 text-sm text-black font-mono leading-tight space-y-2">
            <div class="text-center py-10">
                <i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i>
                <p class="text-gray-500 mt-2">Memuat Struk...</p>
            </div>
        </div>

        <div id="pdf-container" class="hidden"></div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="../../js/review/struk_admin.js" type="module"></script>
</body>

</html>