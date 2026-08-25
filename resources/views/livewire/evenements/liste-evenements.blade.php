<div class="space-y-4">

    @if (session('message'))
        <div class="rounded-md bg-green-50 border border-green-200 p-3 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    @if (session('erreur'))
        <div class="rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-800">
            {{ session('erreur') }}
        </div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            <input type="search" wire:model.live.debounce.300ms="recherche"
                   placeholder="Intitule ou lieu..."
                   class="w-full sm:max-w-xs rounded-md border-gray-300 shadow-sm">

            <select wire:model.live="filtreStatut" class="rounded-md border-gray-300 shadow-sm">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="ouvrirCreation"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Nouvel evenement
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Evenement</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Periode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Demandeur</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase text-gray-500">Couv.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($evenements as $evenement)
                    <tr wire:key="evenement-{{ $evenement->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $evenement->intitule }}</div>
                            @if ($evenement->lieu)
                                <div class="text-sm text-gray-500">{{ $evenement->lieu }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $evenement->date_debut->format('d/m/Y') }}
                            @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                                &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $evenement->demandeur->nom }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm text-gray-700">
                            {{ $evenement->couvertures_count }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                @class([
                                    'bg-gray-100 text-gray-800'   => $evenement->statut->value === 'brouillon',
                                    'bg-blue-100 text-blue-800'   => $evenement->statut->value === 'valide',
                                    'bg-amber-100 text-amber-800' => $evenement->statut->value === 'en_cours',
                                    'bg-green-100 text-green-800' => $evenement->statut->value === 'termine',
                                    'bg-red-100 text-red-800'     => $evenement->statut->value === 'annule',
                                ])">
                                {{ $evenement->statut->libelle() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-sm whitespace-nowrap">
                            <a href="{{ route('evenements.show', $evenement) }}"
                               class="text-gray-700 hover:text-gray-900">Detail</a>

                            @if ($evenement->statut->value === 'brouillon')
                                <button wire:click="valider({{ $evenement->id }})"
                                        class="ml-3 text-green-600 hover:text-green-900">Valider</button>
                            @endif

                            <button wire:click="ouvrirEdition({{ $evenement->id }})"
                                    class="ml-3 text-indigo-600 hover:text-indigo-900">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $evenement->id }})"
                                    class="ml-3 text-red-600 hover:text-red-900">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                            Aucun evenement.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $evenements->links() }}</div>

    @if ($modaleOuverte)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ $evenementId ? 'Modifier l\'evenement' : 'Nouvel evenement' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Intitule</label>
                        <input type="text" wire:model="intitule"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @error('intitule') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lieu</label>
                        <input type="text" wire:model="lieu"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de debut</label>
                            <input type="date" wire:model="date_debut"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('date_debut') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date de fin</label>
                            <input type="date" wire:model="date_fin"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('date_fin') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Demandeur</label>
                        <select wire:model="demandeur_id" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">&mdash; Choisir &mdash;</option>
                            @foreach ($agents as $a)
                                <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                            @endforeach
                        </select>
                        @error('demandeur_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model="description" rows="3"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="enregistrer"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($suppressionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-gray-600">
                    L'evenement sera retire des listes. Suppression logique, reversible.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionId', null)"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">
                        Annuler
                    </button>
                    <button wire:click="supprimer"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-700">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
