<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo usuário
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
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
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'google_id' => null,
                'trial_started_at' => now(),
                'is_premium' => false,
                'email_verified_at' => now(),
            ]);

            // Criar token para o usuário
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
                    'token_type' => 'Bearer'
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
                'message' => 'Erro ao criar conta',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
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
                return response()->json([
                    'success' => false,
                    'message' => 'Email ou senha incorretos'
                ], 401);
            }

            // Revogar tokens existentes se o usuário quiser (opcional)
            if ($request->revoke_other_tokens) {
                $user->tokens()->delete();
            }

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
                        'created_at' => $user->created_at,
                        'last_login' => now(),
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
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
                'message' => 'Erro ao fazer login',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
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
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
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
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
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

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_premium' => $user->is_premium,
                    'trial_started_at' => $user->trial_started_at,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados do usuário',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
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
            
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|min:2',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'current_password' => 'nullable|string|min:6',
                'password' => 'nullable|string|min:6|confirmed',
            ], [
                'name.required' => 'O nome é obrigatório.',
                'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
                'email.required' => 'O email é obrigatório.',
                'email.email' => 'Digite um email válido.',
                'email.unique' => 'Este email já está em uso.',
                'current_password.min' => 'A senha atual deve ter pelo menos 6 caracteres.',
                'password.min' => 'A nova senha deve ter pelo menos 6 caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
            ]);

            // Se está tentando alterar a senha
            if (isset($validatedData['password'])) {
                if (!isset($validatedData['current_password'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Senha atual é obrigatória para alterar a senha',
                        'errors' => [
                            'current_password' => ['A senha atual é obrigatória para alterar a senha.']
                        ]
                    ], 422);
                }
                
                if (!Hash::check($validatedData['current_password'], $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Senha atual incorreta',
                        'errors' => [
                            'current_password' => ['A senha atual não confere.']
                        ]
                    ], 422);
                }
                
                $validatedData['password'] = Hash::make($validatedData['password']);
            }

            // Remover current_password dos dados a serem salvos
            unset($validatedData['current_password']);

            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_premium' => $user->is_premium,
                    'updated_at' => $user->updated_at,
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
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Verificar se o token é válido
     */
    public function checkToken(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Token válido',
            'data' => [
                'user_id' => $request->user()->id,
                'token_name' => $request->user()->currentAccessToken()->name,
                'expires_at' => $request->user()->currentAccessToken()->expires_at,
            ]
        ]);
    }

    /**
     * Refresh token (opcional - criar novo token)
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Revogar token atual
            $request->user()->currentAccessToken()->delete();
            
            // Criar novo token
            $newToken = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token renovado com sucesso',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'Bearer'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar token',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno do servidor'
            ], 500);
        }
    }
}