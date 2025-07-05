@extends('layouts.app')

@section('title', 'Treinos - TreinoApp')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-list text-blue-500 mr-2"></i>
                    Meus Treinos
                </h1>
                <p class="text-gray-600">Gerencie todos os seus treinos</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('treinos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-plus mr-2"></i>Novo Treino
                </a>
                <button onclick="refreshTreinos()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-sync-alt mr-2"></i>Atualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-filter text-gray-500 mr-2"></i>
            Filtros
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Busca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" id="search-input" placeholder="Nome do treino..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Dificuldade -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dificuldade</label>
                <select id="difficulty-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas</option>
                    <option value="iniciante">Iniciante</option>
                    <option value="intermediario">Intermediário</option>
                    <option value="avancado">Avançado</option>
                </select>
            </div>
            
            <!-- Tipo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <input type="text" id="type-filter" placeholder="Ex: Musculação..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <!-- Ordenação -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar por</label>
                <select id="sort-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="created_at,desc">Mais recentes</option>
                    <option value="created_at,asc">Mais antigos</option>
                    <option value="nome_treino,asc">Nome A-Z</option>
                    <option value="nome_treino,desc">Nome Z-A</option>
                    <option value="duracao_estimada,desc">Maior duração</option>
                    <option value="duracao_estimada,asc">Menor duração</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex space-x-3">
            <button onclick="applyFilters()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <button onclick="clearFilters()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors">
                <i class="fas fa-times mr-2"></i>Limpar
            </button>
        </div>
    </div>

    <!-- Treinos Grid -->
    <div id="treinos-container" class="space-y-4">
        <!-- Loading state -->
        <div id="loading-state" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-gray-400 text-3xl mb-4"></i>
            <p class="text-gray-500">Carregando treinos...</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination-container" class="mt-8"></div>
</div>

@push('scripts')
<script>
    let currentPage = 1;
    let currentFilters = {};
    
    // Carregar treinos
    async function loadTreinos(page = 1) {
        currentPage = page;
        
        try {
            // Construir query string
            const params = new URLSearchParams({
                page: page,
                per_page: 12,
                ...currentFilters
            });
            
            const response = await apiRequest(`/treinos?${params.toString()}`);
            const data = response.data;
            
            renderTreinos(data.data);
            renderPagination(data);
            
        } catch (error) {
            console.error('Erro ao carregar treinos:', error);
            document.getElementById('treinos-container').innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-exclamation-triangle text-red-400 text-3xl mb-4"></i>
                    <p class="text-red-500 mb-4">Erro ao carregar treinos</p>
                    <button onclick="loadTreinos()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Tentar novamente
                    </button>
                </div>
            `;
        }
    }
    
    // Renderizar treinos
    function renderTreinos(treinos) {
        const container = document.getElementById('treinos-container');
        
        if (treinos.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-dumbbell text-gray-400 text-4xl mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-600 mb-2">Nenhum treino encontrado</h3>
                    <p class="text-gray-500 mb-6">Que tal criar seu primeiro treino?</p>
                    <a href="{{ route('treinos.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>Criar Treino
                    </a>
                </div>
            `;
            return;
        }
        
        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                ${treinos.map(treino => `
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow card-hover">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-1">${treino.nome_treino}</h3>
                                    <p class="text-sm text-gray-600">${treino.tipo_treino}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getDifficultyColor(treino.dificuldade)}">
                                    ${getDifficultyText(treino.dificuldade)}
                                </span>
                            </div>
                            
                            <!-- Description -->
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                ${treino.descricao || 'Sem descrição disponível'}
                            </p>
                            
                            <!-- Stats -->
                            <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-dumbbell mr-1"></i>
                                    <span>${treino.total_exercicios} exercícios</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-clock mr-1"></i>
                                    <span>${treino.duracao_formatada}</span>
                                </div>
                            </div>
                            
                            <!-- Groups -->
                            <div class="mb-4">
                                <p class="text-xs text-gray-500 mb-1">Grupos musculares:</p>
                                <p class="text-sm text-gray-700">${treino.grupos_musculares}</p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <a href="/treinos/${treino.id}" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Ver
                                </a>
                                <a href="/treinos/${treino.id}/editar" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-center py-2 px-3 rounded-md text-sm font-medium transition-colors">
                                    <i class="fas fa-edit mr-1"></i>Editar
                                </a>
                                <button onclick="deleteTreino(${treino.id}, '${treino.nome_treino}')" class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-md text-sm font-medium transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    // Renderizar paginação
    function renderPagination(data) {
        const container = document.getElementById('pagination-container');
        
        if (data.last_page <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let pagination = '<div class="flex justify-center items-center space-x-1">';
        
        // Previous button
        if (data.current_page > 1) {
            pagination += `<button onclick="loadTreinos(${data.current_page - 1})" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Anterior</button>`;
        }
        
        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === data.current_page) {
                pagination += `<span class="px-3 py-2 text-sm bg-blue-500 text-white border border-blue-500 rounded-md">${i}</span>`;
            } else {
                pagination += `<button onclick="loadTreinos(${i})" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">${i}</button>`;
            }
        }
        
        // Next button
        if (data.current_page < data.last_page) {
            pagination += `<button onclick="loadTreinos(${data.current_page + 1})" class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50">Próximo</button>`;
        }
        
        pagination += '</div>';
        container.innerHTML = pagination;
    }
    
    // Aplicar filtros
    function applyFilters() {
        currentFilters = {};
        
        const search = document.getElementById('search-input').value.trim();
        const difficulty = document.getElementById('difficulty-filter').value;
        const type = document.getElementById('type-filter').value.trim();
        const sort = document.getElementById('sort-filter').value;
        
        if (search) currentFilters.busca = search;
        if (difficulty) currentFilters.dificuldade = difficulty;
        if (type) currentFilters.tipo_treino = type;
        
        if (sort) {
            const [field, direction] = sort.split(',');
            currentFilters.order_by = field;
            currentFilters.order_direction = direction;
        }
        
        loadTreinos(1);
    }
    
    // Limpar filtros
    function clearFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('difficulty-filter').value = '';
        document.getElementById('type-filter').value = '';
        document.getElementById('sort-filter').value = 'created_at,desc';
        
        currentFilters = {};
        loadTreinos(1);
    }
    
    // Atualizar treinos
    function refreshTreinos() {
        loadTreinos(currentPage);
    }
    
    // Deletar treino
    async function deleteTreino(id, nome) {
        if (!confirm(`Tem certeza que deseja excluir o treino "${nome}"?`)) {
            return;
        }
        
        try {
            await apiRequest(`/treinos/${id}`, 'DELETE');
            showMessage('Treino excluído com sucesso', 'success');
            refreshTreinos();
        } catch (error) {
            console.error('Erro ao excluir treino:', error);
        }
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Carregar treinos
        loadTreinos();
        
        // Aplicar filtros ao pressionar Enter
        document.getElementById('search-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
        
        document.getElementById('type-filter').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    });
</script>
@endpush
@endsection