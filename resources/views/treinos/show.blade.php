@extends('layouts.app')

@section('title', 'Detalhes do Treino - TreinoApp')

@section('content')
<div class="fade-in">
    <!-- Treino Header -->
    <div id="treino-header" class="bg-white rounded-lg shadow-md p-6 mb-6">
        <!-- Loading state -->
        <div class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
            <p class="text-gray-500">Carregando treino...</p>
        </div>
    </div>

    <!-- Exercícios -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-dumbbell text-blue-500 mr-2"></i>
                Exercícios
            </h2>
            <button onclick="addExercicio()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Adicionar Exercício
            </button>
        </div>
        
        <div id="exercicios-container">
            <!-- Exercícios serão carregados aqui -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-gray-400 text-2xl mb-2"></i>
                <p class="text-gray-500">Carregando exercícios...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal para adicionar/editar exercício -->
<div id="exercicio-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800">Adicionar Exercício</h3>
                <button onclick="closeExercicioModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="exercicio-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Nome do Exercício -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Exercício *</label>
                        <input type="text" id="nome_exercicio" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Grupo Muscular -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Grupo Muscular</label>
                        <select id="grupo_muscular" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            <option value="Peito">Peito</option>
                            <option value="Costas">Costas</option>
                            <option value="Ombros">Ombros</option>
                            <option value="Bíceps">Bíceps</option>
                            <option value="Tríceps">Tríceps</option>
                            <option value="Pernas">Pernas</option>
                            <option value="Glúteos">Glúteos</option>
                            <option value="Abdômen">Abdômen</option>
                            <option value="Core">Core</option>
                            <option value="Cardio">Cardio</option>
                        </select>
                    </div>
                    
                    <!-- Tipo de Execução -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Execução *</label>
                        <select id="tipo_execucao" required onchange="toggleExecucaoFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="repeticao">Por Repetições</option>
                            <option value="tempo">Por Tempo</option>
                        </select>
                    </div>
                    
                    <!-- Séries -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Séries</label>
                        <input type="number" id="series" min="1" value="3"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Repetições (só para tipo repetição) -->
                    <div id="repeticoes-field">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Repetições</label>
                        <input type="number" id="repeticoes" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Tempo de Execução (só para tipo tempo) -->
                    <div id="tempo-execucao-field" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tempo de Execução (segundos)</label>
                        <input type="number" id="tempo_execucao" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Peso -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Peso</label>
                        <div class="flex">
                            <input type="number" id="peso" step="0.1" min="0"
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <select id="unidade_peso" class="px-3 py-2 border border-gray-300 rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="kg">kg</option>
                                <option value="lb">lb</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Tempo de Descanso -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tempo de Descanso (segundos)</label>
                        <input type="number" id="tempo_descanso" min="0" value="60"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- Observações -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                        <textarea id="observacoes" rows="3" placeholder="Dicas de execução, observações especiais..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeExercicioModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-save mr-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const treinoId = {{ $id }};
    let currentExercicioId = null;
    
    // Carregar dados do treino
    async function loadTreino() {
        try {
            const response = await apiRequest(`/treinos/${treinoId}`);
            const treino = response.data;
            
            renderTreinoHeader(treino);
            renderExercicios(treino.exercicios);
            
        } catch (error) {
            console.error('Erro ao carregar treino:', error);
            document.getElementById('treino-header').innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-2"></i>
                    <p class="text-red-500 mb-4">Erro ao carregar treino</p>
                    <a href="/treinos" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Voltar aos Treinos
                    </a>
                </div>
            `;
        }
    }
    
    // Renderizar header do treino
    function renderTreinoHeader(treino) {
        document.getElementById('treino-header').innerHTML = `
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="mb-4 lg:mb-0">
                    <div class="flex items-center mb-2">
                        <a href="/treinos" class="text-blue-500 hover:text-blue-600 mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-3xl font-bold text-gray-800">${treino.nome_treino}</h1>
                        <span class="ml-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getDifficultyColor(treino.dificuldade)}">
                            ${getDifficultyText(treino.dificuldade)}
                        </span>
                    </div>
                    <p class="text-gray-600 mb-3">${treino.descricao || 'Sem descrição'}</p>
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-tag mr-1"></i>${treino.tipo_treino}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-dumbbell mr-1"></i>${treino.total_exercicios} exercícios
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-clock mr-1"></i>${treino.duracao_formatada}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-bullseye mr-1"></i>${treino.grupos_musculares}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="/treinos/${treino.id}/editar" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-edit mr-2"></i>Editar Treino
                    </a>
                    <button onclick="deleteTreino(${treino.id}, '${treino.nome_treino}')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i class="fas fa-trash mr-2"></i>Excluir
                    </button>
                </div>
            </div>
        `;
    }
    
    // Renderizar exercícios
    function renderExercicios(exercicios) {
        const container = document.getElementById('exercicios-container');
        
        if (exercicios.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-dumbbell text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-600 mb-2">Nenhum exercício cadastrado</h3>
                    <p class="text-gray-500 mb-6">Adicione exercícios para completar seu treino</p>
                    <button onclick="addExercicio()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>Adicionar Primeiro Exercício
                    </button>
                </div>
            `;
            return;
        }
        
        container.innerHTML = `
            <div class="space-y-4">
                ${exercicios.map((exercicio, index) => `
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="bg-blue-500 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold mr-3">
                                        ${exercicio.ordem}
                                    </span>
                                    <h3 class="text-lg font-semibold text-gray-800">${exercicio.nome_exercicio}</h3>
                                    ${exercicio.grupo_muscular ? `
                                        <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            ${exercicio.grupo_muscular}
                                        </span>
                                    ` : ''}
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-3">
                                    <span class="flex items-center font-medium">
                                        <i class="fas fa-redo mr-1"></i>${exercicio.texto_execucao}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-pause mr-1"></i>Descanso: ${exercicio.texto_descanso}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-1"></i>~${Math.round(exercicio.tempo_total_estimado / 60)} min
                                    </span>
                                </div>
                                
                                ${exercicio.observacoes ? `
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-3">
                                        <p class="text-sm text-yellow-700">
                                            <i class="fas fa-lightbulb mr-1"></i>
                                            ${exercicio.observacoes}
                                        </p>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                <button onclick="editExercicio(${exercicio.id})" class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteExercicio(${exercicio.id}, '${exercicio.nome_exercicio}')" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    // Modal functions
    function addExercicio() {
        currentExercicioId = null;
        document.getElementById('modal-title').textContent = 'Adicionar Exercício';
        document.getElementById('exercicio-form').reset();
        document.getElementById('series').value = '3';
        document.getElementById('tempo_descanso').value = '60';
        document.getElementById('unidade_peso').value = 'kg';
        toggleExecucaoFields();
        document.getElementById('exercicio-modal').classList.remove('hidden');
    }
    
    function editExercicio(id) {
        // Implementation for editing would load the exercise data
        // For now, just open the modal
        addExercicio();
        document.getElementById('modal-title').textContent = 'Editar Exercício';
        currentExercicioId = id;
    }
    
    function closeExercicioModal() {
        document.getElementById('exercicio-modal').classList.add('hidden');
        currentExercicioId = null;
    }
    
    function toggleExecucaoFields() {
        const tipo = document.getElementById('tipo_execucao').value;
        const repeticoesField = document.getElementById('repeticoes-field');
        const tempoField = document.getElementById('tempo-execucao-field');
        
        if (tipo === 'tempo') {
            repeticoesField.classList.add('hidden');
            tempoField.classList.remove('hidden');
        } else {
            repeticoesField.classList.remove('hidden');
            tempoField.classList.add('hidden');
        }
    }
    
    // Form submission
    document.getElementById('exercicio-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            nome_exercicio: document.getElementById('nome_exercicio').value,
            grupo_muscular: document.getElementById('grupo_muscular').value,
            tipo_execucao: document.getElementById('tipo_execucao').value,
            series: parseInt(document.getElementById('series').value) || 1,
            tempo_descanso: parseInt(document.getElementById('tempo_descanso').value) || 0,
            peso: parseFloat(document.getElementById('peso').value) || null,
            unidade_peso: document.getElementById('unidade_peso').value,
            observacoes: document.getElementById('observacoes').value
        };
        
        if (formData.tipo_execucao === 'repeticao') {
            formData.repeticoes = parseInt(document.getElementById('repeticoes').value);
        } else {
            formData.tempo_execucao = parseInt(document.getElementById('tempo_execucao').value);
        }
        
        try {
            if (currentExercicioId) {
                await apiRequest(`/treinos/${treinoId}/exercicios/${currentExercicioId}`, 'PUT', formData);
                showMessage('Exercício atualizado com sucesso', 'success');
            } else {
                await apiRequest(`/treinos/${treinoId}/exercicios`, 'POST', formData);
                showMessage('Exercício adicionado com sucesso', 'success');
            }
            
            closeExercicioModal();
            loadTreino(); // Recarregar para atualizar duração
            
        } catch (error) {
            console.error('Erro ao salvar exercício:', error);
        }
    });
    
    // Delete functions
    async function deleteExercicio(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o exercício "${nome}"?`)) {
            return;
        }
        
        try {
            await apiRequest(`/treinos/${treinoId}/exercicios/${id}`, 'DELETE');
            showMessage('Exercício excluído com sucesso', 'success');
            loadTreino();
        } catch (error) {
            console.error('Erro ao excluir exercício:', error);
        }
    }
    
    async function deleteTreino(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o treino "${nome}"?`)) {
            return;
        }
        
        try {
            await apiRequest(`/treinos/${id}`, 'DELETE');
            showMessage('Treino excluído com sucesso', 'success');
            window.location.href = '/treinos';
        } catch (error) {
            console.error('Erro ao excluir treino:', error);
        }
    }
    
    // Load data on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadTreino();
    });
</script>
@endpush
@endsection