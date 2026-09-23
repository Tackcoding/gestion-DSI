<div class="space-y-5">
    @php
        // Initiale du jour, du lundi (1) au dimanche (7)
        $initiales = [1 => 'L', 2 => 'M', 3 => 'M', 4 => 'J', 5 => 'V', 6 => 'S', 7 => 'D'];
    @endphp

    {{-- Navigation d'un mois à l'autre --}}
    <div class="barre-outils">
        <div class="flex items-center gap-2">
            <x-ui.bouton variante="secondaire" class="btn-icone" wire:click="moisPrecedent">
                <x-ui.icone nom="fleche-gauche" />
                <span class="masque-visuel">Mois précédent</span>
            </x-ui.bouton>

            <h2 class="titre min-w-[12rem] text-center" aria-live="polite">{{ $libelleMois }}</h2>

            <x-ui.bouton variante="secondaire" class="btn-icone" wire:click="moisSuivant">
                <x-ui.icone nom="fleche-gauche" class="rotate-180" />
                <span class="masque-visuel">Mois suivant</span>
            </x-ui.bouton>
        </div>

        @unless ($estMoisCourant)
            <div class="barre-outils-actions">
                <x-ui.bouton variante="discret" icone="calendrier" wire:click="moisCourant">
                    Revenir au mois en cours
                </x-ui.bouton>
            </div>
        @endunless
    </div>

    {{-- Absents du jour : la première question du directeur --}}
    @if ($estMoisCourant)
        <div class="etat etat-information">
            <div>
                @if ($absentsAujourdhui->isEmpty())
                    Personne n'est absent aujourd'hui.
                @else
                    <strong>{{ $absentsAujourdhui->count() }} {{ $absentsAujourdhui->count() > 1 ? 'absents' : 'absent' }} aujourd'hui :</strong>
                    @foreach ($absentsAujourdhui as $ligne)
                        {{ $ligne['agent']->nom }} ({{ mb_strtolower($ligne['type']->libelle) }}){{ $loop->last ? '.' : ',' }}
                    @endforeach
                @endif
            </div>
        </div>
    @endif

    {{-- Grille : une ligne par agent, une colonne par jour --}}
    <div class="tableau-cadre">
        <table class="planning">
            <caption class="masque-visuel">
                Absences de {{ $libelleMois }} : une ligne par agent, une colonne par jour.
            </caption>

            <thead>
                <tr>
                    <th scope="col" class="planning-agent">Agent</th>
                    @foreach ($jours as $jour)
                        <th scope="col" @class([
                                'planning-jour',
                                'planning-weekend'    => $jour->isWeekend(),
                                'planning-aujourdhui' => $jour->isToday(),
                            ])>
                            <span class="planning-initiale" aria-hidden="true">{{ $initiales[$jour->dayOfWeekIso] }}</span>
                            <span>{{ $jour->day }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @forelse ($agents as $agent)
                    <tr wire:key="planning-{{ $agent->id }}">
                        <th scope="row" class="planning-agent">
                            <span class="planning-nom">{{ $agent->nom }}</span>
                            <span class="planning-fonction">{{ $agent->fonction?->libelle }}</span>
                        </th>

                        @foreach ($jours as $jour)
                            @php
                                $demande = $grille[$agent->id][$jour->toDateString()] ?? null;
                                $enAttente = $demande && $demande->statut === \App\Enums\StatutDemandeAbsence::Demandee;
                            @endphp

                            <td @class([
                                    'planning-cellule',
                                    'planning-weekend'    => $jour->isWeekend(),
                                    'planning-aujourdhui' => $jour->isToday(),
                                ])
                                @if ($demande)
                                    title="{{ $agent->nom }} — {{ $demande->type->libelle }}, du {{ $demande->date_debut->format('d/m') }} au {{ $demande->date_fin->format('d/m') }}{{ $enAttente ? ' (en attente)' : '' }}"
                                @endif>
                                @if ($demande)
                                    <span @class([
                                            'planning-case',
                                            'planning-t-' . $demande->type->code,
                                            'planning-attente' => $enAttente,
                                        ]) aria-hidden="true">{{ $lettres[$demande->type->code] ?? '•' }}</span>
                                    <span class="masque-visuel">
                                        {{ $demande->type->libelle }}{{ $enAttente ? ', en attente' : '' }}
                                    </span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $jours->count() + 1 }}" class="vide">Aucun agent actif.</td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <th scope="row" class="planning-agent">Absents</th>
                    @foreach ($jours as $jour)
                        <td @class([
                                'planning-total',
                                'planning-weekend'    => $jour->isWeekend(),
                                'planning-aujourdhui' => $jour->isToday(),
                            ])>
                            {{ $absentsParJour[$jour->toDateString()] ?? '' }}
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Légende --}}
    <div class="planning-legende" aria-label="Légende">
        @foreach ($types as $type)
            <span class="planning-legende-item">
                <span class="planning-case planning-t-{{ $type->code }}" aria-hidden="true">{{ $lettres[$type->code] ?? '•' }}</span>
                {{ $type->libelle }}
            </span>
        @endforeach

        <span class="planning-legende-item">
            <span class="planning-case planning-t-conge_annuel planning-attente" aria-hidden="true">C</span>
            Demande en attente (contour)
        </span>

        <span class="planning-legende-item">
            <span class="planning-case planning-case-weekend" aria-hidden="true"></span>
            Week-end, non décompté
        </span>
    </div>
</div>
