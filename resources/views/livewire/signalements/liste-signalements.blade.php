<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="barre-outils">
        <div class="barre-outils-filtres">
            <x-ui.selection wire:model.live="filtreStatut" libelle="Filtrer par statut">
                <option value="">Tous les signalements</option>
                @foreach ($statuts as $s)
                    <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
                @endforeach
            </x-ui.selection>

            @if (method_exists($signalements, 'total'))
                <span class="barre-outils-compte">
                    {{ $signalements->total() }} {{ $signalements->total() > 1 ? 'signalements' : 'signalement' }}
                </span>
            @endif
        </div>

        @can('gerer-materiel')
            <div class="barre-outils-actions">
                <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                    Nouveau signalement
                </x-ui.bouton>
            </div>
        @endcan
    </div>

    <x-ui.tableau :colonnes="['Référence', 'Objet', 'Nature', 'Constat', 'Statut', 'Actions' => 'tableau-actions']">
        @forelse ($signalements as $signalement)
            <tr wire:key="sig-{{ $signalement->id }}">
                <td class="tableau-titre tableau-periode">{{ $signalement->reference }}</td>

                <td>
                    {{ $signalement->objet() }}
                    @if ($signalement->agentResponsable)
                        <span class="tableau-secondaire">Détenteur : {{ $signalement->agentResponsable->nom }}</span>
                    @endif
                </td>

                <td>{{ $signalement->type->libelle() }}</td>

                <td class="tableau-periode">
                    {{ $signalement->date_constat->format('d/m/Y') }}
                    <span class="tableau-secondaire">{{ $signalement->constatePar->nom }}</span>
                </td>

                <td>
                    @php
                        $etat = match ($signalement->statut->value) {
                            'brouillon' => 'attente',
                            'vise'      => 'ok',
                            default     => 'neutre',
                        };
                    @endphp
                    <x-ui.badge :etat="$etat">{{ $signalement->statut->libelle() }}</x-ui.badge>
                </td>

                <td class="tableau-actions">
                    <x-ui.action icone="document" libelle="Procès-verbal"
                                 href="{{ route('signalements.pdf', $signalement) }}" target="_blank" rel="noopener" texte />

                    @can('viser-signalement')
                        @if ($signalement->statut->value === 'brouillon')
                            <x-ui.action icone="tampon" libelle="Viser" wire:click="ouvrirVisa({{ $signalement->id }})" texte />
                        @elseif ($signalement->statut->value === 'vise')
                            <x-ui.action icone="boite" libelle="Classer" wire:click="ouvrirClassement({{ $signalement->id }})" texte />
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="6">
                <p>Aucun signalement enregistré.</p>
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $signalements->links('pagination.midsp') }}

    {{-- Nouveau signalement --}}
    @if ($modaleOuverte)
        <x-ui.modale titre="Nouveau signalement"
                     sous-titre="Le procès-verbal ne prend effet qu'après visa du Directeur."
                     fermer="$set('modaleOuverte', false)">

            <div class="grille-2">
                <div class="champ-bloc">
                    <label for="signalement-type" class="libelle">Nature</label>
                    <x-ui.selection id="signalement-type" wire:model="type">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}">{{ $t->libelle() }}</option>
                        @endforeach
                    </x-ui.selection>
                </div>

                <x-ui.champ libelle="Date du constat" type="date" wire:model="date_constat" :erreur="$errors->first('date_constat')" required />
            </div>

            <div class="champ-bloc mt-4">
                <label for="signalement-materiel" class="libelle">Matériel <span aria-hidden="true">*</span></label>
                <x-ui.selection id="signalement-materiel" wire:model.live="materiel_id" :class="$errors->has('materiel_id') ? 'champ-erreur' : ''">
                    <option value="">— Choisir —</option>
                    @foreach ($materiels as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->designation }}@if ($m->marque) — {{ $m->marque }} {{ $m->modele }}@endif
                        </option>
                    @endforeach
                </x-ui.selection>
                @error('materiel_id') <p class="champ-message">{{ $message }}</p> @enderror
            </div>

            @if ($accessoires->isNotEmpty())
                <div class="champ-bloc mt-4">
                    <label for="signalement-accessoire" class="libelle">Accessoire concerné</label>
                    <x-ui.selection id="signalement-accessoire" wire:model="accessoire_id">
                        <option value="">Le matériel lui-même</option>
                        @foreach ($accessoires as $a)
                            <option value="{{ $a->id }}">{{ $a->libelle }}</option>
                        @endforeach
                    </x-ui.selection>
                    <p class="champ-aide">Laissez vide si c'est le matériel entier qui est concerné.</p>
                </div>
            @endif

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Quantité" type="number" min="1" wire:model="quantite" :erreur="$errors->first('quantite')" required />

                <div class="champ-bloc">
                    <label for="signalement-agent" class="libelle">Agent détenteur</label>
                    <x-ui.selection id="signalement-agent" wire:model="agent_responsable_id">
                        <option value="">— Non déterminé —</option>
                        @foreach ($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                        @endforeach
                    </x-ui.selection>
                </div>
            </div>

            <div class="mt-4">
                <x-ui.champ libelle="Circonstances" type="textarea" rows="4" wire:model="circonstances"
                            placeholder="Décrire les faits : date, lieu, contexte, personnes présentes."
                            :erreur="$errors->first('circonstances')" required />
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Établir le procès-verbal</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Visa --}}
    @if ($visaId)
        <x-ui.modale titre="Viser le procès-verbal"
                     sous-titre="Votre visa rend ce document opposable. Il ne pourra plus être modifié."
                     largeur="md" fermer="$set('visaId', null)">
            <x-ui.champ libelle="Observation (facultative)" type="textarea" rows="3" wire:model="observation_visa" />

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('visaId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" icone="tampon" wire:click="viser">Apposer le visa</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Classement --}}
    @if ($classementId)
        <x-ui.modale titre="Classer le signalement" largeur="md" fermer="$set('classementId', null)">
            <x-ui.champ libelle="Suite donnée" type="textarea" rows="3" wire:model="suite_donnee"
                        placeholder="Remplacement, retenue, classement sans suite…"
                        :erreur="$errors->first('suite_donnee')" required />

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('classementId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" icone="boite" wire:click="classer">Classer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
