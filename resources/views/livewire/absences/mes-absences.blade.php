<div class="space-y-6">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    {{-- Soldes de l'agent connecte --}}
    @if ($this->soldes)
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($this->soldes as $solde)
                <div class="carte p-4">
                    <div class="eyebrow">{{ $solde['type']->libelle }}</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="titre text-3xl">{{ rtrim(rtrim(number_format($solde['restants'], 1, ',', ''), '0'), ',') }}</span>
                        <span class="text-sm text-[var(--gris)]">
                            jour(s) restant(s) sur {{ rtrim(rtrim(number_format($solde['accordes'], 1, ',', ''), '0'), ',') }}
                        </span>
                    </div>
                    @if ($solde['type']->duree_max_par_demande)
                        <p class="mt-1 text-xs text-[var(--gris)]">
                            {{ rtrim(rtrim(number_format($solde['type']->duree_max_par_demande, 1, ',', ''), '0'), ',') }} jour(s) maximum par demande
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="flex items-center justify-between">
        <h3 class="titre text-lg">Demandes d'absence</h3>
        <button wire:click="ouvrirCreation" class="btn btn-principal">
            + Nouvelle demande
        </button>
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    @can('valider-absence')<th>Agent</th>@endcan
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($demandes as $demande)
                    <tr wire:key="dem-{{ $demande->id }}">
                        @can('valider-absence')
                            <td>
                                <div class="font-medium">{{ $demande->agent->nom }}</div>
                                <div class="text-sm text-[var(--gris)]">{{ $demande->agent->prenom }}</div>
                            </td>
                        @endcan
                        <td>{{ $demande->type->libelle }}</td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $demande->date_debut->format('d/m/Y') }}
                            @if (! $demande->date_debut->isSameDay($demande->date_fin))
                                &rarr; {{ $demande->date_fin->format('d/m/Y') }}
                            @endif
                            @if ($demande->demi_journee)
                                <span class="block text-xs">demi-journée</span>
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">
                            {{ rtrim(rtrim(number_format($demande->nb_jours, 1, ',', ''), '0'), ',') }}
                        </td>
                        <td>
                            <span @class([
                                'badge',
                                'badge-attente' => $demande->statut->value === 'demandee',
                                'badge-ok'      => $demande->statut->value === 'validee',
                                'badge-alerte'  => $demande->statut->value === 'refusee',
                                'badge-neutre'  => $demande->statut->value === 'annulee',
                            ])>
                                {{ $demande->statut->libelle() }}
                            </span>

                            @if ($demande->motif_refus)
                                <div class="mt-1 text-xs text-[var(--alerte)]">
                                    {{ $demande->motif_refus }}
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            @if ($demande->justificatif_path)
                                <a href="{{ Storage::url($demande->justificatif_path) }}" target="_blank"
                                   class="lien-action">Justificatif</a>
                            @endif

                            @if ($demande->statut->value === 'demandee')
                                <button wire:click="confirmerSuppression({{ $demande->id }})"
                                        class="lien-alerte ms-4">Annuler</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->can('valider-absence') ? 6 : 5 }}"
                            class="py-10 text-center text-[var(--gris)]">
                            Aucune demande enregistrée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $demandes->links() }}</div>

    {{-- Modale de demande --}}
    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">Nouvelle demande d'absence</h3>

                <div class="space-y-4">
                    @can('valider-absence')
                        <div>
                            <label class="libelle">Agent concerné</label>
                            <select wire:model.live="agent_id" class="champ mt-1">
                                <option value="">&mdash; Choisir &mdash;</option>
                                @foreach ($agents as $a)
                                    <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                                @endforeach
                            </select>
                            @error('agent_id') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    @endcan

                    <div>
                        <label class="libelle">Type d'absence</label>
                        <select wire:model.live="type_id" class="champ mt-1">
                            <option value="">&mdash; Choisir &mdash;</option>
                            @foreach ($types as $t)
                                <option value="{{ $t->id }}">{{ $t->libelle }}</option>
                            @endforeach
                        </select>
                        @error('type_id') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Du</label>
                            <input type="date" wire:model.live="date_debut" class="champ mt-1">
                            @error('date_debut') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Au</label>
                            <input type="date" wire:model.live="date_fin" class="champ mt-1">
                            @error('date_fin') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="demi_journee"
                               class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                        <span class="text-sm text-[var(--gris)]">Demi-journée seulement</span>
                    </label>

                    {{-- Apercu du decompte : samedi et dimanche exclus --}}
                    @if ($this->apercuJours !== null)
                        <div class="rounded-md border-l-4 border-[var(--vert)] bg-[var(--ok-clair)] px-3 py-2 text-sm text-[var(--vert-fonce)]">
                            {{ rtrim(rtrim(number_format($this->apercuJours, 1, ',', ''), '0'), ',') }}
                            jour(s) ouvré(s) seront décomptés (week-ends exclus).
                        </div>
                    @endif

                    <div>
                        <label class="libelle">Motif</label>
                        <textarea wire:model="motif" rows="2" class="champ mt-1"></textarea>
                    </div>

                    <div>
                        <label class="libelle">Justificatif (PDF ou image)</label>
                        <input type="file" wire:model="justificatif" class="champ mt-1">
                        <div wire:loading wire:target="justificatif" class="mt-1 text-xs text-[var(--gris)]">
                            Envoi en cours...
                        </div>
                        @error('justificatif') <span class="erreur">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)" class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrer" class="btn btn-principal">
                        Envoyer la demande
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation d'annulation --}}
    @if ($suppressionId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Annuler cette demande ?</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    Elle sera retirée de la liste des demandes en attente.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionId', null)" class="btn btn-secondaire">Retour</button>
                    <button wire:click="supprimer" class="btn btn-alerte">Annuler la demande</button>
                </div>
            </div>
        </div>
    @endif
</div>
