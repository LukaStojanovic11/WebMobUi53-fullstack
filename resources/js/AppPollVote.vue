<script setup>
import { ref, onMounted, computed } from 'vue';
import { useFetchApi } from './composables/useFetchApi';
import { usePolling } from './composables/usePolling';

// On récupère le token depuis l'URL
// Ex: /polls/ABC123 → token = "ABC123"
const token = window.location.pathname.split('/').pop();

const { fetchApi } = useFetchApi();

// Les données du sondage
const poll = ref(null);
const error = ref(null);
const loading = ref(true);

// Les options sélectionnées par l'utilisateur (tableau pour supporter choix multiple)
const selectedOptions = ref([]);

// Message après le vote
const voteMessage = ref(null);
const hasVoted = ref(false);

// Charger le sondage depuis l'API
async function loadPoll() {
    try {
        // GET /api/v1/polls/{token} → public, pas besoin d'être connecté
        const data = await fetchApi({ url: `/polls/${token}` });
        poll.value = data;
        loading.value = false;
    } catch (err) {
        error.value = 'Sondage introuvable.';
        loading.value = false;
    }
}

// Vérifier si le sondage est terminé (date de fin dépassée)
const isExpired = computed(() => {
    if (!poll.value?.ends_at) return false;
    return new Date(poll.value.ends_at) < new Date();
});

// Vérifier si le sondage est en brouillon
const isDraft = computed(() => {
    return poll.value?.is_draft;
});

// Calculer le total des votes pour le graphique
const totalVotes = computed(() => {
    if (!poll.value?.options) return 0;
    return poll.value.options.reduce((sum, opt) => sum + (opt.votes_count ?? 0), 0);
});

// Calculer le pourcentage de votes pour une option
function getPercentage(votesCount) {
    if (totalVotes.value === 0) return 0;
    return Math.round((votesCount / totalVotes.value) * 100);
}

// Gérer la sélection d'une option
function toggleOption(optionId) {
    if (poll.value.allow_multiple_choices) {
        // Choix multiple : on ajoute ou retire l'option
        const index = selectedOptions.value.indexOf(optionId);
        if (index === -1) {
            selectedOptions.value.push(optionId);
        } else {
            selectedOptions.value.splice(index, 1);
        }
    } else {
        // Choix unique : on remplace la sélection
        selectedOptions.value = [optionId];
    }
}

// Soumettre le vote
async function submitVote() {
    if (selectedOptions.value.length === 0) {
        voteMessage.value = { type: 'error', text: 'Veuillez sélectionner une option.' };
        return;
    }

    try {
        // POST /api/v1/polls/{token}/vote
        await fetchApi({
            url: `/polls/${token}/vote`,
            method: 'POST',
            data: { option_ids: selectedOptions.value },
        });

        hasVoted.value = true;
        voteMessage.value = { type: 'success', text: 'Vote enregistré !' };
        // On recharge le sondage pour voir les résultats mis à jour
        await loadPoll();
    } catch (err) {
        if (err.status === 401) {
            voteMessage.value = { type: 'error', text: 'Vous devez être connecté pour voter.' };
        } else if (err.status === 422) {
            voteMessage.value = { type: 'error', text: err.data?.message ?? 'Vote invalide.' };
        } else {
            voteMessage.value = { type: 'error', text: 'Erreur lors du vote.' };
        }
    }
}

// Charger le sondage au démarrage
onMounted(loadPoll);

// Polling : recharger les résultats toutes les 5 secondes
usePolling(loadPoll, 5000);
</script>

<template>
    <main class="min-h-screen p-6 max-w-2xl mx-auto">

        <!-- Chargement -->
        <p v-if="loading" class="text-gray-400">Chargement...</p>

        <!-- Erreur -->
        <p v-else-if="error" class="text-red-400">{{ error }}</p>

        <!-- Sondage -->
        <div v-else-if="poll">

            <!-- Question -->
            <h1 class="text-2xl font-bold mb-2">{{ poll.question }}</h1>

            <!-- Badges statut -->
            <div class="flex gap-2 mb-6">
                <span v-if="isDraft" class="text-xs bg-yellow-600 text-white px-2 py-1 rounded">
                    Brouillon — vote non disponible
                </span>
                <span v-else-if="isExpired" class="text-xs bg-red-600 text-white px-2 py-1 rounded">
                    Sondage terminé
                </span>
                <span v-else class="text-xs bg-green-600 text-white px-2 py-1 rounded">
                    Sondage ouvert
                </span>
                <span v-if="poll.ends_at && !isExpired" class="text-xs bg-gray-600 text-white px-2 py-1 rounded">
                    Se termine le {{ new Date(poll.ends_at).toLocaleString() }}
                </span>
            </div>

            <!-- Message vote -->
            <div
                v-if="voteMessage"
                :class="voteMessage.type === 'success' ? 'bg-green-800' : 'bg-red-800'"
                class="text-white p-3 rounded mb-4"
            >
                {{ voteMessage.text }}
            </div>

            <!-- Formulaire de vote (seulement si pas brouillon, pas expiré, pas encore voté) -->
            <div v-if="!isDraft && !isExpired && !hasVoted" class="mb-6">
                <p class="text-sm text-gray-400 mb-3">
                    {{ poll.allow_multiple_choices ? 'Plusieurs choix possibles' : 'Un seul choix possible' }}
                </p>

                <div class="space-y-2">
                    <button
                        v-for="option in poll.options"
                        :key="option.id"
                        @click="toggleOption(option.id)"
                        :class="selectedOptions.includes(option.id)
                            ? 'bg-purple-600 border-purple-400'
                            : 'bg-gray-800 border-gray-600'"
                        class="w-full text-left p-3 rounded border hover:border-purple-400 transition"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <button
                    @click="submitVote"
                    class="mt-4 w-full bg-purple-600 text-white py-3 rounded font-semibold hover:bg-purple-700"
                >
                    Voter
                </button>
            </div>

            <!-- Résultats (affichés si : voté, expiré, ou résultats publics) -->
            <div v-if="hasVoted || isExpired || poll.results_public">
                <h2 class="text-lg font-semibold mb-3">Résultats</h2>
                <p class="text-sm text-gray-400 mb-4">{{ totalVotes }} vote(s) au total</p>

                <div class="space-y-3">
                    <div v-for="option in poll.options" :key="option.id">
                        <div class="flex justify-between text-sm mb-1">
                            <span>{{ option.label }}</span>
                            <span>{{ getPercentage(option.votes_count) }}% ({{ option.votes_count ?? 0 }})</span>
                        </div>
                        <!-- Barre de progression -->
                        <div class="w-full bg-gray-700 rounded h-4">
                            <div
                                class="bg-purple-500 h-4 rounded transition-all duration-500"
                                :style="{ width: getPercentage(option.votes_count) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message si résultats non publics et pas encore voté -->
            <div v-else-if="!isDraft">
                <p class="text-gray-400 text-sm mt-4">
                    Les résultats ne sont pas publics. Votez pour les voir.
                </p>
            </div>

        </div>
    </main>
</template>
