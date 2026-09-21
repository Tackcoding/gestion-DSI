{{-- Etat vide : une phrase qui dit quoi faire.
     Dans un tableau : <x-ui.vide colspan="6">Aucun événement ne correspond à votre recherche.</x-ui.vide>
     Hors tableau    : <x-ui.vide>Aucune demande en attente.</x-ui.vide> --}}
@props(['colspan' => null])
@if ($colspan)
    <tr>
        <td colspan="{{ $colspan }}" class="vide">{{ $slot }}</td>
    </tr>
@else
    <div class="vide">{{ $slot }}</div>
@endif
