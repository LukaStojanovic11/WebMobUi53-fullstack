<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from './composables/useFetchApi';

const urlParts = window.location.pathname.split('/');
const editIndex = urlParts.indexOf('edit');
const pollId = editIndex > 0 ? urlParts[editIndex - 1] : null;
const isEditing = pollId !== null;

const { fetchApi } = useFetchApi();

const question = ref('');
const options = ref(['', '']);
const allowMultipleChoices = ref(false);
const resultsPublic = ref(false);
const duration = ref('');
const isDraft = ref(true);
const error = ref(null);

// En mode édition, on lit les données depuis sessionStorage
onMounted(() => {
    if (!isEditing) return;

    const stored = sessionStorage.getItem('editPoll');
    if (!stored) {
        error.value = 'Impossible de charger le sondage.';
        return;
    }

    const poll = JSON.parse(stored);
    question.value = poll.question;
    options.value = poll.options.length > 0 ? poll.options.map(o => o.label) : ['', ''];
    allowMultipleChoices.value = poll.allow_multiple_choices;
    resultsPublic.value = poll.results_public;
    duration.value = poll.duration ?? '';
    isDraft.value = poll.is_draft;

    sessionStorage.removeItem('editPoll');
});

function addOption() {
    options.value.push('');
}

function removeOption(index) {
    if (options.value.length <= 2) return;
    options.value.splice(index, 1);
}

async function submit() {
    error.value = null;

    if (!question.value.trim()) {
        error.value = 'La question est obligatoire.';
        return;
    }

    const filledOptions = options.value.filter(o => o.trim() !== '');
    if (filledOptions.length < 2) {
        error.value = 'Il faut au moins 2 options.';
        return;
    }

    try {
        if (isEditing) {
            // 1. On met à jour les infos du sondage
            await fetchApi({
                url: `/polls/${pollId}`,
                method: 'PUT',
                data: {
                    question: question.value,
                    allow_multiple_choices: allowMultipleChoices.value,
                    results_public: resultsPublic.value,
                    duration: duration.value !== '' ? parseInt(duration.value) : null,
                    is_draft: isDraft.value,
                },
            });

            // 2. On récupère les options actuelles en base
            const existing = await fetchApi({ url: `/polls/${pollId}/show` });
            const existingOptions = existing.options;

            // 3. On supprime les options en trop si on en a moins qu'avant
            for (let i = filledOptions.length; i < existingOptions.length; i++) {
                await fetchApi({
                    url: `/polls/${pollId}/options/${existingOptions[i].id}`,
                    method: 'DELETE'
                });
            }

            // 4. On met à jour les options existantes
            for (let i = 0; i < Math.min(filledOptions.length, existingOptions.length); i++) {
                await fetchApi({
                    url: `/polls/${pollId}/options/${existingOptions[i].id}`,
                    method: 'PUT',
                    data: { label: filledOptions[i] },
                });
            }

            // 5. On crée les nouvelles options si on en a plus qu'avant
            for (let i = existingOptions.length; i < filledOptions.length; i++) {
                await fetchApi({
                    url: `/polls/${pollId}/options`,
                    method: 'POST',
                    data: { label: filledOptions[i] },
                });
            }

        } else {
            // Création du sondage
            const poll = await fetchApi({
                url: '/polls',
                method: 'POST',
                data: {
                    question: question.value,
                    allow_multiple_choices: allowMultipleChoices.value,
                    results_public: resultsPublic.value,
                    duration: duration.value !== '' ? parseInt(duration.value) : null,
                    is_draft: isDraft.value,
                },
            });

            // On crée les options
            for (const label of filledOptions) {
                await fetchApi({
                    url: `/polls/${poll.id}/options`,
                    method: 'POST',
                    data: { label },
                });
            }
        }

        window.location.href = '/polls/dashboard';

    } catch (err) {
        if (err.status === 401) window.location.href = '/auth/login';
        else if (err.data?.errors) {
            const firstError = Object.values(err.data.errors)[0][0];
            error.value = firstError;
        } else {
            error.value = 'Une erreur est survenue.';
        }
    }
}
</script>

<template>
    <main class="min-h-screen p-6 max-w-2xl mx-auto">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">
                {{ isEditing ? 'Modifier le sondage' : 'Nouveau sondage' }}
            </h1>
            <a href="/polls/dashboard" class="text-gray-400 hover:text-white">
                ← Retour
            </a>
        </div>

        <p v-if="error" class="bg-red-800 text-white p-3 rounded mb-4">{{ error }}</p>

        <div class="space-y-6">

            <div>
                <label class="block text-sm font-medium mb-1">Question *</label>
                <input
                    v-model="question"
                    type="text"
                    placeholder="Quelle est votre question ?"
                    class="w-full bg-gray-800 text-white rounded p-3 border border-gray-600 focus:outline-none focus:border-purple-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Options de réponse *</label>
                <div v-for="(option, index) in options" :key="index" class="flex gap-2 mb-2">
                    <input
                        v-model="options[index]"
                        type="text"
                        :placeholder="`Option ${index + 1}`"
                        class="flex-1 bg-gray-800 text-white rounded p-3 border border-gray-600 focus:outline-none focus:border-purple-500"
                    />
                    <button
                        @click="removeOption(index)"
                        :disabled="options.length <= 2"
                        class="bg-red-700 text-white px-3 rounded hover:bg-red-600 disabled:opacity-30"
                    >
                        ✕
                    </button>
                </div>
                <button @click="addOption" class="text-purple-400 hover:text-purple-300 text-sm mt-1">
                    + Ajouter une option
                </button>
            </div>

            <div class="space-y-3">
                <label class="block text-sm font-medium">Paramètres</label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" v-model="allowMultipleChoices" class="w-4 h-4" />
                    <span>Autoriser plusieurs choix</span>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" v-model="resultsPublic" class="w-4 h-4" />
                    <span>Résultats publics (visibles sans connexion)</span>
                </label>

                <div>
                    <label class="block text-sm mb-1">Durée (en secondes, vide = illimitée)</label>
                    <input
                        v-model="duration"
                        type="number"
                        min="1"
                        placeholder="Ex: 3600 pour 1 heure"
                        class="w-full bg-gray-800 text-white rounded p-3 border border-gray-600 focus:outline-none focus:border-purple-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Statut</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" :value="true" v-model="isDraft" />
                            <span>Brouillon</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" :value="false" v-model="isDraft" />
                            <span>Lancer maintenant</span>
                        </label>
                    </div>
                </div>
            </div>

            <button
                @click="submit"
                class="w-full bg-purple-600 text-white py-3 rounded font-semibold hover:bg-purple-700"
            >
                {{ isEditing ? 'Enregistrer les modifications' : 'Créer le sondage' }}
            </button>

        </div>
    </main>
</template>
