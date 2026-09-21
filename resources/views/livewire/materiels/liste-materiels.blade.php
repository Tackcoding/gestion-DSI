<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="barre-outils">
        <div class="barre-outils-filtres">
            <x-ui.recherche wire:model.live.debounce.300ms="recherche"
                            placeholder="Désignation…"
                            libelle="Rechercher un matériel" />

            <x-ui.selection wire:model.live="filtreEtat" libelle="Filtrer par état">
                <option value="">Tous les états</option>
                @foreach ($etats as $e)
                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                @endforeach
            </x-ui.selection>

            @if (method_exists($materiels, 'total'))
                <span class="barre-outils-compte">
                    {{ $materiels->total() }} {{ $materiels->total() > 1 ? 'références' : 'référence' }}
                </span>
            @endif
        </div>

        <div class="barre-outils-actions">
            <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                Nouveau matériel
            </x-ui.bouton>
        </div>
    </div>

    <x-ui.tableau :colonnes="['Désignation', 'Quantité' => 'tableau-nombre', 'État', 'Statut', 'Actions' => 'tableau-actions']">
        @forelse ($materiels as $materiel)
            <tr wire:key="materiel-{{ $materiel->id }}">
                <td>
                    <span class="tableau-titre">{{ $materiel->designation }}</span>
                    @if ($materiel->description)
                        <span class="tableau-secondaire">{{ $materiel->description }}</span>
                    @endif
                </td>

                <td class="tableau-nombre">{{ $materiel->quantite_totale }}</td>

                <td>
                    @php
                        $etat = match ($materiel->etat->value) {
                            'bon'          => 'ok',
                            'moyen'        => 'attente',
                            'hors_service' => 'alerte',
                            default        => 'neutre',
                        };
                    @endphp
                    <x-ui.badge :etat="$etat">{{ $materiel->etat->libelle() }}</x-ui.badge>
                </td>

                <td>
                    <x-ui.badge :etat="$materiel->actif ? 'ok' : 'neutre'">
                        {{ $materiel->actif ? 'Actif' : 'Inactif' }}
                    </x-ui.badge>
                </td>

                <td class="tableau-actions">
                    <x-ui.action icone="crayon" libelle="Modifier" wire:click="ouvrirEdition({{ $materiel->id }})" texte />
                    <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppression({{ $materiel->id }})" danger />
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="5">
                @if ($recherche || $filtreEtat)
                    <p>Aucun matériel ne correspond à votre recherche.</p>
                @else
                    <p>Aucun matériel enregistré pour le moment.</p>
                    <x-ui.bouton variante="secondaire" icone="plus" wire:click="ouvrirCreation">
                        Ajouter la première référence
                    </x-ui.bouton>
                @endif
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $materiels->links('pagination.midsp') }}

    {{-- Création / modification --}}
    @if ($modaleOuverte)
        <x-ui.modale :titre="$materielId ? 'Modifier le matériel' : 'Nouveau matériel'"
                     fermer="$set('modaleOuverte', false)">

            <x-ui.champ libelle="Désignation" wire:model="designation" :erreur="$errors->first('designation')" required />

            <x-ui.champ libelle="Description" type="textarea" rows="2" wire:model="description" />

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Quantité totale" type="number" min="1" wire:model="quantite_totale" :erreur="$errors->first('quantite_totale')" required />

                <div class="champ-bloc">
                    <label for="materiel-etat" class="libelle">État</label>
                    <x-ui.selection id="materiel-etat" wire:model="etat">
                        @foreach ($etats as $e)
                            <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                        @endforeach
                    </x-ui.selection>
                </div>
            </div>

            <div class="mt-2">
                <x-ui.case wire:model="actif">Matériel actif</x-ui.case>
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Enregistrer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <x-ui.modale titre="Supprimer ce matériel ?" largeur="md" fermer="$set('suppressionId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Il disparaîtra des listes. La suppression est logique et réversible.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" icone="corbeille" wire:click="supprimer">Supprimer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
