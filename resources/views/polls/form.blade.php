{{--
    Vue Blade pour le formulaire de création/édition d'un sondage.
    Elle utilise le layout par défaut (header, footer, navigation).
--}}
<x-default-layout>

    {{-- On injecte le fichier JS de notre app Vue formulaire --}}
    <x-slot:scripts>
        @vite(['resources/js/poll-form.js'])
    </x-slot>

    {{-- L'élément sur lequel Vue va se monter --}}
    <div id="app"></div>

</x-default-layout>
