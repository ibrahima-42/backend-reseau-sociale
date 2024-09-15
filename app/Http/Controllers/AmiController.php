<?php

namespace App\Http\Controllers;

use App\Models\Ami;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function demande(Request $request)
    {
        \Log::info('Requête de demande d\'ami reçue', $request->all());

        try {
            $request->validate([
                'ami_id' => 'required|exists:users,id|different:user_id'
            ]);


            if (Auth::id() === (int) $request->ami_id) {
                \Log::warning('Tentative d\'envoi de demande d\'ami à soi-même');
                return response()->json([
                    'error' => 'Vous ne pouvez pas vous envoyer une demande d\'ami'
                ], 400);
            }

            $existDemande = Ami::where(function ($query) use ($request) {
                $query->where('user_id', Auth::id())
                    ->where('ami_id', $request->ami_id);
            })->orWhere(function ($query) use ($request) {
                $query->where('user_id', $request->ami_id)
                    ->where('ami_id', Auth::id());
            })->first();

            if ($existDemande) {
                \Log::warning('Une demande existe déjà entre ces utilisateurs');
                return response()->json([
                    'error' => 'Une demande existe déjà entre ces utilisateurs'
                ], 400);
            }

            $demande = Ami::create([
                "user_id" => Auth::id(),
                "ami_id" => $request->ami_id,
                "status" => false,
            ]);

            \Log::info('Demande d\'ami créée avec succès', ['demande' => $demande]);

            return response()->json([
                'message' => 'Demande envoyée avec succès',
                'data' => $demande
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de la demande', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur interne du serveur'], 500);
        }
    }


    public function recevoirDemandeAmi()
    {
        // Récupérer l'ID de l'utilisateur connecté
        $userId = Auth::id();

        // Récupérer toutes les demandes d'amis où l'utilisateur connecté est le destinataire (ami_id)
        $demandes = Ami::where('ami_id', $userId)
            ->where('status', false) // statut non accepté (demande en attente)
            ->with('user') // Charger les informations de l'utilisateur qui a envoyé la demande
            ->get();

        // Vérifier si des demandes existent
        if ($demandes->isEmpty()) {
            return response()->json([
                'message' => 'Aucune demande d\'ami reçue.'
            ], 200);
        }

        // Retourner les demandes avec les informations de l'utilisateur demandeur
        return response()->json([
            'message' => 'Demandes d\'ami reçues.',
            'data' => $demandes
        ], 200);
    }

    public function accepterDemandeAmi($demandeId)
    {
        // Récupérer la demande d'ami avec l'ID fourni
        $demande = Ami::find($demandeId);

        // Vérifier si la demande existe et que l'utilisateur connecté est bien le destinataire
        if (!$demande || $demande->ami_id != Auth::id()) {
            return response()->json([
                'message' => 'Demande d\'ami introuvable ou non autorisée.'
            ], 404);
        }

        // Mettre à jour le statut de la demande à "accepté"
        $demande->status = true;
        $demande->save();

        return response()->json([
            'message' => 'Demande d\'ami acceptée avec succès.'
        ], 200);
    }



}
