@extends('layouts.app')

@section('title', 'Editar Treino - TreinoApp')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div id="page-header" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-500">Carregando treino...</p>
        </div>
    </div>

    <!-- Form -->
    <div id="form-container" class="hidden bg-white rounded-lg shadow-md p-6">
        <form id="treino-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome do Treino -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Treino *
                    </label>
                    <input type="text" id="nome_treino" required placeholder="Ex: Treino Push A"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Tipo de Treino -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Treino *
                    </label>
                    <select id="tipo_treino" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Selecione o tipo...</option>
                        <option value="Musculação">Musculação</option>
                        <option value="Cardio">Cardio</option>
                        <option value="Funcional">Funcional</option>
                        <option value="Calistenia">Calistenia</option>
                        <option value="CrossFit">CrossFit</option>
                        <option value="Pilates">Pilates</option>
                        <option value="Yoga">Yoga</option>
                        <option value="Alongamento">Alongamento</option>
                        <option value="HIIT">HIIT</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <!-- Dificuldade -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dificuldade
                    </label>
                    <select id="dificuldade"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Não definido</option>
                        <option value="iniciante">🟢 Iniciante</option>
                        <option value="intermediario">🟡 Intermediário</option>
                        <option value="avancado">🔴 Avançado</option>
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select id="status"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="ativo">✅ Ativo</option>
                        <option value="inativo">❌ Inativo</option>
                    </select>
                </div>

                <!-- Descrição -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição
                    </label>
                    <textarea id="descricao" rows="4" 
                              placeholder="Descreva o objetivo, grupos musculares trabalhados, observações especiais..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>
            </div>

            <!-- Current Stats -->
            <div class="bg-gray-50 rounded-lg p-6 border">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Estatísticas Atuais
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-white rounded-lg p-3">
                        <p class="text-2xl font-bold text-blue-600" id="stat-exercicios">-</p>
                        <p class="text-sm text-gray-600">Exercícios</p>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <p class="text-2xl font-bold text-green-600" id="stat-duracao">-</p>
                        <p class="text-sm text-gray-600">Duração</p>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <p class="text-xl font-bold text-purple-600" id="stat-grupos">-</p>
                        <p class="text-sm text-gray-600">Grupos</p>
                    </div>
                    <div class="bg-white rounded-lg p-3">
                        <p class="text-xl font-bold text-orange-600" id="stat-status">-</p>
                        <p class="text-sm text-gray-600">Status</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="goToTreinoDetails()" 
                        class="order-2 sm:order-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Voltar
                </button>
                
                <button type="submit" 
                        class="order-1 sm:order-2 flex-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white px-6 py-3 rounded-lg font-medium transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Salvar Alterações
                </button>
                
                <button type="button" onclick="goToManageExercicios()" 
                        class="order-3 bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-dumbbell mr-2"></i>
                    Gerenciar Exercícios
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div id="danger-zone" class="hidden bg-red-50 rounded-lg p-6 mt-6 border border-red-200">
        <h3 class="text-lg font-semibold text-red-800 mb-3">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            Zona de Perigo
        </h3>
        <p class="text-red-700 text-sm mb-4">
            As ações abaixo são irreversíveis. Tenha certeza antes de prosseguir.
        </p>
        <div class="flex flex-col sm:flex-row gap-3">
            <button onclick="archiveTreino()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-archive mr-2"></i>
                Arquivar Treino
            </button>
            <button onclick="deleteTreino()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-trash mr-2"></i>
                Excluir Permanentemente
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const treinoId = {{ $id }};
    let currentTreino = null;
    
    // Carregar dados do treino
    async function loadTreino() {
        try {
            const response = await apiRequest(`/treinos/${treinoId}`);
            currentTreino = response.data;
            
            renderPageHeader();
            fillForm();
            updateStats();
            showForm();
            
        } catch (error) {
            console.error('Erro ao carregar treino:', error);
            document.getElementById('page-header').innerHTML = `
                <div class="flex items-center">
                    <a href="/treinos" class="text-blue-500 hover:text-blue-600 mr-4">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="text-center py-4 flex-1">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-2"></i>
                        <p class="text-red-500 mb-4">Erro ao carregar treino</p>
                        <a href="/treinos" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Voltar aos Treinos
                        </a>
                    </div>
                </div>
            `;
        }
    }
    
    // Renderizar header da página
    function renderPageHeader() {
        document.getElementById('page-header').innerHTML = `
            <div class="flex items-center">
                <a href="/treinos/${treinoId}" class="text-blue-500 hover:text-blue-600 mr-4">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">
                        <i class="fas fa-edit text-yellow-500 mr-2"></i>
                        Editar: ${currentTreino.nome_treino}
                    </h1>
                    <p class="text-gray-600">Modifique as informações do seu treino</p>
                </div>
            </div>
        `;
    }
    
    // Preencher formulário
    function fillForm() {
        document.getElementById('nome_treino').value = currentTreino.nome_treino || '';
        document.getElementById('tipo_treino').value = currentTreino.tipo_treino || '';
        document.getElementById('dificuldade').value = currentTreino.dificuldade || '';
        document.getElementById('status').value = currentTreino.status || 'ativo';
        document.getElementById('descricao').value = currentTreino.descricao || '';
    }
    
    // Atualizar estatísticas
    function updateStats() {
        document.getElementById('stat-exercicios').textContent = currentTreino.total_exercicios;
        document.getElementById('stat-duracao').textContent = currentTreino.duracao_formatada;
        document.getElementById('stat-grupos').textContent = currentTreino.grupos_musculares === 'Não especificado' ? '0' : currentTreino.grupos_musculares.split(', ').length;
        document.getElementById('stat-status').textContent = currentTreino.status === 'ativo' ? '✅' : '❌';
    }
    
    // Mostrar formulário
    function showForm() {
        document.getElementById('form-container').classList.remove('hidden');
        document.getElementById('danger-zone').classList.remove('hidden');
    }
    
    // Navegação
    function goToTreinoDetails() {
        window.location.href = `/treinos/${treinoId}`;
    }
    
    function goToManageExercicios() {
        window.location.href = `/treinos/${treinoId}`;
    }
    
    // Form submission
    document.getElementById('treino-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            nome_treino: document.getElementById('nome_treino').value,
            tipo_treino: document.getElementById('tipo_treino').value,
            descricao: document.getElementById('descricao').value,
            dificuldade: document.getElementById('dificuldade').value || null,
            status: document.getElementById('status').value
        };
        
        try {
            const response = await apiRequest(`/treinos/${treinoId}`, 'PUT', formData);
            const treino = response.data;
            
            showMessage('Treino atualizado com sucesso!', 'success');
            
            // Update current treino data
            currentTreino = { ...currentTreino, ...treino };
            renderPageHeader();
            
            // Optionally redirect
            setTimeout(() => {
                window.location.href = `/treinos/${treinoId}`;
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao atualizar treino:', error);
        }
    });
    
    // Archive treino
    async function archiveTreino() {
        if (!confirm(`Tem certeza que deseja arquivar o treino "${currentTreino.nome_treino}"?\n\nO treino ficará inativo mas não será excluído.`)) {
            return;
        }
        
        try {
            await apiRequest(`/treinos/${treinoId}`, 'PUT', { status: 'inativo' });
            showMessage('Treino arquivado com sucesso', 'success');
            
            setTimeout(() => {
                window.location.href = '/treinos';
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao arquivar treino:', error);
        }
    }
    
    // Delete treino
    async function deleteTreino() {
        if (!confirm(`ATENÇÃO: Tem certeza que deseja EXCLUIR PERMANENTEMENTE o treino "${currentTreino.nome_treino}"?\n\nEsta ação não pode ser desfeita e todos os exercícios serão perdidos.`)) {
            return;
        }
        
        // Double confirmation
        const confirmText = prompt(`Para confirmar, digite o nome do treino:\n"${currentTreino.nome_treino}"`);
        if (confirmText !== currentTreino.nome_treino) {
            showMessage('Nome não confere. Exclusão cancelada.', 'error');
            return;
        }
        
        try {
            await apiRequest(`/treinos/${treinoId}`, 'DELETE');
            showMessage('Treino excluído permanentemente', 'success');
            
            setTimeout(() => {
                window.location.href = '/treinos';
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao excluir treino:', error);
        }
    }
    
    // Auto-save changes (optional)
    let autoSaveTimer;
    function autoSave() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            // Could implement auto-save here
            console.log('Auto-save triggered');
        }, 3000);
    }
    
    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTreino();
        
        // Add auto-save listeners (optional)
        const fields = ['nome_treino', 'tipo_treino', 'dificuldade', 'descricao', 'status'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.addEventListener('input', autoSave);
            }
        });
    });
</script>
@endpush
@endsection