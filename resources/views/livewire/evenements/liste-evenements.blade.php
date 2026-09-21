<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    {{-- Barre d'outils : filtres à gauche, action principale à droite --}}
    <div class="barre-outils">
        <div class="barre-outils-filtres">
            <x-ui.recherche wire:model.live.debounce.300ms="recherche"
                            placeholder="Intitulé ou lieu…"
                            libelle="Rechercher un événement" />

            <x-ui.selection wire:model.live="filtreStatut" libelle="Filtrer par statut">
                <option value="">Tous les statuts</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </x-ui.selection>

            @if (method_exists($evenements, 'total'))
                <span class="barre-outils-compte">
                    {{ $evenements->total() }} {{ $evenements->total() > 1 ? 'événements' : 'événement' }}
                </span>
            @endif
        </div>

        @can('gerer-evenements')
            <div class="barre-outils-actions">
                <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                    Nouvel événement
                </x-ui.bouton>
            </div>
        @endcan
    </div>

    {{-- Liste --}}
    <x-ui.tableau :colonnes="['Événement', 'Période', 'Demandeur', 'Couvertures' => 'tableau-nombre', 'Statut', 'Actions' => 'tableau-actions']">
        @forelse ($evenements as $evenement)
            <tr wire:key="evenement-{{ $evenement->id }}">
                <td>
                    <a href="{{ route('evenements.detail', $evenement) }}" class="tableau-titre">
                        {{ $evenement->intitule }}
                    </a>
                    @if ($evenement->lieu)
                        <span class="tableau-secondaire">{{ $evenement->lieu }}</span>
                    @endif
                </td>

                <td class="tableau-periode">
                    {{ $evenement->date_debut->format('d/m/Y') }}
                    @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                        &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                    @endif
                </td>

                <td>{{ $evenement->demandeur->nom }}</td>

                {{-- Un événement validé ou en cours sans aucune couverture est une alerte, pas un zéro --}}
                <td class="tableau-nombre">
                    @if ($evenement->couvertures_count > 0)
                        {{ $evenement->couvertures_count }}
                    @elseif (in_array($evenement->statut->value, ['valide', 'en_cours']))
                        <x-ui.badge etat="alerte">Aucune</x-ui.badge>
                    @else
                        <span class="text-[var(--midsp-gris-acier)]">—</span>
                    @endif
                </td>

                <td>
                    @php
                        $etat = match ($evenement->statut->value) {
                            'valide', 'termine' => 'ok',
                            'en_cours'          => 'attente',
                            default             => 'neutre',
                        };
                    @endphp
                    <x-ui.badge :etat="$etat">{{ $evenement->statut->libelle() }}</x-ui.badge>
                </td>

                <td class="tableau-actions">
                    <x-ui.action icone="oeil" libelle="Détail" href="{{ route('evenements.detail', $evenement) }}" />

                    @can('gerer-evenements')
                        @if ($evenement->statut->value === 'brouillon')
                            <x-ui.action icone="coche" libelle="Valider" wire:click="valider({{ $evenement->id }})" texte />
                        @endif
                        <x-ui.action icone="crayon" libelle="Modifier" wire:click="ouvrirEdition({{ $evenement->id }})" />
                        <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppression({{ $evenement->id }})" danger />
                    @endcan
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="6">
                @if ($recherche || $filtreStatut)
                    <p>Aucun événement ne correspond à votre recherche.</p>
                @else
                    <p>Aucun événement enregistré pour le moment.</p>
                    @can('gerer-evenements')
                        <x-ui.bouton variante="secondaire" icone="plus" wire:click="ouvrirCreation">
                            Créer le premier événement
                        </x-ui.bouton>
                    @endcan
                @endif
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $evenements->links('pagination.midsp') }}

    {{-- Création / modification --}}
    @if ($modaleOuverte)
        <x-ui.modale :titre="$evenementId ? 'Modifier l\'événement' : 'Nouvel événement'"
                     fermer="$set('modaleOuverte', false)">

            <x-ui.champ libelle="Intitulé" wire:model="intitule" :erreur="$errors->first('intitule')" required />

            <x-ui.champ libelle="Lieu" wire:model="lieu" aide="Ville, quartier ou salle" />

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Date de début" type="date" wire:model="date_debut" :erreur="$errors->first('date_debut')" required />
                <x-ui.champ libelle="Date de fin" type="date" wire:model="date_fin" :erreur="$errors->first('date_fin')" required />
            </div>

            <div class="grille-2 mt-4">
                <div class="champ-bloc">
                    <label for="evenement-demandeur" class="libelle">Demandeur <span aria-hidden="true">*</span></label>
                    <x-ui.selection id="evenement-demandeur" wire:model="demandeur_id" :class="$errors->has('demandeur_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('demandeur_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>

                @if ($evenementId)
                    <div class="champ-bloc">
                        <label for="evenement-statut" class="libelle">Statut</label>
                        <x-ui.selection id="evenement-statut" wire:model="statut">
                            @foreach ($statuts as $s)
                                <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                            @endforeach
                        </x-ui.selection>
                    </div>
                @else
                    <p class="champ-aide self-end pb-3">L'événement sera créé en brouillon.</p>
                @endif
            </div>

            <div class="mt-4">
                <x-ui.champ libelle="Description" type="textarea" rows="3" wire:model="description" />
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Enregistrer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <x-ui.modale titre="Supprimer cet événement ?" largeur="md" fermer="$set('suppressionId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Il sera retiré des listes. La suppression est logique et réversible.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" icone="corbeille" wire:click="supprimer">Supprimer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
