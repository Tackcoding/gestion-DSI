<div class="space-y-6">

    @if (session('message'))
        <div class="rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- En-tete de l'evenement --}}
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $evenement->intitule }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $evenement->date_debut->format('d/m/Y') }}
                    @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                        &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                    @endif
                    @if ($evenement->lieu) &middot; {{ $evenement->lieu }} @endif
                </p>
                @if ($evenement->description)
                    <p class="mt-2 text-sm text-gray-600">{{ $evenement->description }}</p>
                @endif
            </div>
            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-800">
                {{ $evenement->statut->libelle() }}
            </span>
        </div>
    </div>

    {{-- Couvertures --}}
    <div>
        <div class="mb-3 flex items-center justify-between">
            <h4 class="font-semibold text-gray-900">Couvertures</h4>
            <button wire:click="ouvrirCreationCouverture"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700">
                + Ajouter une couverture
            </button>
        </div>

        <div class="space-y-3">
            @forelse ($couvertures as $couverture)
                <div wire:key="couv-{{ $couverture->id }}"
                     class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-medium text-gray-900">
                                {{ $couverture->date->format('d/m/Y') }}
                            </div>
                            <div class="mt-1 text-sm text-gray-600">
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
                        <div class="text-sm whitespace-nowrap">
                            <button wire:click="ouvrirEquipe({{ $couverture->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">Equipe</button>
                            <button wire:click="ouvrirEditionCouverture({{ $couverture->id }})"
                                    class="ml-3 text-gray-700 hover:text-gray-900">Modifier</button>
                            <button wire:click="confirmerSuppressionCouverture({{ $couverture->id }})"
                                    class="ml-3 text-red-600 hover:text-red-900">Supprimer</button>
                        </div>
                    </div>

                    {{-- Equipe mobilisee --}}
                    <div class="mt-3 border-t border-gray-100 pt-3">
                        @if ($couverture->agents->isEmpty())
                            <p class="text-sm italic text-gray-500">Aucun agent affecte.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach ($couverture->agents as $agent)
                                    <span class="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs text-indigo-800">
                                        {{ $agent->nom }} {{ $agent->prenom }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                    Aucune couverture pour cet evenement.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modale couverture --}}
    @if ($modaleCouverture)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ $couvertureId ? 'Modifier la couverture' : 'Nouvelle couverture' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" wire:model="date"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @error('date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Heure de depart</label>
                            <input type="time" wire:model="heure_depart"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lieu de depart</label>
                            <input type="text" wire:model="lieu_depart"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Heure de retour</label>
                            <input type="time" wire:model="heure_retour"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lieu de retour</label>
                            <input type="text" wire:model="lieu_retour"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Observation</label>
                        <textarea wire:model="observation" rows="2"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleCouverture', false)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="enregistrerCouverture"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Affectation d'equipe --}}
    @if ($couvertureEquipeId && $couvertureEnCours)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">
                    Equipe du {{ $couvertureEnCours->date->format('d/m/Y') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Seuls les agents disponibles ce jour-la sont proposes :
                    les agents en absence validee ou deja mobilises sur une autre
                    couverture n'apparaissent pas.
                </p>

                <div class="mt-4 max-h-72 space-y-1 overflow-y-auto rounded-md border border-gray-200 p-2">
                    @forelse ($agentsDisponibles as $agent)
                        <label wire:key="dispo-{{ $agent->id }}"
                               class="flex items-center gap-3 rounded px-2 py-1.5 hover:bg-gray-50">
                            <input type="checkbox" wire:model="agentsSelectionnes"
                                   value="{{ $agent->id }}" class="rounded border-gray-300">
                            <span class="text-sm text-gray-900">{{ $agent->nom }} {{ $agent->prenom }}</span>
                            <span class="ml-auto text-xs text-gray-500">{{ $agent->fonction->libelle }}</span>
                        </label>
                    @empty
                        <p class="px-2 py-4 text-center text-sm text-gray-500">
                            Aucun agent disponible a cette date.
                        </p>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('couvertureEquipeId', null)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="enregistrerEquipe"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                        Enregistrer l'equipe
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation suppression couverture --}}
    @if ($suppressionCouvertureId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Supprimer cette couverture ?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Les affectations d'agents associees seront egalement supprimees.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionCouvertureId', null)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="supprimerCouverture"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
