{{--
    Cette vue Blade est la page qui héberge notre app Vue.js du dashboard.
    Elle utilise le layout par défaut (header, footer, navigation).
--}}
<x-default-layout>

    {{-- On injecte le fichier JS de notre app Vue dans le slot "scripts" du layout --}}
    <x-slot:scripts>
        @vite(['resources/js/poll-dashboard.js'])
    </x-slot>

    {{-- L'élément sur lequel Vue va se monter --}}
    <div id="app"></div>

</x-default-layout>
