<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review</title>
    <link rel="stylesheet" href="/src/output2.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</head>
<style>
 @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }
        
        @keyframes starGlow {
            0%, 100% { filter: drop-shadow(0 0 4px #fbbf24); }
            50% { filter: drop-shadow(0 0 8px #f59e0b); }
        }
        
        .modal-enter {
            animation: modalSlideIn 0.3s ease-out;
        }
        
        .star-glow {
            animation: starGlow 2s infinite;
        }
        
        .floating-gradient {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        /* Modal body constraints to prevent full-screen overflow on mobile */
        form#reviewForm {
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Constrain inner padding on small screens */
        @media (max-width: 640px) {
            form#reviewForm {
                margin: 0 0.5rem;
                max-width: calc(100vw - 1rem);
                border-radius: 1rem;
                padding: 1rem;
            }
            .p-8 { padding: 0.75rem; }
        }

        /* Star rating sizing and behavior */
        #starRating {
            font-size: 1.25rem; /* base for icons */
            line-height: 1;
        }
        #starRating .star {
            width: 2rem;
            height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 120ms ease, filter 200ms ease;
        }
        /* Prevent excessive scaling on small devices */
        @media (max-width: 640px) {
            #starRating .star { width: 1.6rem; height: 1.6rem; }
            .peer-checked\:scale-105 { transform: scale(1.03) !important; }
        }

        /* Tag sizing tweaks to avoid wrapping overflow */
        #tagOptions span {
            white-space: nowrap;
            max-width: 140px;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
        }
</style>
<!-- Modal Overlay -->
<div id="reviewModal" class="fixed inset-0 bg-gradient-to-br from-black/60 to-purple-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">
    <!-- Modal Content -->
    <form id="reviewForm" class="glass-effect border border-white/20 w-full max-w-lg mx-auto rounded-3xl shadow-2xl relative overflow-hidden modal-enter">

        <!-- Animated Background Elements -->
        <div class="absolute top-0 left-0 w-full h-2 floating-gradient"></div>
        <div class="absolute -top-10 -right-10 w-20 h-20 bg-pink-300/20 rounded-full blur-xl"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-300/20 rounded-full blur-xl"></div>

        <!-- Close Button -->
        <button type="button" id="closeModal" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100/80 hover:bg-red-100 text-gray-500 hover:text-red-500 transition-all duration-200 hover:scale-110 z-10">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-400 to-purple-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-700 to-gray-900 bg-clip-text text-transparent">
                    Bagikan Pengalaman Anda
                </h2>
                <p class="text-gray-500 mt-2 text-sm">Ulasan Anda sangat berharga untuk kami</p>
            </div>

            <!-- Rating Section -->
            <div class="mb-8">
                <label class="block text-gray-700 font-medium mb-3 text-center">Berikan Rating</label>
                <div class="flex items-center justify-center gap-2">
                    <div id="starRating" class="flex gap-1 cursor-pointer">
                        <!-- Stars will be generated by JS -->
                    </div>
                    <span id="ratingText" class="ml-3 text-sm text-gray-500 font-medium"></span>
                </div>
            </div>

            <!-- Review Details (Initially Hidden) -->
            <div id="reviewDetails" class="space-y-6 hidden">

                <!-- Category Tags -->
                <div class="space-y-3">
                    <label class="block text-gray-700 font-medium">Aspek yang Dinilai</label>
                    <div id="tagOptions" class="flex flex-wrap gap-2">
                        <label class="cursor-pointer group">
                            <input type="checkbox" name="tags[]" value="Pelayanan" class="hidden peer">
                            <span class="peer-checked:bg-gradient-to-r peer-checked:from-blue-500 peer-checked:to-blue-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-105 
                                           px-4 py-2 border-2 border-gray-200 rounded-full bg-gray-50 text-gray-700 
                                           hover:bg-blue-50 hover:border-blue-200 transition-all duration-200 text-sm font-medium
                                           flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Pelayanan
                            </span>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="checkbox" name="tags[]" value="Produk" class="hidden peer">
                            <span class="peer-checked:bg-gradient-to-r peer-checked:from-green-500 peer-checked:to-green-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-105
                                           px-4 py-2 border-2 border-gray-200 rounded-full bg-gray-50 text-gray-700 
                                           hover:bg-green-50 hover:border-green-200 transition-all duration-200 text-sm font-medium
                                           flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Produk
                            </span>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="checkbox" name="tags[]" value="Harga" class="hidden peer">
                            <span class="peer-checked:bg-gradient-to-r peer-checked:from-yellow-500 peer-checked:to-yellow-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-105
                                           px-4 py-2 border-2 border-gray-200 rounded-full bg-gray-50 text-gray-700 
                                           hover:bg-yellow-50 hover:border-yellow-200 transition-all duration-200 text-sm font-medium
                                           flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                Harga
                            </span>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="checkbox" name="tags[]" value="Kebersihan" class="hidden peer">
                            <span class="peer-checked:bg-gradient-to-r peer-checked:from-purple-500 peer-checked:to-purple-600 peer-checked:text-white peer-checked:shadow-lg peer-checked:scale-105
                                           px-4 py-2 border-2 border-gray-200 rounded-full bg-gray-50 text-gray-700 
                                           hover:bg-purple-50 hover:border-purple-200 transition-all duration-200 text-sm font-medium
                                           flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                Kebersihan
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Comment Section -->
                <div class="space-y-3">
                    <label for="comment" class="block text-gray-700 font-medium">Ceritakan Pengalaman Anda</label>
                    <div class="relative">
                        <textarea id="comment" name="comment" rows="4"
                            placeholder="Bagikan detail pengalaman Anda di sini..."
                            class="w-full p-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-400 focus:border-pink-400 text-sm resize-none transition-all duration-200"></textarea>
                        <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                            <span id="charCount">0</span>/500
                        </div>
                    </div>
                </div>

                <!-- Photo Upload -->
                <div class="space-y-3">
                    <label class="block text-gray-700 font-medium">Tambahkan Foto (Opsional)</label>
                    <div class="flex items-center gap-4">
                        <label for="photo" class="cursor-pointer group">
                            <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-dashed border-gray-300 
                                           flex flex-col items-center justify-center text-gray-500 
                                           hover:from-pink-50 hover:to-purple-50 hover:border-pink-300 hover:text-pink-500
                                           transition-all duration-200 group-hover:scale-105">
                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs font-medium">Foto</span>
                            </div>
                            <input type="file" id="photo" name="photos[]" accept="image/jpeg,image/png" class="hidden" multiple />
                        </label>
                        <div id="photoPreview" class="flex gap-2 flex-wrap flex-1"></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-gradient-to-r from-pink-500 to-purple-600 hover:from-pink-600 hover:to-purple-700 
                                                 text-white py-4 rounded-xl font-medium text-lg shadow-lg hover:shadow-xl 
                                                 transition-all duration-200 hover:scale-[1.02] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Kirim Ulasan
                </button>
            </div>
        </div>
    </form>
</div>
<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center hidden">
    <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 border-4 border-white border-t-transparent rounded-full animate-spin"></div>
        <p class="text-white font-medium text-lg">Mengirim review...</p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- <script src="/src/js/customer_pubs/review.js" type="module"></script> -->

</html>