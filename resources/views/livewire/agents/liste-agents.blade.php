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
                            placeholder="Nom, prénom ou IM…"
                            libelle="Rechercher un agent" />

            <x-ui.selection wire:model.live="filtreFonction" libelle="Filtrer par fonction">
                <option value="">Toutes les fonctions</option>
                @foreach ($fonctions as $f)
                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                @endforeach
            </x-ui.selection>

            @if (method_exists($agents, 'total'))
                <span class="barre-outils-compte">
                    {{ $agents->total() }} {{ $agents->total() > 1 ? 'agents' : 'agent' }}
                </span>
            @endif
        </div>

        <div class="barre-outils-actions">
            <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                Nouvel agent
            </x-ui.bouton>
        </div>
    </div>

    <x-ui.tableau :colonnes="['IM', 'Agent', 'Fonction', 'Service', 'Statut', 'Actions' => 'tableau-actions']">
        @forelse ($agents as $agent)
            <tr wire:key="agent-{{ $agent->id }}">
                <td class="tableau-periode text-[var(--midsp-gris)]">{{ $agent->im ?? '—' }}</td>

                <td>
                    <span class="tableau-titre">{{ $agent->nom }}</span>
                    <span class="tableau-secondaire">{{ $agent->prenom }}</span>
                </td>

                <td>{{ $agent->fonction->libelle }}</td>
                <td>{{ $agent->service->code }}</td>

                <td>
                    <x-ui.badge :etat="$agent->actif ? 'ok' : 'neutre'">
                        {{ $agent->actif ? 'Actif' : 'Inactif' }}
                    </x-ui.badge>
                </td>

                <td class="tableau-actions">
                    <x-ui.action icone="crayon" libelle="Modifier" wire:click="ouvrirEdition({{ $agent->id }})" texte />
                    <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppression({{ $agent->id }})" danger />
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="6">
                @if ($recherche || $filtreFonction)
                    <p>Aucun agent ne correspond à votre recherche.</p>
                @else
                    <p>Aucun agent enregistré pour le moment.</p>
                    <x-ui.bouton variante="secondaire" icone="plus" wire:click="ouvrirCreation">
                        Ajouter le premier agent
                    </x-ui.bouton>
                @endif
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $agents->links('pagination.midsp') }}

    {{-- Création / modification --}}
    @if ($modaleOuverte)
        <x-ui.modale :titre="$agentId ? 'Modifier l\'agent' : 'Nouvel agent'"
                     fermer="$set('modaleOuverte', false)">

            <div class="grille-2">
                <x-ui.champ libelle="Nom" wire:model="nom" :erreur="$errors->first('nom')" required />
                <x-ui.champ libelle="Prénom" wire:model="prenom" :erreur="$errors->first('prenom')" required />
            </div>

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="IM" wire:model="im" :erreur="$errors->first('im')" aide="Immatriculation, si l'agent en a une" />
                <x-ui.champ libelle="Téléphone" type="tel" wire:model="telephone" />
            </div>

            <div class="grille-2 mt-4">
                <div class="champ-bloc">
                    <label for="agent-fonction" class="libelle">Fonction <span aria-hidden="true">*</span></label>
                    <x-ui.selection id="agent-fonction" wire:model="fonction_id" :class="$errors->has('fonction_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($fonctions as $f)
                            <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('fonction_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-2">
                <x-ui.case wire:model="actif">Agent actif</x-ui.case>
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Enregistrer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <x-ui.modale titre="Supprimer cet agent ?" largeur="md" fermer="$set('suppressionId', null)">
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
