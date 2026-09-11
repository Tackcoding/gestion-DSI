<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="flex items-center justify-between">
        <h4 class="titre text-lg">Matériel réservé</h4>

        @can('gerer-evenements')
            <button wire:click="ouvrirCreation" class="btn btn-principal">
                + Réserver du matériel
            </button>
        @endcan
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th>Matériel</th>
                    <th>Qté</th>
                    <th>Période</th>
                    <th>Destination</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reservations as $reservation)
                    <tr wire:key="resa-{{ $reservation->id }}">
                        <td>
                            <div class="font-medium">{{ $reservation->materiel->designation }}</div>
                            @if ($reservation->materiel->marque)
                                <div class="text-sm text-[var(--gris)]">
                                    {{ $reservation->materiel->marque }} {{ $reservation->materiel->modele }}
                                </div>
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">{{ $reservation->quantite }}</td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $reservation->date_debut->format('d/m/Y H:i') }}<br>
                            {{ $reservation->date_fin->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-sm">
                            @if ($reservation->estUnPretExterne())
                                <span class="badge badge-attente">Prêt externe</span>
                                <div class="mt-1 text-[var(--gris)]">
                                    {{ $reservation->direction_emprunteuse }}
                                    @if ($reservation->contact_emprunteur)
                                        <br>{{ $reservation->contact_emprunteur }}
                                    @endif
                                </div>
                            @else
                                <span class="text-[var(--gris)]">Usage interne</span>
                            @endif
                        </td>
                        <td>
                            <span @class([
                                'badge',
                                'badge-attente' => $reservation->statut->value === 'demandee',
                                'badge-ok'      => $reservation->statut->value === 'validee',
                                'badge-alerte'  => $reservation->statut->value === 'refusee',
                                'badge-neutre'  => $reservation->statut->value === 'annulee',
                            ])>
                                {{ $reservation->statut->libelle() }}
                            </span>

                            @if ($reservation->motif_refus)
                                <div class="mt-1 text-xs text-[var(--alerte)]">
                                    {{ $reservation->motif_refus }}
                                </div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            @if ($reservation->statut->value === 'demandee')
                                @can('valider-reservation')
                                    <button wire:click="valider({{ $reservation->id }})"
                                            class="lien-action">Valider</button>
                                    <button wire:click="ouvrirRefus({{ $reservation->id }})"
                                            class="lien-alerte ms-4">Refuser</button>
                                @endcan

                                @can('gerer-evenements')
                                    <button wire:click="confirmerSuppression({{ $reservation->id }})"
                                            class="lien-alerte ms-4">Supprimer</button>
                                @endcan
                            @else
                                <span class="text-sm text-[var(--gris)]">
                                    @if ($reservation->validateur)
                                        {{ $reservation->validateur->nom }}
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--gris)]">
                            Aucun matériel réservé pour cet événement.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modale de reservation --}}
    @if ($modaleOuverte)
        <div class="voile">
            <div class="modale max-w-lg">
                <h3 class="titre mb-5 text-lg">Réserver du matériel</h3>

                <div class="space-y-4">
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="libelle">Retrait</label>
                            <input type="datetime-local" wire:model.live="date_debut" class="champ mt-1">
                            @error('date_debut') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="libelle">Retour</label>
                            <input type="datetime-local" wire:model.live="date_fin" class="champ mt-1">
                            @error('date_fin') <span class="erreur">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="libelle">Quantité</label>
                        <input type="number" min="1" wire:model.live="quantite" class="champ mt-1">
                        @error('quantite') <span class="erreur">{{ $message }}</span> @enderror
                    </div>

                    {{-- Pret externe : propose uniquement pour les supports
                         de communication. Le materiel identifie individuellement
                         ne sort pas de la direction. --}}
                    @if ($materiel_id)
                        @if ($this->materielPretable)
                            <div class="rounded-md border border-[var(--trait)] p-3">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model.live="pretExterne"
                                           class="rounded border-[var(--trait)] text-[var(--vert)] focus:ring-[var(--vert)]">
                                    <span class="text-sm">Prêt à une autre direction</span>
                                </label>

                                @if ($pretExterne)
                                    <div class="mt-3 space-y-3">
                                        <div>
                                            <label class="libelle">Direction emprunteuse</label>
                                            <input type="text" wire:model="direction_emprunteuse"
                                                   class="champ mt-1"
                                                   placeholder="Direction du Cabinet, DAAF...">
                                            @error('direction_emprunteuse')
                                                <span class="erreur">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div>
                                            <label class="libelle">Contact</label>
                                            <input type="text" wire:model="contact_emprunteur"
                                                   class="champ mt-1"
                                                   placeholder="Nom et téléphone du responsable">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-[var(--gris)]">
                                Ce matériel ne peut pas être prêté à une autre direction.
                            </p>
                        @endif
                    @endif

                    {{-- Disponibilite calculee en direct sur la periode saisie.
                         Ce n'est qu'un confort : la verification qui fait foi est
                         celle de ReservationService, sous verrou de transaction. --}}
                    @if ($this->disponibilite)
                        @php $d = $this->disponibilite; @endphp
                        <div @class([
                            'rounded-md border-l-4 px-3 py-2 text-sm',
                            'border-[var(--vert)] bg-[var(--ok-clair)] text-[var(--vert-fonce)]' => $d['suffisant'],
                            'border-[var(--alerte)] bg-[var(--alerte-clair)] text-[var(--alerte)]' => ! $d['suffisant'],
                        ])>
                            @if ($d['suffisant'])
                                {{ $d['disponible'] }} exemplaire(s) disponible(s)
                                sur {{ $d['total'] }} pour cette période.
                            @else
                                Seulement {{ $d['disponible'] }} exemplaire(s) disponible(s)
                                sur cette période : d'autres réservations validées se chevauchent.
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('modaleOuverte', false)" class="btn btn-secondaire">
                        Annuler
                    </button>
                    <button wire:click="enregistrer" class="btn btn-principal">
                        Enregistrer la demande
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modale de refus --}}
    @if ($refusId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Refuser la réservation</h3>
                <p class="mt-1 text-sm text-[var(--gris)]">
                    Le motif sera visible par le demandeur.
                </p>

                <div class="mt-4">
                    <label class="libelle">Motif</label>
                    <textarea wire:model="motif_refus" rows="3" class="champ mt-1"></textarea>
                    @error('motif_refus') <span class="erreur">{{ $message }}</span> @enderror
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('refusId', null)" class="btn btn-secondaire">Annuler</button>
                    <button wire:click="refuser" class="btn btn-alerte">Refuser</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Supprimer cette réservation ?</h3>
                <p class="mt-2 text-sm text-[var(--gris)]">
                    Le matériel redeviendra disponible sur cette période.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('suppressionId', null)" class="btn btn-secondaire">Annuler</button>
                    <button wire:click="supprimer" class="btn btn-alerte">Supprimer</button>
                </div>
            </div>
        </div>
    @endif
</div>
