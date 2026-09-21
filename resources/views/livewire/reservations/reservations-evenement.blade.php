<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="barre-outils">
        <h2 class="titre text-lg">Matériel réservé</h2>

        @can('gerer-evenements')
            <div class="barre-outils-actions">
                <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                    Réserver du matériel
                </x-ui.bouton>
            </div>
        @endcan
    </div>

    <x-ui.tableau :colonnes="['Matériel', 'Qté' => 'tableau-nombre', 'Période', 'Destination', 'Statut', 'Actions' => 'tableau-actions']">
        @forelse ($reservations as $reservation)
            <tr wire:key="resa-{{ $reservation->id }}">
                <td>
                    <span class="tableau-titre">{{ $reservation->materiel->designation }}</span>
                    @if ($reservation->materiel->marque)
                        <span class="tableau-secondaire">
                            {{ $reservation->materiel->marque }} {{ $reservation->materiel->modele }}
                        </span>
                    @endif
                </td>

                <td class="tableau-nombre">{{ $reservation->quantite }}</td>

                <td class="tableau-periode text-sm">
                    {{ $reservation->date_debut->format('d/m/Y H:i') }}<br>
                    {{ $reservation->date_fin->format('d/m/Y H:i') }}
                </td>

                <td class="text-sm">
                    @if ($reservation->estUnPretExterne())
                        <x-ui.badge etat="attente">Prêt externe</x-ui.badge>
                        <span class="tableau-secondaire">
                            {{ $reservation->direction_emprunteuse }}
                            @if ($reservation->contact_emprunteur)
                                &middot; {{ $reservation->contact_emprunteur }}
                            @endif
                        </span>
                    @else
                        <span class="text-[var(--midsp-gris)]">Usage interne</span>
                    @endif
                </td>

                <td>
                    @php
                        $etat = match ($reservation->statut->value) {
                            'demandee' => 'attente',
                            'validee'  => 'ok',
                            'refusee'  => 'alerte',
                            default    => 'neutre',
                        };
                    @endphp
                    <x-ui.badge :etat="$etat">{{ $reservation->statut->libelle() }}</x-ui.badge>

                    @if ($reservation->motif_refus)
                        <span class="tableau-secondaire text-[var(--midsp-rouge)]">{{ $reservation->motif_refus }}</span>
                    @endif
                </td>

                <td class="tableau-actions">
                    @if ($reservation->statut->value === 'demandee')
                        @can('valider-reservation')
                            <x-ui.action icone="coche" libelle="Valider" wire:click="valider({{ $reservation->id }})" texte />
                            <x-ui.action icone="fermer" libelle="Refuser" wire:click="ouvrirRefus({{ $reservation->id }})" danger />
                        @endcan

                        @can('gerer-evenements')
                            <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppression({{ $reservation->id }})" danger />
                        @endcan
                    @elseif ($reservation->validateur)
                        <span class="text-sm text-[var(--midsp-gris)]">{{ $reservation->validateur->nom }}</span>
                    @endif
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="6">
                <p>Aucun matériel réservé pour cet événement.</p>
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{-- Nouvelle réservation --}}
    @if ($modaleOuverte)
        <x-ui.modale titre="Réserver du matériel" fermer="$set('modaleOuverte', false)">

            <div class="champ-bloc">
                <label for="reservation-materiel" class="libelle">Matériel <span aria-hidden="true">*</span></label>
                <x-ui.selection id="reservation-materiel" wire:model.live="materiel_id" :class="$errors->has('materiel_id') ? 'champ-erreur' : ''">
                    <option value="">— Choisir —</option>
                    @foreach ($materiels as $m)
                        <option value="{{ $m->id }}">
                            {{ $m->designation }}@if ($m->marque) — {{ $m->marque }} {{ $m->modele }}@endif
                        </option>
                    @endforeach
                </x-ui.selection>
                @error('materiel_id') <p class="champ-message">{{ $message }}</p> @enderror
            </div>

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="Retrait" type="datetime-local" wire:model.live="date_debut" :erreur="$errors->first('date_debut')" required />
                <x-ui.champ libelle="Retour" type="datetime-local" wire:model.live="date_fin" :erreur="$errors->first('date_fin')" required />
            </div>

            <div class="mt-4">
                <x-ui.champ libelle="Quantité" type="number" min="1" wire:model.live="quantite" :erreur="$errors->first('quantite')" required />
            </div>

            {{-- Prêt à une autre direction : uniquement pour les supports de communication --}}
            @if ($materiel_id)
                @if ($this->materielPretable)
                    <div class="encadre mt-4">
                        <x-ui.case wire:model.live="pretExterne">Prêt à une autre direction</x-ui.case>

                        @if ($pretExterne)
                            <div class="mt-2 space-y-4">
                                <x-ui.champ libelle="Direction emprunteuse" wire:model="direction_emprunteuse"
                                            placeholder="Direction du Cabinet, DAAF…"
                                            :erreur="$errors->first('direction_emprunteuse')" required />
                                <x-ui.champ libelle="Contact" wire:model="contact_emprunteur"
                                            placeholder="Nom et téléphone du responsable" />
                            </div>
                        @endif
                    </div>
                @else
                    <p class="encart encart-neutre mt-4">
                        Ce matériel ne peut pas être prêté à une autre direction.
                    </p>
                @endif
            @endif

            {{-- Disponibilité sur la période --}}
            @if ($this->disponibilite)
                @php $d = $this->disponibilite; @endphp
                <div @class(['encart mt-4', 'encart-alerte' => ! $d['suffisant']])>
                    @if ($d['suffisant'])
                        {{ $d['disponible'] }} exemplaire(s) disponible(s) sur {{ $d['total'] }} pour cette période.
                    @else
                        Seulement {{ $d['disponible'] }} exemplaire(s) disponible(s) sur cette période :
                        d'autres réservations validées se chevauchent.
                    @endif
                </div>
            @endif

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Enregistrer la demande</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Refus --}}
    @if ($refusId)
        <x-ui.modale titre="Refuser la réservation" sous-titre="Le motif sera visible par le demandeur." largeur="md" fermer="$set('refusId', null)">
            <x-ui.champ libelle="Motif" type="textarea" rows="3" wire:model="motif_refus" :erreur="$errors->first('motif_refus')" required />

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('refusId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" wire:click="refuser">Refuser</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Suppression --}}
    @if ($suppressionId)
        <x-ui.modale titre="Supprimer cette réservation ?" largeur="md" fermer="$set('suppressionId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Le matériel redeviendra disponible sur cette période.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" icone="corbeille" wire:click="supprimer">Supprimer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
