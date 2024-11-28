<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgricooUser; // Modèle spécifique pour Agricoo
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthAgricooController extends Controller
{
    // Inscription pour Agricoo
    public function register(Request $request)
    {
        $input = $request->all();

        // Validation des champs d'inscription spécifiques à Agricoo
        $validator = Validator::make($input, [
            'nom_complet' => 'required|string|max:255',
            'email' => 'required|email|unique:agricoo_users,email',
            'telephone' => 'nullable|string|max:15',
            'password' => 'required|min:6|confirmed',
            'date_naissance' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422); // Code 422 pour validation échouée
        }

        // Créer un utilisateur Agricoo
        $user = AgricooUser::create([
            'nom_complet' => $input['nom_complet'],
            'email' => $input['email'],
            'telephone' => $input['telephone'],
            'password' => bcrypt($input['password']),
            'date_naissance' => $input['date_naissance'],
        ]);

        // Générer un token pour l'utilisateur
        $token = $user->createToken('Agricoo API Token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Inscription réussie',
            'token' => $token,
            'user' => $user
        ], 201); // Code 201 pour une création réussie
    }

    // Connexion pour Agricoo
    public function login(Request $request)
    {
        $input = $request->all();

        // Validation des champs de connexion
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422); // Code 422 pour validation échouée
        }

        // Authentifier l'utilisateur
        if (Auth::attempt(['email' => $input['email'], 'password' => $input['password']])) {
            $user = Auth::user();
            $token = $user->createToken('Agricoo API Token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $user
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Identifiants invalides',
        ], 401); // Code 401 pour une authentification échouée
    }

    // Déconnexion pour Agricoo
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnexion réussie'], 200);
    }
}
