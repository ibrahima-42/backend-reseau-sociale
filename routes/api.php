<?php

use App\Http\Controllers\AmiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Post\UserPostController;
use App\Http\Controllers\Authentification\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('posts',UserPostController::class);

// Route pour post
Route::post('/ajoutPost', [UserPostController::class, 'create'])->middleware('auth:sanctum');
Route::get('/listePost', [UserPostController::class, 'index']);
Route::delete('/deletePost/{id}', [UserPostController::class, 'delete'])->middleware('auth:sanctum');
//

//Route pour l'inscription , connection et deconnection  user
Route::post('/registerUser', [UserController::class, 'register']);
Route::post('/loginUser', [UserController::class, 'login']);
Route::post('/logoutUser', [UserController::class, 'logout'])->middleware('auth:sanctum');
//


//Route pour les commentaires
Route::post('/ajoutComment/{id}', [CommentController::class, 'create'])->middleware('auth:sanctum');
Route::get('/{postId}/comments', [CommentController::class, 'index']);
//


// Route pour les messages
Route::get('/listMessage', [MessageController::class, 'index']);
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/userMessage', [MessageController::class, 'userMessage']);
    Route::post('/sendMessage', [MessageController::class, 'sendMessage']);
});
//


//Route pour faire une demande d'ami
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/demande', [AmiController::class, 'demande'])->middleware('auth:sanctum');
});
//


//Route pour lister les users
Route::get('/users', [UserController::class, 'listUsers'])->middleware('auth:sanctum');
//


//Route pour la reception de demande d'ami
Route::get('/demande/recues', [AmiController::class, 'recevoirDemandeAmi'])->middleware('auth:sanctum');
Route::post('/accepter-demande/{id}', [AmiController::class, 'accepterDemandeAmi'])->middleware('auth:sanctum');
//


// routes/web.php ou routes/api.php
Route::get('/search', [UserController::class, 'search']);


