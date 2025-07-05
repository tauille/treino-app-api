@extends('layouts.app')

@section('title', 'Criar Treino - TreinoApp')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center">
            <a href="{{ route('treinos.index') }}" class="text-blue-500 hover:text-blue-600 mr-4">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-plus text-green-500 mr-2"></i>
                    Criar Novo Treino
                </h1>
                <p class="text-gray-600">Preencha as informações do seu treino</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form id="treino-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome do Treino -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Treino *
                    </label>
                    <input type="text" id="nome_treino" required placeholder="Ex: Treino Push A"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Escolha um nome descritivo para seu treino</p>
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
                        <option value="iniciante">
                            <span class="text-green-600">🟢 Iniciante</span>
                        </option>
                        <option value="intermediario">
                            <span class="text-yellow-600">🟡 Intermediário</span>
                        </option>
                        <option value="avancado">
                            <span class="text-red-600">🔴 Avançado</span>
                        </option>
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
                    <p class="text-sm text-gray-500 mt-1">Uma boa descrição ajuda a lembrar dos objetivos do treino</p>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="bg-gray-50 rounded-lg p-6 border-2 border-dashed border-gray-300">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-eye text-blue-500 mr-2"></i>
                    Preview do Treino
                </h3>
                <div id="treino-preview" class="bg-white rounded-lg p-4 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 id="preview-nome" class="text-lg font-semibold text-gray-800 mb-1">Nome do treino aparecerá aqui</h4>
                            <p id="preview-tipo" class="text-sm text-gray-600">Tipo não definido</p>
                        </div>
                        <span id="preview-dificuldade" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Não definido
                        </span>
                    </div>
                    <p id="preview-descricao" class="text-gray-600 text-sm mb-3">Descrição aparecerá aqui...</p>
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-dumbbell mr-1"></i>0 exercícios
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-clock mr-1"></i>0 min
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="window.history.back()" 
                        class="order-2 sm:order-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
                
                <button type="submit" 
                        class="order-1 sm:order-2 flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Criar Treino
                </button>
                
                <button type="button" onclick="createAndAddExercicios()" 
                        class="order-3 bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Criar e Adicionar Exercícios
                </button>
            </div>
        </form>
    </div>

    <!-- Tips Card -->
    <div class="bg-blue-50 rounded-lg p-6 mt-6">
        <h3 class="text-lg font-semibold text-blue-800 mb-3">
            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
            Dicas para um Bom Treino
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-700">
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                <span>Use nomes descritivos que ajudem a identificar rapidamente o treino</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                <span>Defina a dificuldade para organizar melhor sua progressão</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                <span>Inclua informações sobre grupos musculares na descrição</span>
            </div>
            <div class="flex items-start">
                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                <span>Após criar, você poderá adicionar exercícios detalhados</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Update preview em tempo real
    function updatePreview() {
        const nome = document.getElementById('nome_treino').value || 'Nome do treino aparecerá aqui';
        const tipo = document.getElementById('tipo_treino').value || 'Tipo não definido';
        const dificuldade = document.getElementById('dificuldade').value;
        const descricao = document.getElementById('descricao').value || 'Descrição aparecerá aqui...';
        
        document.getElementById('preview-nome').textContent = nome;
        document.getElementById('preview-tipo').textContent = tipo;
        document.getElementById('preview-descricao').textContent = descricao;
        
        // Update difficulty badge
        const difficultyBadge = document.getElementById('preview-dificuldade');
        if (dificuldade) {
            difficultyBadge.textContent = getDifficultyText(dificuldade);
            difficultyBadge.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getDifficultyColor(dificuldade)}`;
        } else {
            difficultyBadge.textContent = 'Não definido';
            difficultyBadge.className = 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
        }
    }
    
    // Add event listeners para live preview
    document.addEventListener('DOMContentLoaded', function() {
        const fields = ['nome_treino', 'tipo_treino', 'dificuldade', 'descricao'];
        fields.forEach(field => {
            document.getElementById(field).addEventListener('input', updatePreview);
        });
        
        // Initial preview update
        updatePreview();
    });
    
    // Form submission
    document.getElementById('treino-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            nome_treino: document.getElementById('nome_treino').value,
            tipo_treino: document.getElementById('tipo_treino').value,
            descricao: document.getElementById('descricao').value,
            dificuldade: document.getElementById('dificuldade').value || null
        };
        
        try {
            const response = await apiRequest('/treinos', 'POST', formData);
            const treino = response.data;
            
            showMessage('Treino criado com sucesso!', 'success');
            
            // Redirect to treinos list
            setTimeout(() => {
                window.location.href = '/treinos';
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao criar treino:', error);
        }
    });
    
    // Create and redirect to add exercises
    async function createAndAddExercicios() {
        const formData = {
            nome_treino: document.getElementById('nome_treino').value,
            tipo_treino: document.getElementById('tipo_treino').value,
            descricao: document.getElementById('descricao').value,
            dificuldade: document.getElementById('dificuldade').value || null
        };
        
        // Validate required fields
        if (!formData.nome_treino || !formData.tipo_treino) {
            showMessage('Preencha os campos obrigatórios (Nome e Tipo)', 'error');
            return;
        }
        
        try {
            const response = await apiRequest('/treinos', 'POST', formData);
            const treino = response.data;
            
            showMessage('Treino criado! Redirecionando para adicionar exercícios...', 'success');
            
            // Redirect to treino details to add exercises
            setTimeout(() => {
                window.location.href = `/treinos/${treino.id}`;
            }, 1500);
            
        } catch (error) {
            console.error('Erro ao criar treino:', error);
        }
    }
    
    // Auto-save draft (optional feature)
    let draftTimer;
    function saveDraft() {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(() => {
            const formData = {
                nome_treino: document.getElementById('nome_treino').value,
                tipo_treino: document.getElementById('tipo_treino').value,
                descricao: document.getElementById('descricao').value,
                dificuldade: document.getElementById('dificuldade').value
            };
            
            localStorage.setItem('treino_draft', JSON.stringify(formData));
        }, 1000);
    }
    
    // Load draft
    function loadDraft() {
        const draft = localStorage.getItem('treino_draft');
        if (draft) {
            const data = JSON.parse(draft);
            document.getElementById('nome_treino').value = data.nome_treino || '';
            document.getElementById('tipo_treino').value = data.tipo_treino || '';
            document.getElementById('descricao').value = data.descricao || '';
            document.getElementById('dificuldade').value = data.dificuldade || '';
            updatePreview();
        }
    }
    
    // Clear draft when form is submitted
    document.getElementById('treino-form').addEventListener('submit', function() {
        localStorage.removeItem('treino_draft');
    });
    
    // Load draft on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadDraft();
        
        // Add auto-save listeners
        const fields = ['nome_treino', 'tipo_treino', 'descricao', 'dificuldade'];
        fields.forEach(field => {
            document.getElementById(field).addEventListener('input', saveDraft);
        });
    });
</script>
@endpush
@endsection