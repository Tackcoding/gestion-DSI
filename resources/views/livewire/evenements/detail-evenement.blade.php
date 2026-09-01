<div class="space-y-6">

    @if (session('message'))
        <div class="message">
            {{ session('message') }}
        </div>
    @endif

    {{-- En-tete de l'evenement --}}
    <div class="carte p-5">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="titre text-lg">{{ $evenement->intitule }}</h3>
                <p class="mt-1 text-sm text-[var(--gris)]">
                    {{ $evenement->date_debut->format('d/m/Y') }}
                    @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                        &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                    @endif
                    @if ($evenement->lieu) &middot; {{ $evenement->lieu }} @endif
                </p>
                @if ($evenement->description)
                    <p class="mt-2 text-sm text-[var(--gris)]">{{ $evenement->description }}</p>
                @endif
            </div>
            <span class="badge badge-neutre">
                {{ $evenement->statut->libelle() }}
            </span>
        </div>
    </div>

    {{-- Couvertures --}}
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h4 class="titre text-lg">Couvertures</h4>
            @can('gerer-evenements')
            <button wire:click="ouvrirCreationCouverture"
                    class="btn btn-principal">
                + Ajouter une couverture
            </button>
            @endcan
        </div>

        <div class="space-y-3">
            @forelse ($couvertures as $couverture)
                <div wire:key="couv-{{ $couverture->id }}"
                     class="carte p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-medium">
                                {{ $couverture->date->format('d/m/Y') }}
                            </div>
                            <div class="mt-1 text-sm text-[var(--gris)]">
                                @if ($couverture->heure_depart)
                                    Depart {{ substr($couverture->heure_depart, 0, 5) }}
                                    @if ($couverture->lieu_depart) &mdash; {{ $couverture->lieu_depart }} @endif
                                @endif
                                @if ($couverture->heure_retour)
                                    &middot; Retour {{ substr($couverture->heure_retour, 0, 5) }}
                                    @if ($couverture->lieu_retour) &mdash; {{ $couverture->lieu_retour }} @endif
                                @endif
                            </div>
                        </div>
                       @can('gerer-evenements')
                        <div class="flex shrink-0 gap-4 text-sm">
                            <button wire:click="ouvrirEquipe({{ $couverture->id }})"
                                    class="lien-action">Equipe</button>
                            <button wire:click="ouvrirEditionCouverture({{ $couverture->id }})"
                                    class="lien-action">Modifier</button>
                            <button wire:click="confirmerSuppressionCouverture({{ $couverture->id }})"
                                    class="lien-alerte">Supprimer</button>
                        </div>
                    @endcan
                    </div>

                    {{-- Equipe mobilisee --}}
                    <div class="mt-3 border-t border-[var(--trait)] pt-3">
                        @if ($couverture->agents->isEmpty())
                            <p class="text-sm italic text-[var(--gris)]">Aucun agent affecte.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($couverture->agents as $agent)
                                    <span class="badge badge-ok">
                                        {{ $agent->nom }} {{ $agent->prenom }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="carte border-dashed p-10 text-center text-sm text-[var(--gris)]">
                    Aucune couverture pour cet evenement.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modale couverture --}}
    @if ($modaleCouverture)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">
                    {{ $couvertureId ? 'Modifier la couverture' : 'Nouvelle couverture' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="libelle">Date</label>
                        <input type="date" wire:model="date"
                               class="champ mt-1">
                        @error('date') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Heure de depart</label>
                            <input type="time" wire:model="heure_depart"
                                   class="champ mt-1">
                        </div>
                        <div>
                            <label class="libelle">Lieu de depart</label>
                            <input type="text" wire:model="lieu_depart"
                                   class="champ mt-1">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Heure de retour</label>
                            <input type="time" wire:model="heure_retour"
                                   class="champ mt-1">
                        </div>
                        <div>
                            <label class="libelle">Lieu de retour</label>
                            <input type="text" wire:model="lieu_retour"
                                   class="champ mt-1">
                        </div>
                    </div>

                    <div>
                        <label class="libelle">Observation</label>
                        <textarea wire:model="observation" rows="2"
                                  class="champ mt-1"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleCouverture', false)"
                            class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrerCouverture"
                            class="btn btn-principal">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Affectation d'equipe --}}
    @if ($couvertureEquipeId && $couvertureEnCours)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre text-lg">
                    Equipe du {{ $couvertureEnCours->date->format('d/m/Y') }}
                </h3>
                <p class="mt-1 text-sm text-[var(--gris)]">
                    Seuls les agents disponibles ce jour-la sont proposes :
                    les agents en absence validee ou deja mobilises sur une autre
                    couverture n'apparaissent pas.
                </p>

                <div class="mt-4 max-h-72 space-y-1 overflow-y-auto rounded-md border border-[var(--trait)] p-2">
                    @forelse ($agentsDisponibles as $agent)
                        <label wire:key="dispo-{{ $agent->id }}"
                               class="flex items-center gap-3 rounded px-2 py-1.5 transition hover:bg-[var(--fond)]">
                            <input type="checkbox" wire:model="agentsSelectionnes"
                                   value="{{ $agent->id }}" class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                            <span class="text-sm">{{ $agent->nom }} {{ $agent->prenom }}</span>
                            <span class="ms-auto text-xs text-[var(--gris)]">{{ $agent->fonction->libelle }}</span>
                        </label>
                    @empty
                        <p class="px-2 py-5 text-center text-sm text-[var(--gris)]">
                            Aucun agent disponible a cette date.
                        </p>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('couvertureEquipeId', null)"
                            class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrerEquipe"
                            class="btn btn-principal">
                        Enregistrer l'equipe
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation suppression couverture --}}
    @if ($suppressionCouvertureId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Supprimer cette couverture ?</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    Les affectations d'agents associees seront egalement supprimees.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionCouvertureId', null)"
                            class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="supprimerCouverture"
                            class="btn btn-alerte">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
