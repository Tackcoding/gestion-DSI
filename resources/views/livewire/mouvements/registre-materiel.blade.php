<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    {{-- Filtres : l'etat physique, pas le statut administratif --}}
    <div class="flex flex-wrap gap-2">
        <button wire:click="$set('filtre', 'a_sortir')"
                @class(['btn', $filtre === 'a_sortir' ? 'btn-principal' : 'btn-secondaire'])>
            À sortir
        </button>
        <button wire:click="$set('filtre', 'en_circulation')"
                @class(['btn', $filtre === 'en_circulation' ? 'btn-principal' : 'btn-secondaire'])>
            En circulation
        </button>
        <button wire:click="$set('filtre', 'tout')"
                @class(['btn', $filtre === 'tout' ? 'btn-principal' : 'btn-secondaire'])>
            Tout
        </button>
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th>Matériel</th>
                    <th>Événement</th>
                    <th>Période</th>
                    <th class="text-center">Réservé</th>
                    <th class="text-center">Sorti</th>
                    <th class="text-center">Rendu</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    @php
                        $sorti      = $reservation->quantiteSortie();
                        $rendu      = $reservation->quantiteRendue();
                        $circulation = $sorti - $rendu;
                    @endphp

                    <tr wire:key="reg-{{ $reservation->id }}">
                        <td>
                            <div class="font-medium">{{ $reservation->materiel->designation }}</div>
                            @if ($reservation->materiel->marque)
                                <div class="text-sm text-[var(--gris)]">
                                    {{ $reservation->materiel->marque }} {{ $reservation->materiel->modele }}
                                </div>
                            @endif
                            @if ($reservation->materiel->accessoires->isNotEmpty())
                                <div class="mt-1 text-xs text-[var(--gris)]">
                                    {{ $reservation->materiel->accessoires->count() }} accessoire(s)
                                </div>
                            @endif
                        </td>
                        <td class="text-sm">
                            <a href="{{ route('evenements.detail', $reservation->evenement) }}"
                               class="lien-action">
                                {{ $reservation->evenement->intitule }}
                            </a>
                        </td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $reservation->date_debut->format('d/m/Y H:i') }}<br>
                            {{ $reservation->date_fin->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-center text-[var(--gris)]">{{ $reservation->quantite }}</td>
                        <td class="text-center text-[var(--gris)]">{{ $sorti }}</td>
                        <td class="text-center">
                            @if ($circulation > 0)
                                <span class="badge badge-attente">{{ $rendu }} / {{ $sorti }}</span>
                            @elseif ($sorti > 0)
                                <span class="badge badge-ok">Complet</span>
                            @else
                                <span class="text-[var(--gris)]">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            @can('gerer-materiel')
                                @if ($sorti < $reservation->quantite)
                                    <button wire:click="ouvrirSortie({{ $reservation->id }})"
                                            class="lien-action">Sortie</button>
                                @endif

                                @if ($circulation > 0)
                                    <button wire:click="ouvrirRetour({{ $reservation->id }})"
                                            class="lien-action ms-4">Retour</button>
                                @endif
                            @endcan

                            @if ($sorti === 0 && ! auth()->user()->can('gerer-materiel'))
                                <span class="text-sm text-[var(--gris)]">Non sorti</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-10 text-center text-[var(--gris)]">
                            Aucune réservation dans cette catégorie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modale sortie / retour --}}
    @if ($modaleOuverte && $reservationCourante)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-1 text-lg">
                    {{ $typeMouvement === 'sortie' ? 'Sortie de matériel' : 'Retour de matériel' }}
                </h3>
                <p class="mb-5 text-sm text-[var(--gris)]">
                    {{ $reservationCourante->materiel->designation }}
                    @if ($reservationCourante->materiel->marque)
                        — {{ $reservationCourante->materiel->marque }} {{ $reservationCourante->materiel->modele }}
                    @endif
                </p>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Quantité</label>
                            <input type="number" min="1" wire:model="quantite" class="champ mt-1">
                            @error('quantite') <span class="erreur">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="libelle">
                                {{ $typeMouvement === 'sortie' ? 'Retiré par' : 'Rendu par' }}
                            </label>
                            <select wire:model="agent_id" class="champ mt-1">
                                <option value="">&mdash; Choisir &mdash;</option>
                                @foreach ($agents as $a)
                                    <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                                @endforeach
                            </select>
                            @error('agent_id') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($typeMouvement === 'retour')
                        <div>
                            <label class="libelle">État constaté</label>
                            <select wire:model="etat_constate" class="champ mt-1">
                                @foreach ($etats as $e)
                                    <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-[var(--gris)]">
                                Un matériel rendu hors service sortira du stock disponible.
                            </p>
                        </div>
                    @endif

                    {{-- Checklist des accessoires : le coeur de l'ecran de retour --}}
                    @if ($reservationCourante->materiel->accessoires->isNotEmpty())
                        <div class="rounded-md border border-[var(--trait)] p-3">
                            <div class="eyebrow mb-2">
                                Accessoires
                                @if ($typeMouvement === 'retour')
                                    — cocher ce qui est revenu
                                @endif
                            </div>

                            <div class="space-y-2">
                                @foreach ($reservationCourante->materiel->accessoires as $accessoire)
                                    <div wire:key="acc-{{ $accessoire->id }}"
                                         class="flex flex-col gap-1 border-b border-[var(--trait)] pb-2 last:border-0 last:pb-0">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox"
                                                   wire:model.live="constats.{{ $accessoire->id }}.present"
                                                   class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                                            <span class="text-sm">{{ $accessoire->libelle }}</span>
                                        </label>

                                        @if (! ($constats[$accessoire->id]['present'] ?? false))
                                            <input type="text"
                                                   wire:model="constats.{{ $accessoire->id }}.observation"
                                                   placeholder="Manquant — préciser si besoin"
                                                   class="champ text-sm">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="libelle">Observation</label>
                        <textarea wire:model="observation" rows="2" class="champ mt-1"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)" class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrer" class="btn btn-principal">
                        {{ $typeMouvement === 'sortie' ? 'Enregistrer la sortie' : 'Enregistrer le retour' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
