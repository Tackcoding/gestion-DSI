<div class="space-y-6">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    {{-- Fiche de l'événement --}}
    <div class="carte p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="tableau-periode text-[var(--midsp-gris)]">
                    {{ $evenement->date_debut->format('d/m/Y') }}
                    @if (! $evenement->date_debut->isSameDay($evenement->date_fin))
                        &rarr; {{ $evenement->date_fin->format('d/m/Y') }}
                    @endif
                    @if ($evenement->lieu) &middot; {{ $evenement->lieu }} @endif
                </p>
                @if ($evenement->demandeur)
                    <p class="mt-1 text-sm text-[var(--midsp-gris)]">
                        Demandé par {{ $evenement->demandeur->nom }}
                    </p>
                @endif
                @if ($evenement->description)
                    <p class="mt-3 text-sm">{{ $evenement->description }}</p>
                @endif
            </div>

            @php
                $etat = match ($evenement->statut->value) {
                    'valide', 'termine' => 'ok',
                    'en_cours'          => 'attente',
                    default             => 'neutre',
                };
            @endphp
            <x-ui.badge :etat="$etat" class="shrink-0">{{ $evenement->statut->libelle() }}</x-ui.badge>
        </div>
    </div>

    {{-- Couvertures --}}
    <section>
        <div class="barre-outils">
            <h2 class="titre text-lg">Couvertures</h2>
            @can('gerer-evenements')
                <div class="barre-outils-actions">
                    <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreationCouverture">
                        Ajouter une couverture
                    </x-ui.bouton>
                </div>
            @endcan
        </div>

        <div class="space-y-3">
            @forelse ($couvertures as $couverture)
                <div wire:key="couv-{{ $couverture->id }}" class="carte p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="tableau-titre tableau-periode">
                                {{ $couverture->date->translatedFormat('l j F Y') }}
                            </div>
                            <div class="mt-1 text-sm text-[var(--midsp-gris)]">
                                @if ($couverture->heure_depart)
                                    Départ {{ substr($couverture->heure_depart, 0, 5) }}
                                    @if ($couverture->lieu_depart) &mdash; {{ $couverture->lieu_depart }} @endif
                                @endif
                                @if ($couverture->heure_retour)
                                    &middot; Retour {{ substr($couverture->heure_retour, 0, 5) }}
                                    @if ($couverture->lieu_retour) &mdash; {{ $couverture->lieu_retour }} @endif
                                @endif
                                @if (! $couverture->heure_depart && ! $couverture->heure_retour)
                                    Horaires non précisés
                                @endif
                            </div>
                        </div>

                        @can('gerer-evenements')
                            <div class="flex shrink-0 -mr-2">
                                <x-ui.action icone="personnes" libelle="Équipe" wire:click="ouvrirEquipe({{ $couverture->id }})" texte />
                                <x-ui.action icone="crayon" libelle="Modifier" wire:click="ouvrirEditionCouverture({{ $couverture->id }})" />
                                <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppressionCouverture({{ $couverture->id }})" danger />
                            </div>
                        @endcan
                    </div>

                    {{-- Équipe affectée --}}
                    <div class="mt-3 border-t border-[var(--midsp-gris-filet)] pt-3">
                        @if ($couverture->agents->isEmpty())
                            <x-ui.badge etat="alerte">Aucun agent affecté</x-ui.badge>
                        @else
                            <div class="badges">
                                @foreach ($couverture->agents as $agent)
                                    <x-ui.badge etat="ok">{{ $agent->nom }} {{ $agent->prenom }}</x-ui.badge>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="carte">
                    <x-ui.vide>
                        <p>Aucune couverture pour cet événement.</p>
                        @can('gerer-evenements')
                            <x-ui.bouton variante="secondaire" icone="plus" wire:click="ouvrirCreationCouverture">
                                Planifier la première couverture
                            </x-ui.bouton>
                        @endcan
                    </x-ui.vide>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Création / modification d'une couverture --}}
    @if ($modaleCouverture)
        <x-ui.modale :titre="$couvertureId ? 'Modifier la couverture' : 'Nouvelle couverture'"
                     fermer="$set('modaleCouverture', false)">

            <x-ui.champ libelle="Date" type="date" wire:model="date" :erreur="$errors->first('date')" required />

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Heure de départ" type="time" wire:model="heure_depart" />
                <x-ui.champ libelle="Lieu de départ" wire:model="lieu_depart" />
            </div>

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Heure de retour" type="time" wire:model="heure_retour" />
                <x-ui.champ libelle="Lieu de retour" wire:model="lieu_retour" />
            </div>

            <div class="mt-4">
                <x-ui.champ libelle="Observation" type="textarea" rows="2" wire:model="observation" />
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleCouverture', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrerCouverture" wire:loading.attr="disabled">Enregistrer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Équipe d'une couverture --}}
    @if ($couvertureEquipeId && $couvertureEnCours)
        <x-ui.modale :titre="'Équipe du ' . $couvertureEnCours->date->format('d/m/Y')"
                     sous-titre="Seuls les agents disponibles ce jour-là sont proposés : les agents en absence validée ou déjà mobilisés sur une autre couverture n'apparaissent pas."
                     fermer="$set('couvertureEquipeId', null)">

            <div class="encadre max-h-72 overflow-y-auto p-2">
                @forelse ($agentsDisponibles as $agent)
                    <label wire:key="dispo-{{ $agent->id }}"
                           class="case-libelle w-full rounded px-2 transition hover:bg-[var(--midsp-or-sable-clair)]">
                        <input type="checkbox" class="case" wire:model="agentsSelectionnes" value="{{ $agent->id }}">
                        <span>{{ $agent->nom }} {{ $agent->prenom }}</span>
                        <span class="ms-auto text-xs text-[var(--midsp-gris)]">{{ $agent->fonction->libelle }}</span>
                    </label>
                @empty
                    <p class="px-2 py-5 text-center text-sm text-[var(--midsp-gris)]">
                        Aucun agent disponible à cette date.
                    </p>
                @endforelse
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('couvertureEquipeId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrerEquipe" wire:loading.attr="disabled">Enregistrer l'équipe</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Suppression d'une couverture --}}
    @if ($suppressionCouvertureId)
        <x-ui.modale titre="Supprimer cette couverture ?" largeur="md" fermer="$set('suppressionCouvertureId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Les affectations d'agents associées seront également supprimées.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionCouvertureId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" icone="corbeille" wire:click="supprimerCouverture">Supprimer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
