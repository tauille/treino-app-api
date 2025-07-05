<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    /**
     * Login/Registro com Google
     */
    public function handleGoogleAuth(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'access_token' => 'required|string',
                'google_id' => 'required|string',
                'name' => 'required|string',
                'email' => 'required|email',
                'avatar_url' => 'nullable|string',
                'id_token' => 'nullable|string',
            ]);

            // Verificar token do Google
            $googleUser = $this->verifyGoogleToken($request->access_token);
            
            if (!$googleUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token do Google inválido'
                ], 401);
            }

            // Verificar se os dados conferem
            if ($googleUser['email'] !== $request->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados do Google não conferem'
                ], 400);
            }

            // Buscar ou criar usuário
            $user = $this->findOrCreateUser($request);

            // Revogar tokens antigos (opcional)
            // $user->tokens()->delete();

            // Criar novo token Sanctum
            $token = $user->createToken('google-login')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => $user->wasRecentlyCreated 
                    ? "Bem-vindo ao Treino App, {$user->name}!" 
                    : "Bem-vindo de volta, {$user->name}!",
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_premium' => $user->is_premium ?? false,
                        'trial_started_at' => $user->trial_started_at,
                        'premium_expires_at' => $user->premium_expires_at,
                        'created_at' => $user->created_at,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], $user->wasRecentlyCreated ? 201 : 200);

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
     * Verificar token do Google
     */
    private function verifyGoogleToken(string $accessToken): ?array
    {
        try {
            // Verificar token com a API do Google
            $response = Http::get('https://www.googleapis.com/oauth2/v2/userinfo', [
                'access_token' => $accessToken
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erro ao verificar token do Google: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Buscar ou criar usuário
     */
    private function findOrCreateUser(Request $request): User
    {
        // Primeiro, tentar encontrar por google_id
        $user = User::where('google_id', $request->google_id)->first();

        if ($user) {
            // Usuário já existe com este Google ID
            // Atualizar dados se necessário
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            return $user;
        }

        // Verificar se já existe usuário com este email
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            // Usuário existe mas não tem google_id
            // Vincular conta Google ao usuário existente
            $existingUser->update([
                'google_id' => $request->google_id,
                'email_verified_at' => now(),
            ]);
            return $existingUser;
        }

        // Criar novo usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'google_id' => $request->google_id,
            'password' => Hash::make(\Str::random(32)),
            'email_verified_at' => now(),
            'trial_started_at' => now(),
            'is_premium' => false,
        ]);

        return $user;
    }

    /**
     * Desconectar conta Google
     */
    public function disconnect(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->google_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conta não está vinculada ao Google'
                ], 400);
            }

            // Remover google_id
            $user->update(['google_id' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Conta Google desconectada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desconectar conta Google',
                'error' => config('app.debug') ? $e->getMessage() : 'Tente novamente mais tarde'
            ], 500);
        }
    }

    /**
     * Verificar status da conexão Google
     */
    public function status(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'is_connected' => !is_null($user->google_id ?? null),
                    'google_id' => $user->google_id ? 'connected' : null,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status',
                'error' => config('app.debug') ? $e->getMessage() : 'Erro interno'
            ], 500);
        }
    }
}