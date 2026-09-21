<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    {{-- Filtres : un seul actif à la fois --}}
    <div class="segments" role="group" aria-label="Filtrer le registre">
        <x-ui.bouton variante="secondaire" wire:click="$set('filtre', 'a_sortir')" :aria-pressed="$filtre === 'a_sortir' ? 'true' : 'false'">
            À sortir
        </x-ui.bouton>
        <x-ui.bouton variante="secondaire" wire:click="$set('filtre', 'en_circulation')" :aria-pressed="$filtre === 'en_circulation' ? 'true' : 'false'">
            En circulation
        </x-ui.bouton>
        <x-ui.bouton variante="secondaire" wire:click="$set('filtre', 'tout')" :aria-pressed="$filtre === 'tout' ? 'true' : 'false'">
            Tout
        </x-ui.bouton>
    </div>

    <x-ui.tableau :colonnes="['Matériel', 'Événement', 'Période', 'Réservé' => 'tableau-nombre', 'Sorti' => 'tableau-nombre', 'Rendu', 'Actions' => 'tableau-actions']">
        @forelse ($reservations as $reservation)
            @php
                $sorti       = $reservation->quantiteSortie();
                $rendu       = $reservation->quantiteRendue();
                $circulation = $sorti - $rendu;
            @endphp

            <tr wire:key="reg-{{ $reservation->id }}">
                <td>
                    <span class="tableau-titre">{{ $reservation->materiel->designation }}</span>
                    @if ($reservation->materiel->marque)
                        <span class="tableau-secondaire">
                            {{ $reservation->materiel->marque }} {{ $reservation->materiel->modele }}
                        </span>
                    @endif
                    @if ($reservation->materiel->accessoires->isNotEmpty())
                        <span class="tableau-secondaire">
                            {{ $reservation->materiel->accessoires->count() }} accessoire(s)
                        </span>
                    @endif
                </td>

                <td>
                    <a href="{{ route('evenements.detail', $reservation->evenement) }}" class="lien text-sm">
                        {{ $reservation->evenement->intitule }}
                    </a>
                </td>

                <td class="tableau-periode text-sm">
                    {{ $reservation->date_debut->format('d/m/Y H:i') }}<br>
                    {{ $reservation->date_fin->format('d/m/Y H:i') }}
                </td>

                <td class="tableau-nombre">{{ $reservation->quantite }}</td>
                <td class="tableau-nombre">{{ $sorti }}</td>

                <td>
                    @if ($circulation > 0)
                        <x-ui.badge etat="attente">{{ $rendu }} / {{ $sorti }} rendu(s)</x-ui.badge>
                    @elseif ($sorti > 0)
                        <x-ui.badge etat="ok">Complet</x-ui.badge>
                    @else
                        <span class="text-[var(--midsp-gris-acier)]">—</span>
                    @endif
                </td>

                <td class="tableau-actions">
                    @can('gerer-materiel')
                        @if ($sorti < $reservation->quantite)
                            <x-ui.action icone="sortie" libelle="Sortie" wire:click="ouvrirSortie({{ $reservation->id }})" texte />
                        @endif

                        @if ($circulation > 0)
                            <x-ui.action icone="retour" libelle="Retour" wire:click="ouvrirRetour({{ $reservation->id }})" texte />
                        @endif
                    @else
                        @if ($sorti === 0)
                            <span class="text-sm text-[var(--midsp-gris)]">Non sorti</span>
                        @endif
                    @endcan
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="7">
                <p>Aucune réservation dans cette catégorie.</p>
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{-- Sortie / retour --}}
    @if ($modaleOuverte && $reservationCourante)
        <x-ui.modale :titre="$typeMouvement === 'sortie' ? 'Sortie de matériel' : 'Retour de matériel'"
                     :sous-titre="$reservationCourante->materiel->designation . ($reservationCourante->materiel->marque ? ' — ' . $reservationCourante->materiel->marque . ' ' . $reservationCourante->materiel->modele : '')"
                     fermer="$set('modaleOuverte', false)">

            <div class="grille-2">
                <x-ui.champ libelle="Quantité" type="number" min="1" wire:model="quantite" :erreur="$errors->first('quantite')" required />

                <div class="champ-bloc">
                    <label for="mouvement-agent" class="libelle">
                        {{ $typeMouvement === 'sortie' ? 'Retiré par' : 'Rendu par' }} <span aria-hidden="true">*</span>
                    </label>
                    <x-ui.selection id="mouvement-agent" wire:model="agent_id" :class="$errors->has('agent_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($agents as $a)
                            <option value="{{ $a->id }}">{{ $a->nom }} {{ $a->prenom }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('agent_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>
            </div>

            @if ($typeMouvement === 'retour')
                <div class="champ-bloc mt-4">
                    <label for="mouvement-etat" class="libelle">État constaté</label>
                    <x-ui.selection id="mouvement-etat" wire:model="etat_constate">
                        @foreach ($etats as $e)
                            <option value="{{ $e->value }}">{{ $e->libelle() }}</option>
                        @endforeach
                    </x-ui.selection>
                    <p class="champ-aide">Un matériel rendu hors service sortira du stock disponible.</p>
                </div>
            @endif

            {{-- Accessoires --}}
            @if ($reservationCourante->materiel->accessoires->isNotEmpty())
                <div class="encadre mt-4">
                    <p class="eyebrow mb-1">
                        Accessoires
                        @if ($typeMouvement === 'retour')
                            — cocher ce qui est revenu
                        @endif
                    </p>

                    <div class="divide-y divide-[var(--midsp-gris-filet)]">
                        @foreach ($reservationCourante->materiel->accessoires as $accessoire)
                            <div wire:key="acc-{{ $accessoire->id }}" class="py-1">
                                <x-ui.case wire:model.live="constats.{{ $accessoire->id }}.present">
                                    {{ $accessoire->libelle }}
                                </x-ui.case>

                                @if (! ($constats[$accessoire->id]['present'] ?? false))
                                    <input type="text"
                                           wire:model="constats.{{ $accessoire->id }}.observation"
                                           placeholder="Manquant — préciser si besoin"
                                           aria-label="Observation sur {{ $accessoire->libelle }}"
                                           class="champ mb-2 text-sm">
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-4">
                <x-ui.champ libelle="Observation" type="textarea" rows="2" wire:model="observation" />
            </div>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">
                    {{ $typeMouvement === 'sortie' ? 'Enregistrer la sortie' : 'Enregistrer le retour' }}
                </x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
