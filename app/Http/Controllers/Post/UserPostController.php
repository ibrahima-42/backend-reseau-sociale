<?php

namespace App\Http\Controllers\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Validator;

class UserPostController extends Controller
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    public function create(Request $request)
    {
        try {
            // Valider les données reçues
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4048',
                'status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

             // Stockage de l'image si elle est présente
                $imagePath = null;
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('images', 'public');
                }

            $user = $request->user();

            // Créer un nouveau post avec les données validées
            $post = $user->posts()->create([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $imagePath,
                'status' => $request->status
            ]);

            // Retourner une réponse JSON avec le post créé
            return response()->json(['message' => 'Post créé avec succès', 'post' => $post, 'user' => $user], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la création du post: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        // Récupérer tous les posts et les retourner en JSON
        // $posts = Post::all();
        return Post::with(['user'])->latest()->get();
    }

    public function store(Request $request)
    {
        $field = $request->validate([
            'title'=>'required|max:255',
            'description'=>'required',
        ]);



        // $post = Post::create($field);
        $post = $request->user()->posts()->create($field);
        return ['post' => $post];
    }

    public function show(Post $post)
    {
        // Retourner un post spécifique en JSON
        return response()->json($post, 200);
    }

    public function delete($id)
    {
        // Trouver le post par son ID et le supprimer
        try {
            $post = Post::findOrFail($id);
            $post->delete();
            return response()->json([
                'message' => 'Post supprimé avec succès', 'post' => $post
            ], 200);
        }
        catch(\Exception $e) {
            return response()->json([
                'error'=>'erreur lor de la suppresion du post', 'message'=> $e->getMessage()
            ], 500);
        }
    }

    public function search(Request $request)
    {
        // Validation de la requête
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        // Récupération du mot-clé de la recherche
        $searchTerm = $request->input('query');

        // Recherche dans les titres et descriptions des posts
        $posts = Post::where('title', 'LIKE', "%{$searchTerm}%")
            ->orWhere('description', 'LIKE', "%{$searchTerm}%")
            ->get();

        // Retourner les résultats en format JSON ou vue
        return response()->json([
            'posts' => $posts,
            'message' => count($posts) > 0 ? 'Résultats trouvés' : 'Aucun résultat trouvé',
        ]);
    }
}
