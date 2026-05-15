<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from './composables/useFetchApi';

const { fetchApi } = useFetchApi();

// La liste des sondages (tableau vide au départ)
const polls = ref([]);

// Message d'erreur si quelque chose tourne mal
const error = ref(null);

// Charger les sondages depuis l'API au démarrage du composant
onMounted(async () => {
    try {
        // GET /api/v1/polls → retourne la liste des sondages avec leurs options
        polls.value = await fetchApi({ url: '/polls' });
    } catch (err) {
        if (err.status === 401) {
            window.location.href = '/auth/login';
        } else {
            error.value = 'Impossible de charger les sondages.';
        }
    }
});

// Supprimer un sondage
async function deletePoll(poll) {
    if (!confirm(`Supprimer le sondage "${poll.question}" ?`)) return;

    try {
        await fetchApi({ url: `/polls/${poll.id}`, method: 'DELETE' });
        polls.value = polls.value.filter(p => p.id !== poll.id);
    } catch (err) {
        alert('Erreur lors de la suppression.');
    }
}

// Copier le lien de partage dans le presse-papier
function copyShareLink(token) {
    const url = `${window.location.origin}/polls/${token}`;
    navigator.clipboard.writeText(url);
    alert('Lien copié !');
}

// Naviguer vers l'édition en passant les données via sessionStorage
function editPoll(poll) {
    // On stocke les données du sondage temporairement
    // pour que le formulaire puisse les lire sans faire d'appel API
    sessionStorage.setItem('editPoll', JSON.stringify(poll));
    window.location.href = `/polls/${poll.id}/edit`;
}
</script>

<template>
    <main class="min-h-screen p-6 max-w-3xl mx-auto">

        <!-- Titre et bouton créer -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Mes sondages</h1>
            <a href="/polls/create" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                + Nouveau sondage
            </a>
        </div>

        <!-- Message d'erreur -->
        <p v-if="error" class="text-red-400 mb-4">{{ error }}</p>

        <!-- Message si aucun sondage -->
        <p v-if="polls.length === 0 && !error" class="text-gray-400">
            Vous n'avez pas encore de sondage.
        </p>

        <!-- Liste des sondages -->
        <ul class="space-y-4">
            <li
                v-for="poll in polls"
                :key="poll.id"
                class="bg-gray-800 rounded p-4 flex justify-between items-start"
            >
                <!-- Infos du sondage -->
                <div>
                    <p class="font-semibold text-white">{{ poll.question }}</p>
                    <!-- Badge brouillon ou lancé -->
                    <span
                        v-if="poll.is_draft"
                        class="text-xs bg-yellow-600 text-white px-2 py-0.5 rounded mt-1 inline-block"
                    >
                        Brouillon
                    </span>
                    <span
                        v-else
                        class="text-xs bg-green-600 text-white px-2 py-0.5 rounded mt-1 inline-block"
                    >
                        Lancé
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 ml-4 flex-shrink-0">
                    <!-- Copier le lien de partage -->
                    <button
                        @click="copyShareLink(poll.secret_token)"
                        class="bg-blue-600 text-white text-sm px-3 py-1 rounded hover:bg-blue-700"
                    >
                        Copier lien
                    </button>

                    <!-- Modifier le sondage -->
                    <button
                        @click="editPoll(poll)"
                        class="bg-gray-600 text-white text-sm px-3 py-1 rounded hover:bg-gray-500"
                    >
                        Modifier
                    </button>

                    <!-- Supprimer -->
                    <button
                        @click="deletePoll(poll)"
                        class="bg-red-600 text-white text-sm px-3 py-1 rounded hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </div>
            </li>
        </ul>

    </main>
</template>
