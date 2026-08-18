<div class="space-y-4">

    {{-- Messages flash --}}
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

    {{-- Barre d'outils --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 gap-2">
            {{-- .live.debounce : filtre pendant la frappe, sans requete a chaque caractere --}}
            <input
                type="search"
                wire:model.live.debounce.300ms="recherche"
                placeholder="Rechercher un materiel..."
                class="w-full sm:max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >

            <select
                wire:model.live="filtreEtat"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Tous les etats</option>
                @foreach ($etats as $e)
                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                @endforeach
            </select>
        </div>

        <button
            wire:click="ouvrirCreation"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        >
            + Nouveau materiel
        </button>
    </div>

    {{-- Tableau --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Designation</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Quantite</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Etat</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($materiels as $materiel)
                    <tr wire:key="materiel-{{ $materiel->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $materiel->designation }}</div>
                            @if ($materiel->description)
                                <div class="text-sm text-gray-500">{{ $materiel->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $materiel->quantite_totale }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                                @class([
                                    'bg-green-100 text-green-800' => $materiel->etat->value === 'bon',
                                    'bg-amber-100 text-amber-800' => $materiel->etat->value === 'moyen',
                                    'bg-red-100 text-red-800'     => $materiel->etat->value === 'hors_service',
                                ])">
                                {{ $materiel->etat->libelle() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            {{ $materiel->actif ? 'Actif' : 'Inactif' }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm">
                            <button wire:click="ouvrirEdition({{ $materiel->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $materiel->id }})"
                                    class="ml-3 text-red-600 hover:text-red-900">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ $materielId ? 'Modifier le materiel' : 'Nouveau materiel' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Designation</label>
                        <input type="text" wire:model="designation"
                               class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        @error('designation')
                            <span class="text-sm text-red-600">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model="description" rows="2"
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantite totale</label>
                            <input type="number" min="1" wire:model="quantite_totale"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('quantite_totale')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Etat</label>
                            <select wire:model="etat" class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                @foreach ($etats as $e)
                                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="actif" class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Materiel actif</span>
                    </label>
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

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Cette action est reversible (suppression logique), mais le materiel
                    disparaitra des listes.
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
