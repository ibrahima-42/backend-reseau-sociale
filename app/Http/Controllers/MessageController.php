<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $message =Message::all();
        return response()->json($message);
    }

    public function userMessage() {

        // recup l'id de user qui est connecter
        $userId = Auth::id();

        if(!$userId){
            return response()->json([
                'error' => 'user no identifier terminer'
            ],401);
        }

        // recup les message envoyer et recu par le user
        $message= Message::where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId)
                        ->get();

        return response()->json($message);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function sendMessage(Request $request)
    {
        //valider les donness
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'contenue' =>'required|string'
        ]);

        //cree un message
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request-> receiver_id,
            'contenue' => $request-> contenue,
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Message envoyer avec  success',
            'data' => $message
        ],201);
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
