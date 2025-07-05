@extends('layouts.app')

@section('title', 'Dashboard - TreinoApp')

@section('content')
<div class="fade-in">
    <!-- Welcome Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
            <i class="fas fa-home text-blue-500 mr-2"></i>
            Dashboard
        </h1>
        <p class="text-gray-600">Bem-vindo ao seu painel de treinos!</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-3 mr-4">
                    <i class="fas fa-list text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-800" id="total-treinos">Carregando...</p>
                    <p class="text-gray-600 text-sm">Total de Treinos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ações Rápidas</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('treinos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg text-center transition-colors">
                <i class="fas fa-plus text-2xl mb-2"></i>
                <p class="font-medium">Criar Novo Treino</p>
            </a>
            <a href="{{ route('treinos.index') }}" class="bg-green-500 hover:bg-green-600 text-white p-4 rounded-lg text-center transition-colors">
                <i class="fas fa-list text-2xl mb-2"></i>
                <p class="font-medium">Ver Todos os Treinos</p>
            </a>
            <button class="bg-yellow-500 hover:bg-yellow-600 text-white p-4 rounded-lg text-center transition-colors">
                <i class="fas fa-star text-2xl mb-2"></i>
                <p class="font-medium">Treinos Favoritos</p>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Teste simples
        document.getElementById('total-treinos').textContent = '2';
        showMessage('Dashboard carregado com sucesso!', 'success');
    });
</script>
@endpush
@endsection