<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class CommentController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }
    
    public function index($postId)
    {
        try {
            // Récupérer les commentaires associés à un post spécifique, triés par date de création (du plus récent au plus ancien)
            $comments = Comment::with('user') // Inclure les informations sur l'utilisateur qui a commenté
                                ->where('post_id', $postId)
                                ->latest()
                                ->get();

            // Retourner les commentaires sous forme de réponse JSON
            return response()->json($comments);
        } catch (\Exception $e) {
            // En cas d'erreur, retourner une réponse JSON avec un message d'erreur et un statut HTTP 500
            return response()->json([
                'error' => 'Erreur lors de l\'affichage des commentaires',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $id)
    {
        // Validation de la requête
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:255', // Validation du champ text
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Vérification si le post existe
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        // Création du commentaire associé à un post spécifique
        $comment = $user->comments()->create([
            'text' => $request->text,
            'post_id' => $id, // Utilisation de l'ID du post à partir de l'URL
        ]);

        return response()->json([
            'message' => 'Commentaire ajouté avec succès',
            'comment' => $comment,
            'user' => $user
        ], 201);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
