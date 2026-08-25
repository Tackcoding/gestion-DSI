<div class="space-y-4">

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

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            <input type="search" wire:model.live.debounce.300ms="recherche"
                   placeholder="Intitule ou lieu..."
                   class="champ sm:max-w-xs">

            <select wire:model.live="filtreStatut" class="champ sm:w-auto">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="ouvrirCreation"
                class="btn btn-principal">
            + Nouvel evenement
        </button>
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th >Evenement</th>
                    <th >Periode</th>
                    <th >Demandeur</th>
                    <th class="text-center">Couv.</th>
                    <th >Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evenements as $evenement)
                    <tr wire:key="evenement-{{ $evenement->id }}">
                        <td >
                            <div class="font-medium">{{ $evenement->intitule }}</div>
                            @if ($evenement->lieu)
                                <div class="text-sm text-[var(--gris)]">{{ $evenement->lieu }}</div>
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">
                            {{ $evenement->date_debut->format('d/m/Y') }}
                            @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                                &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">
                            {{ $evenement->demandeur->nom }}
                        </td>
                        <td class="text-center text-[var(--gris)]">
                            {{ $evenement->couvertures_count }}
                        </td>
                        <td >
                            <span @class([
                                    'badge',
                                    'badge-neutre'  => in_array($evenement->statut->value, ['brouillon', 'annule']),
                                    'badge-attente' => $evenement->statut->value === 'en_cours',
                                    'badge-ok'      => in_array($evenement->statut->value, ['valide', 'termine']),
                                ])>
                                {{ $evenement->statut->libelle() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            <a href="{{ route('evenements.show', $evenement) }}"
                               class="lien-action">Detail</a>

                            @if ($evenement->statut->value === 'brouillon')
                                <button wire:click="valider({{ $evenement->id }})"
                                        class="ml-3 text-green-600 hover:text-green-900">Valider</button>
                            @endif

                            <button wire:click="ouvrirEdition({{ $evenement->id }})"
                                    class="lien-action ms-4">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $evenement->id }})"
                                    class="lien-alerte ms-4">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--gris)]">
                            Aucun evenement.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $evenements->links() }}</div>

    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">
                    {{ $evenementId ? 'Modifier l\'evenement' : 'Nouvel evenement' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="libelle">Intitule</label>
                        <input type="text" wire:model="intitule"
                               class="champ mt-1">
                        @error('intitule') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="libelle">Lieu</label>
                        <input type="text" wire:model="lieu"
                               class="champ mt-1">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Date de debut</label>
                            <input type="date" wire:model="date_debut"
                                   class="champ mt-1">
                            @error('date_debut') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Date de fin</label>
                            <input type="date" wire:model="date_fin"
                                   class="champ mt-1">
                            @error('date_fin') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="libelle">Demandeur</label>
                        <select wire:model="demandeur_id" class="champ mt-1">
                            <option value="">&mdash; Choisir &mdash;</option>
                            @foreach ($agents as $a)
                                <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                            @endforeach
                        </select>
                        @error('demandeur_id') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="libelle">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="champ mt-1"></textarea>
                    </div>
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

    @if ($suppressionId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    L'evenement sera retire des listes. Suppression logique, reversible.
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
