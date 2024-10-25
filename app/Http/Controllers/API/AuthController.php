<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                "email" => "required|email",
                "password" => "required|min:6"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors(),
                ], 422); // Code 422 pour validation échouée
            }

            // Authentifier l'utilisateur
            if (Auth::attempt(['email' => $input['email'], 'password' => $input['password']])) {
                $user = Auth::user();
                $token = $user->createToken('API Token')->plainTextToken;

                return response()->json([
                    "status" => true,
                    "message" => "Connecté avec succès",
                    "token" => $token,
                    "user" => $user
                ], 200);
            }

            return response()->json([
                "status" => false,
                "message" => "Identifiants invalides"
            ], 401); // Code 401 pour une authentification échouée
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $input = $request->all();
            $validator = Validator::make($input, [
                "nom" => "required|string|max:255", // Champ pour le nom
                "prenom" => "required|string|max:255", // Champ pour le prénom
                "email" => "required|email|unique:users,email",
                "password" => "required|min:6|confirmed",
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors()->first(), // Renvoyer un seul message d'erreur
                ], 422);
            }
            
            // Créer un nouvel utilisateur
            $user = User::create([
                'nom' => $input['nom'], // Enregistrer le nom
                'prenom' => $input['prenom'], // Enregistrer le prénom
                'email' => $input['email'],
                'password' => bcrypt($input['password']), 
            ]);
    
            // Générer un token pour l'utilisateur nouvellement créé
            $token = $user->createToken('API Token')->plainTextToken;
    
            return response()->json([
                "status" => true,
                "message" => "Inscription réussie",
                "token" => $token,
                "user" => $user
            ], 201); // Code 201 pour une création réussie
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté avec succès'], 200);
    }
}
