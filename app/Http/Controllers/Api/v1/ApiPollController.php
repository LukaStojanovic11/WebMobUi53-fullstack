<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiPollController extends Controller
{
    /**
     * Liste tous les sondages de l'utilisateur connecté.
     * GET /api/v1/polls
     */
    public function index(Request $request)
    {
        // On récupère les sondages de l'utilisateur connecté,
        // du plus récent au plus ancien.
        // withCount('votes') ajoute un champ "votes_count" à chaque sondage.
        $polls = $request->user()
            ->polls()
            ->withCount('votes')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($polls);
    }

    /**
     * Affiche un sondage via son token secret (accessible sans être connecté).
     * GET /api/v1/polls/{token}
     */
    public function show(string $token)
    {
        // On cherche le sondage par son token unique
        $poll = Poll::with(['options' => function ($query) {
            // Pour chaque option, on compte combien de votes elle a reçu
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Sondage introuvable.'], 404);
        }

        return response()->json($poll);
    }

    /**
     * Crée un nouveau sondage.
     * POST /api/v1/polls
     */
    public function store(Request $request)
    {
        // Validation des données reçues du frontend
        $validated = $request->validate([
            'question'               => 'required|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'duration'               => 'nullable|integer|min:1', // durée en secondes
            'is_draft'               => 'boolean',
        ]);

        // Création du sondage dans la base de données
        $poll = Poll::create([
            'user_id'                => $request->user()->id,
            'question'               => $validated['question'],
            'allow_multiple_choices' => $validated['allow_multiple_choices'] ?? false,
            'results_public'         => $validated['results_public'] ?? false,
            'duration'               => $validated['duration'] ?? null,
            'is_draft'               => $validated['is_draft'] ?? true,
            // On génère un token unique aléatoire de 32 caractères
            'secret_token'           => Str::random(32),
            // Si is_draft est false, le sondage est lancé tout de suite
            'started_at'             => isset($validated['is_draft']) && !$validated['is_draft'] ? now() : null,
            // Si une durée est donnée ET que le sondage est lancé, on calcule ends_at
            'ends_at'                => (isset($validated['is_draft']) && !$validated['is_draft'] && isset($validated['duration']))
                ? now()->addSeconds($validated['duration'])
                : null,
        ]);

        // On retourne le sondage créé avec le code 201 (Created)
        return response()->json($poll, 201);
    }

    /**
     * Met à jour un sondage existant.
     * PUT /api/v1/polls/{id}
     */
    public function update(Request $request, Poll $poll)
    {
        // Vérification : seul le créateur peut modifier son sondage
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'question'               => 'sometimes|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'results_public'         => 'boolean',
            'duration'               => 'nullable|integer|min:1',
            'is_draft'               => 'boolean',
        ]);

        // Si on passe is_draft à false (= on lance le sondage)
        // et qu'il n'était pas encore lancé, on enregistre started_at
        if (isset($validated['is_draft']) && !$validated['is_draft'] && $poll->started_at === null) {
            $validated['started_at'] = now();
            // Si une durée est définie, on calcule ends_at
            $duration = $validated['duration'] ?? $poll->duration;
            if ($duration) {
                $validated['ends_at'] = now()->addSeconds($duration);
            }
        }

        $poll->update($validated);

        return response()->json($poll);
    }

    /**
     * Supprime un sondage.
     * DELETE /api/v1/polls/{id}
     */
    public function destroy(Request $request, Poll $poll)
    {
        // Vérification : seul le créateur peut supprimer son sondage
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $poll->delete();

        // 204 = succès sans contenu à retourner
        return response()->json(null, 204);
    }

    // -------------------------------------------------------
    // GESTION DES OPTIONS
    // -------------------------------------------------------

    /**
     * Ajoute une option à un sondage.
     * POST /api/v1/polls/{id}/options
     */
    public function storeOption(Request $request, Poll $poll)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        $option = $poll->options()->create([
            'label' => $validated['label'],
        ]);

        return response()->json($option, 201);
    }

    /**
     * Modifie une option d'un sondage.
     * PUT /api/v1/polls/{id}/options/{option}
     */
    public function updateOption(Request $request, Poll $poll, PollOption $option)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
        ]);

        $option->update($validated);

        return response()->json($option);
    }

    /**
     * Supprime une option d'un sondage.
     * DELETE /api/v1/polls/{id}/options/{option}
     */
    public function destroyOption(Request $request, Poll $poll, PollOption $option)
    {
        if ($poll->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        $option->delete();

        return response()->json(null, 204);
    }
}
