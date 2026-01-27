<?php
session_start();
if (!isset($_COOKIE['supplier_token'])) {
    header("Location: /supplier_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supplier</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="src/output2.css"> 
    <link rel="stylesheet" href="css/animation-fade-in.css">
    <link rel="icon" type="image/png" href="images/logo1.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="min-h-screen bg-gray-50">
    
    <?php include __DIR__ . '/src/component/supplier_navbar.php'; ?>
    

    <main class="p-8 max-w-7xl mx-auto fade-in mt-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Dashboard</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
           
        </div>

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script src="src/js/user_supplier/middleware_auth.js"></script>
    <script src="src/js/user_supplier/logout.js"></script>
    <script src="src/js/user_supplier/navbar_behavior.js"></script>

</body>
</html>