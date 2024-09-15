<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use HasApiTokens, Notifiable;
    /**
     * Show the form for creating a new resource.
     */
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:64',
        'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'username' => 'required|string|max:64',
        'email' => 'required|email|unique:users',
        'password' => 'required|confirmed|min:8'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Gérer le fichier d'image
    if ($request->hasFile('profile')) {
        $profilePath = $request->file('profile')->store('profiles', 'public');
    }

    // Créer l'utilisateur
    $user = User::create([
        'name' => $request->name,
        'profile' => $profilePath ?? null, // Enregistrer le chemin du fichier
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Générer un token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user,
        'token' => $token,
    ], 201);
}



public function listUsers()
{
    try {
        $users = User::select('id', 'name', 'profile')
            ->where('id', '!=',auth()->user()->id)//exclure le user qui est connecter
            ->get();

        $users->map(function ($user) {
            $user->profile_URL = $user->profile ? asset('storage/' . $user->profile) : null;
            return $user;
        });

        return response()->json($users);
    } catch (\Exception $e) {
        \Log::error("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de la récupération des utilisateurs'], 500);
    }
}

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        //
        $request->validate([
            'email'=>'required|email|exists:users',
            'password'=>'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if( !$user || !Hash::check($request->password, $user->password)){
            return [
                'message' => ' Les informations d\'identification fournies sont incorrectes. '
            ];
        }

        $token = $user->createToken($user->name)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];

    }

    public function logout(Request $request)
    {
        //
        try {
            $request->user()->tokens()->delete();

            return [
                'message'=> ' vous êtes déconnecté. '
            ];
        }

        catch(\Exception $e) {
            return response()->json([
                'message' => 'Erreur l\'or de la deconnection ', 'meaage'=> $e->getMessage()
            ],500);
        }
    }




}
