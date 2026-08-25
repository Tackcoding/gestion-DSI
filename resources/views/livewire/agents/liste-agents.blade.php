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
                   placeholder="Nom, prenom ou IM..."
                   class="champ sm:max-w-xs">

            <select wire:model.live="filtreFonction"
                    class="champ sm:w-auto">
                <option value="">Toutes les fonctions</option>
                @foreach ($fonctions as $f)
                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                @endforeach
            </select>
        </div>

        <button wire:click="ouvrirCreation"
                class="btn btn-principal">
            + Nouvel agent
        </button>
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th >IM</th>
                    <th >Agent</th>
                    <th >Fonction</th>
                    <th >Service</th>
                    <th >Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($agents as $agent)
                    <tr wire:key="agent-{{ $agent->id }}">
                        <td class="text-[var(--gris)]">
                            {{ $agent->im ?? '—' }}
                        </td>
                        <td >
                            <div class="font-medium">{{ $agent->nom }}</div>
                            <div class="text-sm text-[var(--gris)]">{{ $agent->prenom }}</div>
                        </td>
                        <td class="text-[var(--gris)]">{{ $agent->fonction->libelle }}</td>
                        <td class="text-[var(--gris)]">{{ $agent->service->code }}</td>
                        <td >{{ $agent->actif ? 'Actif' : 'Inactif' }}</td>
                        <td class="text-right whitespace-nowrap">
                            <button wire:click="ouvrirEdition({{ $agent->id }})"
                                    class="lien-action">Modifier</button>
                            <button wire:click="confirmerSuppression({{ $agent->id }})"
                                    class="lien-alerte ms-4">Supprimer</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--gris)]">
                            Aucun agent trouve.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $agents->links() }}</div>

    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">
                    {{ $agentId ? 'Modifier l\'agent' : 'Nouvel agent' }}
                </h3>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Nom</label>
                            <input type="text" wire:model="nom"
                                   class="champ mt-1">
                            @error('nom') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Prenom</label>
                            <input type="text" wire:model="prenom"
                                   class="champ mt-1">
                            @error('prenom') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">IM</label>
                            <input type="text" wire:model="im"
                                   class="champ mt-1">
                            @error('im') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Telephone</label>
                            <input type="text" wire:model="telephone"
                                   class="champ mt-1">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Fonction</label>
                            <select wire:model="fonction_id"
                                    class="champ mt-1">
                                <option value="">— Choisir —</option>
                                @foreach ($fonctions as $f)
                                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                                @endforeach
                            </select>
                            @error('fonction_id') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="actif" class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                        <span class="text-sm text-[var(--gris)]">Agent actif</span>
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

    @if ($suppressionId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Confirmer la suppression</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    L'agent disparaitra des listes. Cette suppression est logique et reversible.
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