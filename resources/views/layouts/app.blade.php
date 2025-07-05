<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TreinoApp - Gerenciador de Treinos')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans">
    <!-- Navbar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center text-white font-bold text-xl">
                        <i class="fas fa-dumbbell mr-2 text-yellow-300"></i>
                        TreinoApp
                    </a>
                </div>
                
                <!-- Navigation Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="{{ route('dashboard') }}" class="text-white hover:text-yellow-300 transition-colors">
                        <i class="fas fa-tachometer-alt mr-1"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('treinos.index') }}" class="text-white hover:text-yellow-300 transition-colors">
                        <i class="fas fa-list mr-1"></i>
                        Treinos
                    </a>
                    <a href="{{ route('treinos.create') }}" class="bg-yellow-500 hover:bg-yellow-600 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-1"></i>
                        Novo Treino
                    </a>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-white hover:text-yellow-300">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <a href="{{ route('dashboard') }}" class="block text-white hover:text-yellow-300 py-2">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="{{ route('treinos.index') }}" class="block text-white hover:text-yellow-300 py-2">
                    <i class="fas fa-list mr-2"></i>Treinos
                </a>
                <a href="{{ route('treinos.create') }}" class="block text-white hover:text-yellow-300 py-2">
                    <i class="fas fa-plus mr-2"></i>Novo Treino
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Content -->
    <main class="max-w-7xl mx-auto py-6 px-4">
        <!-- Flash Messages -->
        <div id="flash-messages" class="mb-6"></div>
        
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 flex items-center">
                <i class="fas fa-spinner fa-spin text-blue-500 text-2xl mr-3"></i>
                <span class="text-gray-700">Carregando...</span>
            </div>
        </div>
        
        <!-- Page Content -->
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="max-w-7xl mx-auto py-6 px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; 2025 TreinoApp. Todos os direitos reservados.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-yellow-300 transition-colors">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="hover:text-yellow-300 transition-colors">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        // Global variables
        const API_BASE_URL = '/api';
        const API_TOKEN = localStorage.getItem('api_token') || '1|nFYwc2j3ZBuSbFvhBo9LwJUOHbH5WKzfg97oeuQE07da4901';
        
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileBtn && mobileMenu) {
                mobileBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
        
        // API Helper Functions
        async function apiRequest(endpoint, method = 'GET', data = null) {
            showLoading();
            
            const config = {
                method: method,
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            };
            
            if (data) {
                config.body = JSON.stringify(data);
            }
            
            try {
                const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Erro na requisição');
                }
                
                hideLoading();
                return result;
            } catch (error) {
                hideLoading();
                showMessage(error.message, 'error');
                throw error;
            }
        }
        
        // Loading functions
        function showLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.remove('hidden');
            }
        }
        
        function hideLoading() {
            const overlay = document.getElementById('loading-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }
        
        // Message functions
        function showMessage(message, type = 'success') {
            const container = document.getElementById('flash-messages');
            if (!container) return;
            
            const alertClass = type === 'error' ? 'bg-red-100 border-red-500 text-red-700' : 'bg-green-100 border-green-500 text-green-700';
            const iconClass = type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle';
            
            const alert = document.createElement('div');
            alert.className = `border-l-4 p-4 mb-4 ${alertClass} fade-in`;
            alert.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${iconClass} mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-xl">&times;</button>
                </div>
            `;
            
            container.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 5000);
        }
        
        // Utility functions
        function getDifficultyColor(dificuldade) {
            switch(dificuldade) {
                case 'iniciante': return 'text-green-600 bg-green-100';
                case 'intermediario': return 'text-yellow-600 bg-yellow-100';
                case 'avancado': return 'text-red-600 bg-red-100';
                default: return 'text-gray-600 bg-gray-100';
            }
        }
        
        function getDifficultyText(dificuldade) {
            switch(dificuldade) {
                case 'iniciante': return 'Iniciante';
                case 'intermediario': return 'Intermediário';
                case 'avancado': return 'Avançado';
                default: return 'Não definido';
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>