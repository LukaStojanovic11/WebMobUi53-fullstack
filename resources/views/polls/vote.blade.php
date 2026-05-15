{{--
    Vue Blade pour la page de vote.
    On utilise le layout minimal (vue-app-layout) car cette page
    doit être accessible sans être connecté (résultats publics).
--}}
<x-vue-app-layout>

    <x-slot:title>
        Sondage
    </x-slot>

    <x-slot:scripts>
        @vite(['resources/js/poll-vote.js'])
    </x-slot>

    <div id="app"></div>

</x-vue-app-layout>
