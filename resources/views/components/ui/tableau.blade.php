{{-- Tableau "page de registre".
     Usage simple, les colonnes en tableau :
       <x-ui.tableau :colonnes="['Événement', 'Période', 'Demandeur', 'Équipe' => 'tableau-nombre', 'Statut', 'Actions' => 'tableau-actions']">
           @foreach ($evenements as $e) <tr>...</tr> @endforeach
       </x-ui.tableau>
     Une cle textuelle = libelle => classe de la colonne (nombre a droite, actions a droite).
     Usage avance : remplacer :colonnes par un slot <x-slot:entete> contenant les <th>. --}}
@props(['colonnes' => []])
<div class="tableau-cadre">
    <table {{ $attributes->merge(['class' => 'tableau']) }}>
        @if (isset($entete) || count($colonnes))
            <thead>
                <tr>
                    @isset($entete)
                        {{ $entete }}
                    @else
                        @foreach ($colonnes as $cle => $valeur)
                            @if (is_string($cle))
                                <th scope="col" class="{{ $valeur }}">{{ $cle }}</th>
                            @else
                                <th scope="col">{{ $valeur }}</th>
                            @endif
                        @endforeach
                    @endisset
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
