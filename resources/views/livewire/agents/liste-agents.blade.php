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
                   placeholder="Nom, prenom ou IM..."
                   class="w-full sm:max-w-xs rounded-md border-gray-300 shadow-sm">

            <select wire:model.live="filtreFonction"
                    class="rounded-md border-gray-300 shadow-sm">
                <option value="">Toutes les fonctions</option>
                @foreach ($fonctions as $f)
                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="ouvrirCreation"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            + Nouvel agent
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">IM</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Agent</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Fonction</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($agents as $agent)
                    <tr wire:key="agent-{{ $agent->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $agent->im ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $agent->nom }}</div>
                            <div class="text-sm text-gray-500">{{ $agent->prenom }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $agent->fonction->libelle }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $agent->service->code }}</td>
                        <td class="px-4 py-3 text-sm">{{ $agent->actif ? 'Actif' : 'Inactif' }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            <button wire:click="ouvrirEdition({{ $agent->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $agent->id }})"
                                    class="ml-3 text-red-600 hover:text-red-900">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">
                            Aucun agent trouve.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agents->links() }}</div>

    @if ($modaleOuverte)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ $agentId ? 'Modifier l\'agent' : 'Nouvel agent' }}
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom</label>
                            <input type="text" wire:model="nom"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('nom') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prenom</label>
                            <input type="text" wire:model="prenom"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('prenom') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">IM</label>
                            <input type="text" wire:model="im"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                            @error('im') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Telephone</label>
                            <input type="text" wire:model="telephone"
                                   class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fonction</label>
                            <select wire:model="fonction_id"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">— Choisir —</option>
                                @foreach ($fonctions as $f)
                                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                                @endforeach
                            </select>
                            @error('fonction_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>
                        </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="actif" class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Agent actif</span>
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

    @if ($suppressionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-gray-600">
                    L'agent disparaitra des listes. Cette suppression est logique et reversible.
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