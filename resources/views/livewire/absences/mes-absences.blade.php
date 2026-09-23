<div class="space-y-6">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    {{-- Soldes de congés --}}
    @if ($this->soldes)
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($this->soldes as $solde)
                <div class="carte p-4" wire:key="solde-{{ $solde['type']->id }}">
                    <div class="eyebrow">{{ $solde['type']->libelle }}</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="titre text-3xl">{{ rtrim(rtrim(number_format($solde['restants'], 1, ',', ''), '0'), ',') }}</span>
                        <span class="text-sm text-[var(--midsp-gris)]">
                            jour(s) restant(s) sur {{ rtrim(rtrim(number_format($solde['accordes'], 1, ',', ''), '0'), ',') }}
                        </span>
                    </div>
                    @if ($solde['type']->duree_max_par_demande)
                        <p class="mt-1 text-xs text-[var(--midsp-gris)]">
                            {{ rtrim(rtrim(number_format($solde['type']->duree_max_par_demande, 1, ',', ''), '0'), ',') }} jour(s) maximum par demande
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="barre-outils">
        <h2 class="titre text-lg">Demandes d'absence</h2>
        <div class="barre-outils-actions">
            <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                Nouvelle demande
            </x-ui.bouton>
        </div>
    </div>

    @php $peutValider = auth()->user()->can('valider-absence'); @endphp

    <x-ui.tableau>
        <x-slot:entete>
            @if ($peutValider)<th scope="col">Agent</th>@endif
            <th scope="col">Type</th>
            <th scope="col">Période</th>
            <th scope="col" class="tableau-nombre">Jours</th>
            <th scope="col">Statut</th>
            <th scope="col" class="tableau-actions">Actions</th>
        </x-slot:entete>

        @forelse ($demandes as $demande)
            <tr wire:key="dem-{{ $demande->id }}">
                @if ($peutValider)
                    <td>
                        <span class="tableau-titre">{{ $demande->agent->nom }}</span>
                        <span class="tableau-secondaire">{{ $demande->agent->prenom }}</span>
                    </td>
                @endif

                <td>{{ $demande->type->libelle }}</td>

                <td class="tableau-periode">
                    {{ $demande->date_debut->format('d/m/Y') }}
                    @if (! $demande->date_debut->isSameDay($demande->date_fin))
                        &rarr; {{ $demande->date_fin->format('d/m/Y') }}
                    @endif
                    @if ($demande->demi_journee)
                        <span class="tableau-secondaire">demi-journée</span>
                    @endif
                </td>

                <td class="tableau-nombre">
                    {{ rtrim(rtrim(number_format($demande->nb_jours, 1, ',', ''), '0'), ',') }}
                </td>

                <td>
                    @php
                        $etat = match ($demande->statut->value) {
                            'demandee' => 'attente',
                            'validee'  => 'ok',
                            'refusee'  => 'alerte',
                            default    => 'neutre',
                        };
                    @endphp
                    <x-ui.badge :etat="$etat">{{ $demande->statut->libelle() }}</x-ui.badge>

                    @if ($demande->motif_refus)
                        <span class="tableau-secondaire text-[var(--midsp-rouge)]">{{ $demande->motif_refus }}</span>
                    @endif
                </td>

                <td class="tableau-actions">
                    @if ($demande->estImprimable())
                        <x-ui.action icone="document" libelle="Formulaire PDF"
                                     href="{{ route('absences.formulaire', $demande) }}" target="_blank" rel="noopener" />
                    @endif

                    @if ($demande->justificatif_path)
                        <x-ui.action icone="trombone" libelle="Justificatif"
                                     href="{{ Storage::url($demande->justificatif_path) }}" target="_blank" rel="noopener" />
                    @endif

                    @if ($demande->statut->value === 'demandee')
                        <x-ui.action icone="fermer" libelle="Annuler la demande" wire:click="confirmerSuppression({{ $demande->id }})" danger />
                    @endif
                </td>
            </tr>
        @empty
            <x-ui.vide :colspan="$peutValider ? 6 : 5">
                <p>Aucune demande enregistrée.</p>
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $demandes->links('pagination.midsp') }}

    {{-- Nouvelle demande --}}
    @if ($modaleOuverte)
        <x-ui.modale titre="Nouvelle demande d'absence" fermer="$set('modaleOuverte', false)">

            @if ($peutValider)
                <div class="champ-bloc">
                    <label for="absence-agent" class="libelle">Agent concerné <span aria-hidden="true">*</span></label>
                    <x-ui.selection id="absence-agent" wire:model.live="agent_id" :class="$errors->has('agent_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('agent_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="champ-bloc">
                <label for="absence-type" class="libelle">Type d'absence <span aria-hidden="true">*</span></label>
                <x-ui.selection id="absence-type" wire:model.live="type_id" :class="$errors->has('type_id') ? 'champ-erreur' : ''">
                    <option value="">— Choisir —</option>
                    @foreach ($types as $t)
                        <option value="{{ $t->id }}">{{ $t->libelle }}</option>
                    @endforeach
                </x-ui.selection>
                @error('type_id') <p class="champ-message">{{ $message }}</p> @enderror
            </div>

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Du" type="date" wire:model.live="date_debut" :erreur="$errors->first('date_debut')" required />
                <x-ui.champ libelle="Au" type="date" wire:model.live="date_fin" :erreur="$errors->first('date_fin')" required />
            </div>

            <div class="mt-2">
                <x-ui.case wire:model.live="demi_journee">Demi-journée seulement</x-ui.case>
            </div>

            @if ($this->apercuJours !== null)
                <div class="encart mt-2">
                    {{ rtrim(rtrim(number_format($this->apercuJours, 1, ',', ''), '0'), ',') }}
                    jour(s) ouvré(s) seront décomptés (week-ends exclus).
                </div>
            @endif

            <div class="mt-4">
                <x-ui.champ libelle="Motif de la demande" type="textarea" rows="2" wire:model="motif" />
            </div>

            {{-- Repris sur le formulaire officiel : congé annuel et permission seulement --}}
            @if ($this->avecFormulaire)
                <div class="encadre mt-4">
                    <p class="eyebrow">Pour le formulaire officiel</p>
                    <p class="champ-aide mt-0">Ces informations figureront sur la demande imprimée.</p>

                    <div class="mt-3">
                        <x-ui.champ libelle="Lieu de jouissance" wire:model="lieu_jouissance"
                                    :erreur="$errors->first('lieu_jouissance')"
                                    aide="Ville ou région où le congé sera passé" />

                        <x-ui.champ libelle="Adresse où l'on peut vous joindre" wire:model="adresse_contact"
                                    :erreur="$errors->first('adresse_contact')" />

                        <x-ui.champ libelle="Contact" type="tel" wire:model="contact"
                                    :erreur="$errors->first('contact')" />
                    </div>
                </div>
            @endif

            <div class="champ-bloc mt-4">
                <label for="absence-justificatif" class="libelle">Justificatif (PDF ou image)</label>
                <input id="absence-justificatif" type="file" wire:model="justificatif"
                       class="champ {{ $errors->has('justificatif') ? 'champ-erreur' : '' }}">
                <p wire:loading wire:target="justificatif" class="champ-aide travail">Envoi en cours</p>
                @error('justificatif') <p class="champ-message">{{ $message }}</p> @enderror
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Envoyer la demande</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Annulation d'une demande --}}
    @if ($suppressionId)
        <x-ui.modale titre="Annuler cette demande ?" largeur="md" fermer="$set('suppressionId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Elle sera retirée de la liste des demandes en attente.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionId', null)">Retour</x-ui.bouton>
                <x-ui.bouton variante="danger" wire:click="supprimer">Annuler la demande</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
