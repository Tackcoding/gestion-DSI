<div class="space-y-4">

    {{-- Messages flash --}}
    @if (session('message'))
        <div class="message">
            {{ session('message') }}
        </div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">
            {{ session('erreur') }}
        </div>
    @endif

    {{-- Barre d'outils --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            {{-- .live.debounce : filtre pendant la frappe, sans requete a chaque caractere --}}
            <input
                type="search"
                wire:model.live.debounce.300ms="recherche"
                placeholder="Rechercher un materiel..."
                class="champ sm:max-w-xs"
            >

            <select
                wire:model.live="filtreEtat"
                class="champ sm:w-auto"
            >
                <option value="">Tous les etats</option>
                @foreach ($etats as $e)
                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                @endforeach
            </select>
        </div>

        <button
            wire:click="ouvrirCreation"
            class="btn btn-principal"
        >
            + Nouveau materiel
        </button>
    </div>

    {{-- Tableau --}}
    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th >Designation</th>
                    <th >Quantite</th>
                    <th >Etat</th>
                    <th >Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($materiels as $materiel)
                    <tr wire:key="materiel-{{ $materiel->id }}">
                        <td >
                            <div class="font-medium">{{ $materiel->designation }}</div>
                            @if ($materiel->description)
                                <div class="text-sm text-[var(--gris)]">{{ $materiel->description }}</div>
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">{{ $materiel->quantite_totale }}</td>
                        <td >
                            <span @class([
                                    'badge',
                                    'badge-ok'      => $materiel->etat->value === 'bon',
                                    'badge-attente' => $materiel->etat->value === 'moyen',
                                    'badge-alerte'  => $materiel->etat->value === 'hors_service',
                                ])>
                                {{ $materiel->etat->libelle() }}
                            </span>
                        </td>
                        <td >
                            {{ $materiel->actif ? 'Actif' : 'Inactif' }}
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <button wire:click="ouvrirEdition({{ $materiel->id }})"
                                    class="lien-action">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $materiel->id }})"
                                    class="lien-alerte ms-4">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-[var(--gris)]">
                            Aucun materiel trouve.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $materiels->links() }}</div>

    {{-- Modale creation / edition --}}
    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">
                    {{ $materielId ? 'Modifier le materiel' : 'Nouveau materiel' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="libelle">Designation</label>
                        <input type="text" wire:model="designation"
                               class="champ mt-1">
                        @error('designation')
                            <span class="erreur">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="libelle">Description</label>
                        <textarea wire:model="description" rows="2"
                                  class="champ mt-1"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Quantite totale</label>
                            <input type="number" min="1" wire:model="quantite_totale"
                                   class="champ mt-1">
                            @error('quantite_totale')
                                <span class="erreur">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="libelle">Etat</label>
                            <select wire:model="etat" class="champ mt-1">
                                @foreach ($etats as $e)
                                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="actif" class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                        <span class="text-sm text-[var(--gris)]">Materiel actif</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)"
                            class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrer"
                            class="btn btn-principal">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    Cette action est reversible (suppression logique), mais le materiel
                    disparaitra des listes.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionId', null)"
                            class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="supprimer"
                            class="btn btn-alerte">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
