<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from './composables/useFetchApi';

const { fetchApi } = useFetchApi();

const polls = ref([]);
const error = ref(null);

onMounted(async () => {
    try {
        polls.value = await fetchApi({ url: '/polls' });
    } catch (err) {
        if (err.status === 401) {
            window.location.href = '/auth/login';
        } else {
            error.value = 'Impossible de charger les sondages.';
        }
    }
});

async function deletePoll(poll) {
    if (!confirm(`Supprimer le sondage "${poll.question}" ?`)) return;

    try {
        await fetchApi({ url: `/polls/${poll.id}`, method: 'DELETE' });
        // On retire le sondage de la liste locale sans recharger la page
        polls.value = polls.value.filter(p => p.id !== poll.id);
    } catch (err) {
        // 204 = succès sans contenu, ce n'est pas une erreur
        if (err.status === 204 || err.statusText === 'Error parsing response body as JSON') {
            polls.value = polls.value.filter(p => p.id !== poll.id);
        } else {
            alert('Erreur lors de la suppression.');
        }
    }
}

function copyShareLink(token) {
    const url = `${window.location.origin}/polls/${token}`;
    navigator.clipboard.writeText(url);
    alert('Lien copié !');
}

function editPoll(poll) {
    sessionStorage.setItem('editPoll', JSON.stringify(poll));
    window.location.href = `/polls/${poll.id}/edit`;
}
</script>

<template>
    <main class="min-h-screen p-6 max-w-3xl mx-auto">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Mes sondages</h1>
            <a href="/polls/create" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                + Nouveau sondage
            </a>
        </div>

        <p v-if="error" class="text-red-400 mb-4">{{ error }}</p>

        <p v-if="polls.length === 0 && !error" class="text-gray-400">
            Vous n'avez pas encore de sondage.
        </p>

        <ul class="space-y-4">
            <li
                v-for="poll in polls"
                :key="poll.id"
                class="bg-gray-800 rounded p-4 flex justify-between items-start"
            >
                <div>
                    <p class="font-semibold text-white">{{ poll.question }}</p>
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

                <div class="flex gap-2 ml-4 flex-shrink-0">
                    <button
                        @click="copyShareLink(poll.secret_token)"
                        class="bg-blue-600 text-white text-sm px-3 py-1 rounded hover:bg-blue-700"
                    >
                        Copier lien
                    </button>

                    <button
                        @click="editPoll(poll)"
                        class="bg-gray-600 text-white text-sm px-3 py-1 rounded hover:bg-gray-500"
                    >
                        Modifier
                    </button>

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
