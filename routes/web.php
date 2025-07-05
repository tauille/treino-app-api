<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/teste-web', function () {
    return 'Web funcionando!';
});

/*
|--------------------------------------------------------------------------
| Rotas do Frontend da Aplicação
|--------------------------------------------------------------------------
*/

// Dashboard principal
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Páginas de treinos
Route::get('/treinos', function () {
    return view('treinos.index');
})->name('treinos.index');

Route::get('/treinos/criar', function () {
    return view('treinos.create');
})->name('treinos.create');

Route::get('/treinos/{id}', function ($id) {
    return view('treinos.show', compact('id'));
})->name('treinos.show');

Route::get('/treinos/{id}/editar', function ($id) {
    return view('treinos.edit', compact('id'));
})->name('treinos.edit');

// Login/Registro (opcional para depois)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');