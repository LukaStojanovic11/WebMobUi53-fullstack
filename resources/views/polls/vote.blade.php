{{--
    Vue Blade pour la page de vote.
    On utilise le layout par défaut pour avoir le fond sombre
    cohérent avec le reste de l'application.
--}}
<x-default-layout>

    <x-slot:scripts>
        @vite(['resources/js/poll-vote.js'])
    </x-slot>

    <div id="app"></div>

</x-default-layout>
