<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <select wire:model.live="filtreStatut" class="champ sm:w-auto">
            <option value="">Tous les signalements</option>
            @foreach ($statuts as $s)
                <option value="{{ $s->value }}">{{ $s->libelle() }}</option>
            @endforeach
        </select>

        @can('gerer-materiel')
            <button wire:click="ouvrirCreation" class="btn btn-principal">
                + Nouveau signalement
            </button>
        @endcan
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Objet</th>
                    <th>Nature</th>
                    <th>Constat</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($signalements as $signalement)
                    <tr wire:key="sig-{{ $signalement->id }}">
                        <td class="font-medium">{{ $signalement->reference }}</td>
                        <td>
                            <div>{{ $signalement->objet() }}</div>
                            @if ($signalement->agentResponsable)
                                <div class="text-sm text-[var(--gris)]">
                                    Détenteur : {{ $signalement->agentResponsable->nom }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $signalement->type->libelle() }}</td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $signalement->date_constat->format('d/m/Y') }}<br>
                            {{ $signalement->constatePar->nom }}
                        </td>
                        <td>
                            <span @class([
                                'badge',
                                'badge-attente' => $signalement->statut->value === 'brouillon',
                                'badge-ok'      => $signalement->statut->value === 'vise',
                                'badge-neutre'  => $signalement->statut->value === 'classe',
                            ])>
                                {{ $signalement->statut->libelle() }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap text-right">
                            <a href="{{ route('signalements.pdf', $signalement) }}" target="_blank"
                               class="lien-action">PV</a>

                            @can('viser-signalement')
                                @if ($signalement->statut->value === 'brouillon')
                                    <button wire:click="ouvrirVisa({{ $signalement->id }})"
                                            class="lien-action ms-4">Viser</button>
                                @elseif ($signalement->statut->value === 'vise')
                                    <button wire:click="ouvrirClassement({{ $signalement->id }})"
                                            class="lien-action ms-4">Classer</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--gris)]">
                            Aucun signalement enregistré.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $signalements->links() }}</div>

    {{-- Modale de creation --}}
    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-1 text-lg">Nouveau signalement</h3>
                <p class="mb-5 text-sm text-[var(--gris)]">
                    Le procès-verbal ne prend effet qu'après visa du Directeur.
                </p>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Nature</label>
                            <select wire:model="type" class="champ mt-1">
                                @foreach ($types as $t)
                                    <option value="{{ $t->value }}">{{ $t->libelle() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="libelle">Date du constat</label>
                            <input type="date" wire:model="date_constat" class="champ mt-1">
                            @error('date_constat') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="libelle">Matériel</label>
                        <select wire:model.live="materiel_id" class="champ mt-1">
                            <option value="">&mdash; Choisir &mdash;</option>
                            @foreach ($materiels as $m)
                                <option value="{{ $m->id }}">
                                    {{ $m->designation }}@if ($m->marque) — {{ $m->marque }} {{ $m->modele }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('materiel_id') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    @if ($accessoires->isNotEmpty())
                        <div>
                            <label class="libelle">Accessoire concerné</label>
                            <select wire:model="accessoire_id" class="champ mt-1">
                                <option value="">Le matériel lui-même</option>
                                @foreach ($accessoires as $a)
                                    <option value="{{ $a->id }}">{{ $a->libelle }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-[var(--gris)]">
                                Laissez vide si c'est le matériel entier qui est concerné.
                            </p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Quantité</label>
                            <input type="number" min="1" wire:model="quantite" class="champ mt-1">
                            @error('quantite') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Agent détenteur</label>
                            <select wire:model="agent_responsable_id" class="champ mt-1">
                                <option value="">&mdash; Non déterminé &mdash;</option>
                                @foreach ($agents as $a)
                                    <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="libelle">Circonstances</label>
                        <textarea wire:model="circonstances" rows="4" class="champ mt-1"
                                  placeholder="Décrire les faits : date, lieu, contexte, personnes présentes."></textarea>
                        @error('circonstances') <span class="erreur">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)" class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrer" class="btn btn-principal">
                        Établir le procès-verbal
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modale de visa --}}
    @if ($visaId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Viser le procès-verbal</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    Votre visa rend ce document opposable. Il ne pourra plus être modifié.
                </p>

                <div class="mt-4">
                    <label class="libelle">Observation (facultative)</label>
                    <textarea wire:model="observation_visa" rows="3" class="champ mt-1"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('visaId', null)" class="btn btn-secondaire">Annuler</button>
                    <button wire:click="viser" class="btn btn-principal">Apposer le visa</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modale de classement --}}
    @if ($classementId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Classer le signalement</h3>

                <div class="mt-4">
                    <label class="libelle">Suite donnée</label>
                    <textarea wire:model="suite_donnee" rows="3" class="champ mt-1"
                              placeholder="Remplacement, retenue, classement sans suite..."></textarea>
                    @error('suite_donnee') <span class="erreur">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('classementId', null)" class="btn btn-secondaire">Annuler</button>
                    <button wire:click="classer" class="btn btn-principal">Classer</button>
                </div>
            </div>
        </div>
    @endif
</div>
