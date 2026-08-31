<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Registrazione di un nuovo utente tramite API.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', Password::min(8)],
        ], [
            'first_name.required' => 'Il nome è obbligatorio.',
            'first_name.max'      => 'Il nome non può superare :max caratteri.',
            'last_name.required'  => 'Il cognome è obbligatorio.',
            'last_name.max'       => 'Il cognome non può superare :max caratteri.',
            'email.required'      => 'L\'email è obbligatoria.',
            'email.email'         => 'Inserisci un\'email valida.',
            'email.unique'        => 'Questa email è già registrata.',
            'password.required'   => 'La password è obbligatoria.',
            'password.confirmed'  => 'Le due password non coincidono.',
            'password.min'        => 'La password deve contenere almeno :min caratteri.',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            // "name" viene valorizzato per compatibilità con il pannello admin esistente
            'name'       => trim($data['first_name'].' '.$data['last_name']),
            'email'      => $data['email'],
            'password'   => $data['password'], // hash automatico via cast "hashed"
            // Registrato dalla webapp: può accedere solo dalla webapp (app = true)
            'app'        => true,
        ]);

        $token = $user->createToken('webapp')->plainTextToken;

        return response()->json([
            'user'  => $this->userInfo($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login tramite API, restituisce il token di accesso.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'L\'email è obbligatoria.',
            'email.email'       => 'Inserisci un\'email valida.',
            'password.required' => 'La password è obbligatoria.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenziali non valide.',
            ], 422);
        }

        // Solo gli utenti registrati dalla webapp (app = true) possono
        // accedere tramite API: gli altri sono riservati al backend.
        if (! $user->app) {
            return response()->json([
                'message' => 'Questo account non è abilitato all\'accesso dalla webapp.',
            ], 403);
        }

        $token = $user->createToken('webapp')->plainTextToken;

        return response()->json([
            'user'  => $this->userInfo($user),
            'token' => $token,
        ]);
    }

    /**
     * Dati dell'utente autenticato.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userInfo($request->user()),
        ]);
    }

    /**
     * Logout: revoca il token usato per la richiesta corrente.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout effettuato con successo.',
        ]);
    }

    /**
     * Rappresentazione dell'utente per le risposte API.
     * Se first_name/last_name non sono valorizzati (utenti creati
     * dal pannello admin), deriva dal campo "name".
     */
    private function userInfo(User $user): array
    {
        $firstName = $user->first_name;
        $lastName  = $user->last_name;

        if ((is_null($firstName) || $firstName === '') && (is_null($lastName) || $lastName === '')) {
            $parts = preg_split('/\s+/', trim($user->name ?? ''), 2);
            $firstName = $parts[0] ?? '';
            $lastName  = $parts[1] ?? '';
        }

        return [
            'id'         => $user->id,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $user->email,
            'app'        => (bool) $user->app,
        ];
    }
}
