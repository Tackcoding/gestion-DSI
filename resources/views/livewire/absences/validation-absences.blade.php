<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="barre-outils">
        <h2 class="titre text-lg">Demandes en attente</h2>
        @if (method_exists($enAttente, 'total'))
            <span class="barre-outils-compte">
                {{ $enAttente->total() }} {{ $enAttente->total() > 1 ? 'demandes' : 'demande' }}
            </span>
        @endif
    </div>

    <x-ui.tableau :colonnes="['Agent', 'Type', 'Période', 'Jours' => 'tableau-nombre', 'Motif', 'Décision' => 'tableau-actions']">
        @forelse ($enAttente as $demande)
            <tr wire:key="val-{{ $demande->id }}">
                <td>
                    <span class="tableau-titre">{{ $demande->agent->nom }}</span>
                    <span class="tableau-secondaire">
                        {{ $demande->agent->prenom }} &middot; {{ $demande->agent->fonction->libelle }}
                    </span>
                </td>

                <td>{{ $demande->type->libelle }}</td>

                <td class="tableau-periode">
                    {{ $demande->date_debut->format('d/m/Y') }}
                    @if (! $demande->date_debut->isSameDay($demande->date_fin))
                        &rarr; {{ $demande->date_fin->format('d/m/Y') }}
                    @endif
                </td>

                <td class="tableau-nombre">
                    {{ rtrim(rtrim(number_format($demande->nb_jours, 1, ',', ''), '0'), ',') }}
                </td>

                <td class="text-sm text-[var(--midsp-gris)]">
                    {{ $demande->motif ?: '—' }}
                    @if ($demande->justificatif_path)
                        <a href="{{ Storage::url($demande->justificatif_path) }}" target="_blank" rel="noopener" class="lien block text-sm">
                            Justificatif
                        </a>
                    @endif
                </td>

                <td class="tableau-actions">
                    <x-ui.action icone="coche" libelle="Valider" wire:click="valider({{ $demande->id }})" texte />
                    <x-ui.action icone="fermer" libelle="Refuser" wire:click="ouvrirRefus({{ $demande->id }})" texte danger />
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="6">
                <p>Aucune demande en attente.</p>
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $enAttente->links('pagination.midsp') }}

    {{-- Refus --}}
    @if ($refusId)
        <x-ui.modale titre="Refuser la demande" sous-titre="Le motif sera visible par l'agent." largeur="md" fermer="$set('refusId', null)">
            <x-ui.champ libelle="Motif du refus" type="textarea" rows="3" wire:model="motif_refus" :erreur="$errors->first('motif_refus')" required />

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('refusId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" wire:click="refuser">Refuser</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
