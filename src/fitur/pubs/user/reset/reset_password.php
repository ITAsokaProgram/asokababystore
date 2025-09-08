<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6 space-y-6">
        <h2 class="text-2xl font-bold text-center text-gray-800">Reset Password</h2>

        <form id="resetPasswordForm" method="POST" class="space-y-4">
            <!-- New Password -->
            <div>
                <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center px-3 cursor-pointer"
                        onclick="togglePassword('newPassword', this)">
                        <i class="fas fa-eye text-gray-500"></i>
                    </span>
                    <input type="password" id="newPassword" name="newPassword"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       required >

                </div>
                <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter, kombinasi huruf dan angka</p>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password
                    Baru</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center px-3 cursor-pointer"
                        onclick="togglePassword('confirmPassword', this)">
                        <i class="fas fa-eye text-gray-500"></i>
                    </span>
                    <input type="password" id="confirmPassword" name="confirmPassword"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    required>

                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitResetPassword"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-colors">
                <span class="inline-flex items-center justify-center">
                    <span class="loading-spinner hidden mr-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                    <span class="button-text">Ubah Password</span>
                </span>
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/src/js/customer_pubs/reset_password.js"></script>
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>