<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Berhasil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes checkmark {
            0% {
                stroke-dashoffset: 100;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes confetti-fall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }

        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke: #10b981;
            fill: none;
            animation: checkmark 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
            animation-delay: 0.2s;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            stroke: #10b981;
            stroke-width: 3;
            fill: none;
            animation: checkmark 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        .success-icon {
            animation: scaleIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .success-text {
            animation: fadeInUp 0.6s ease-out 0.3s both;
        }

        .success-subtext {
            animation: fadeInUp 0.6s ease-out 0.5s both;
        }

        .success-details {
            animation: fadeInUp 0.6s ease-out 0.7s both;
        }

        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }

        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            animation: confetti-fall linear forwards;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .success-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 overflow-hidden">
    
    <!-- Confetti Container -->
    <div id="confetti-container" class="fixed inset-0 pointer-events-none"></div>

    <!-- Success Card -->
    <div class="success-card max-w-md w-full rounded-3xl shadow-2xl p-8 md:p-12 text-center relative z-10">
        
        <!-- Success Icon with Checkmark -->
        <div class="success-icon mb-6">
            <svg class="w-24 h-24 md:w-32 md:h-32 mx-auto" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25"/>
                <path class="checkmark-check" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
        </div>

        <!-- Success Text -->
        <h1 class="success-text text-3xl md:text-4xl font-bold text-gray-800 mb-3">
            Absensi Berhasil!
        </h1>

        <p class="success-subtext text-lg text-gray-600 mb-6">
            Terima kasih telah melakukan absensi
        </p>

        <!-- Details -->
        <div class="success-details bg-green-50 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-center text-green-700 mb-2">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="font-semibold">Data tersimpan</span>
            </div>
            <p class="text-sm text-gray-600" id="timestamp"></p>
        </div>

        <!-- Icon Grid -->
        <div class="grid grid-cols-3 gap-4 mb-6 success-details">
            <div class="bg-blue-50 rounded-lg p-3 pulse-animation" style="animation-delay: 0.1s">
                <i class="fas fa-user-check text-2xl text-blue-600 mb-1"></i>
                <p class="text-xs text-gray-600">Terverifikasi</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-3 pulse-animation" style="animation-delay: 0.2s">
                <i class="fas fa-clock text-2xl text-purple-600 mb-1"></i>
                <p class="text-xs text-gray-600">Tepat Waktu</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3 pulse-animation" style="animation-delay: 0.3s">
                <i class="fas fa-database text-2xl text-green-600 mb-1"></i>
                <p class="text-xs text-gray-600">Tersimpan</p>
            </div>
        </div>

        <!-- Countdown -->
        <div class="success-details">
            <p class="text-sm text-gray-500 mb-3">
                Halaman akan ditutup otomatis dalam <span id="countdown" class="font-bold text-purple-600">5</span> detik
            </p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                <div id="progress-bar" class="bg-gradient-to-r from-purple-600 to-blue-600 h-2 rounded-full transition-all duration-1000" style="width: 100%"></div>
            </div>

            <!-- Close Button -->
            <button onclick="closeWindow()" class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-semibold py-3 px-6 rounded-xl transition duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                <i class="fas fa-times mr-2"></i>
                Tutup Sekarang
            </button>
        </div>
    </div>

    <script>
        // Confetti Animation
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7', '#a29bfe', '#fd79a8'];
            
            for (let i = 0; i < 50; i++) {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                confetti.style.animationDelay = Math.random() * 0.5 + 's';
                container.appendChild(confetti);
                
                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }
        }

        // Display current timestamp
        function updateTimestamp() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Makassar'
            };
            const formatter = new Intl.DateTimeFormat('id-ID', options);
            document.getElementById('timestamp').textContent = formatter.format(now) + ' WITA';
        }

        // Countdown timer
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progress-bar');

        function updateCountdown() {
            countdown--;
            countdownElement.textContent = countdown;
            progressBar.style.width = (countdown / 5 * 100) + '%';
            
            if (countdown <= 0) {
                closeWindow();
            }
        }

        function closeWindow() {
            // Coba tutup window/tab
            window.close();
            
            // Jika tidak bisa ditutup (browser security), redirect ke home
            setTimeout(() => {
                window.location.href = '/';
            }, 100);
        }

        // Initialize
        window.addEventListener('DOMContentLoaded', () => {
            createConfetti();
            updateTimestamp();
            
            // Start countdown
            setInterval(updateCountdown, 1000);
            
            // Play success sound (optional)
            // const audio = new Audio('/sounds/success.mp3');
            // audio.play().catch(e => console.log('Audio autoplay prevented'));
        });

        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>