<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo usuário
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'name.required' => 'O nome é obrigatório.',
                'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'Digite um email válido.',
                'email.unique' => 'Este email já está em uso.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'google_id' => null,
                'trial_started_at' => now(),
                'is_premium' => false,
                'email_verified_at' => now(),
            ]);

            // Criar token de autenticação
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Conta criada com sucesso!',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_premium' => $user->is_premium,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Login do usuário
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
            ], [
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'Digite um email válido.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter pelo menos 6 caracteres.',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['As credenciais fornecidas não conferem com nossos registros.'],
                ]);
            }

            // Revogar tokens antigos (opcional - para maior segurança)
            // $user->tokens()->delete();

            // Criar novo token
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => "Bem-vindo de volta, {$user->name}!",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_premium' => $user->is_premium,
                        'trial_started_at' => $user->trial_started_at,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revogar o token atual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Logout de todos os dispositivos
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            // Revogar todos os tokens do usuário
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado em todos os dispositivos'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer logout',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Buscar estatísticas do usuário
            $totalTreinos = $user->treinos()->count();
            $treinosAtivos = $user->treinos()->where('status', 'ativo')->count();
            $totalExercicios = $user->treinos()
                ->withCount(['exercicios' => function ($query) {
                    $query->where('status', 'ativo');
                }])
                ->get()
                ->sum('exercicios_count');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_premium' => $user->is_premium,
                        'trial_started_at' => $user->trial_started_at,
                        'created_at' => $user->created_at,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'stats' => [
                        'total_treinos' => $totalTreinos,
                        'treinos_ativos' => $treinosAtivos,
                        'total_exercicios' => $totalExercicios,
                        'membro_desde' => $user->created_at->diffForHumans(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Atualizar perfil do usuário
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            ], [
                'name.required' => 'O nome é obrigatório.',
                'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'Digite um email válido.',
                'email.unique' => 'Este email já está em uso.',
            ]);

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_premium' => $user->is_premium,
                        'created_at' => $user->created_at,
                    ]
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'current_password' => 'required|string|min:6',
                'password' => 'required|string|min:6|confirmed',
            ], [
                'current_password.required' => 'A senha atual é obrigatória.',
                'current_password.min' => 'A senha atual deve ter pelo menos 6 caracteres.',
                'password.required' => 'A nova senha é obrigatória.',
                'password.min' => 'A nova senha deve ter pelo menos 6 caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['A senha atual não confere.'],
                ]);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            // Revogar todos os tokens (forçar novo login)
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Senha alterada com sucesso. Faça login novamente.'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar senha',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Verificar se o token é válido
     */
    public function verifyToken(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Token válido',
            'data' => [
                'user_id' => $request->user()->id,
                'token_name' => $request->user()->currentAccessToken()->name,
                'expires_at' => null, // Sanctum não tem expiração por padrão
            ]
        ]);
    }
}