<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User; // Assurez-vous d'importer le modèle User

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        // Valider l'email
        $request->validate(['email' => 'required|email']);

        // Vérifier si l'utilisateur existe
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['status' => 'If the email is registered, you will receive a reset link.'], 200);
        }

        // Vérifier si l'utilisateur a dépassé le nombre de tentatives
        if (RateLimiter::tooManyAttempts('forgot-password:' . $request->email, 5)) {
            return response()->json(['error' => 'Please wait before retrying.'], 429);
        }

        // Envoyer le lien de réinitialisation
        $response = Password::sendResetLink($request->only('email'));

        // Enregistrer l'attaque pour les tentatives
        RateLimiter::hit('forgot-password:' . $request->email);

        return $response === Password::RESET_LINK_SENT
            ? response()->json(['status' => trans($response)], 200)
            : response()->json(['error' => trans($response)], 400);
    }

    public function resetPassword(Request $request)
    {
        // Validation du mot de passe et du token
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required'
        ]);

        // Réinitialiser le mot de passe
        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );

        return $response === Password::PASSWORD_RESET
            ? response()->json(['status' => trans($response)], 200)
            : response()->json(['error' => trans($response)], 400);
    }
}
