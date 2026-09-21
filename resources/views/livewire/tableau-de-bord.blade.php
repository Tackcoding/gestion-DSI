<div class="registre">

    {{-- Aujourd'hui --}}
    <section class="registre-section">
        <p class="registre-mention">{{ now()->translatedFormat('l j F Y') }}</p>
        <h2 class="titre">Aujourd'hui</h2>

        <div class="mt-3">
            @forelse ($couverturesDuJour as $couverture)
                <div wire:key="jour-{{ $couverture->id }}"
                     @class([
                        'registre-entree',
                        'registre-entree-active' => $couverture->agents->isNotEmpty(),
                        'registre-entree-alerte' => $couverture->agents->isEmpty(),
                     ])>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <a href="{{ route('evenements.detail', $couverture->evenement) }}" class="tableau-titre">
                                {{ $couverture->evenement->intitule }}
                            </a>
                            <p class="legende mt-0.5">
                                @if ($couverture->heure_depart)
                                    Départ {{ substr($couverture->heure_depart, 0, 5) }}
                                    @if ($couverture->lieu_depart) — {{ $couverture->lieu_depart }} @endif
                                @else
                                    Horaire non précisé
                                @endif
                            </p>
                        </div>

                        <div class="badges sm:justify-end">
                            @forelse ($couverture->agents as $agent)
                                <x-ui.badge etat="ok">{{ $agent->nom }}</x-ui.badge>
                            @empty
                                <x-ui.badge etat="alerte">Aucune équipe affectée</x-ui.badge>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="registre-entree">
                    <p class="text-[var(--midsp-gris)]">
                        Aucune couverture prévue aujourd'hui.
                        <a href="{{ route('evenements.index') }}" class="lien ms-1">Voir les événements</a>
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- À venir --}}
    <section class="registre-section">
        <p class="registre-mention">À venir</p>
        <h2 class="titre">Prochaines couvertures</h2>

        <div class="mt-3">
            @forelse ($prochainesCouvertures as $couverture)
                <div wire:key="prochaine-{{ $couverture->id }}"
                     @class([
                        'registre-entree',
                        'registre-entree-alerte' => $couverture->agents->isEmpty(),
                     ])>
                    <div class="flex items-baseline justify-between gap-4">
                        <a href="{{ route('evenements.detail', $couverture->evenement) }}" class="tableau-titre">
                            {{ $couverture->evenement->intitule }}
                        </a>
                        <span class="legende tableau-periode shrink-0">
                            {{ $couverture->date->translatedFormat('D j M') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="registre-entree">
                    <p class="text-[var(--midsp-gris)]">Rien de programmé pour les jours à venir.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Repères --}}
    <section class="registre-section">
        <p class="registre-mention">État du registre</p>
        <h2 class="titre">Repères</h2>

        <dl class="registre-reperes mt-3">
            <div class="registre-entree">
                <dt>Événements en cours</dt>
                <dd class="titre">{{ $evenementsEnCours }}</dd>
            </div>
            <div class="registre-entree">
                <dt>Agents actifs</dt>
                <dd class="titre">{{ $agentsActifs }}</dd>
            </div>
            <div class="registre-entree">
                <dt>Références de matériel</dt>
                <dd class="titre">{{ $referencesMateriel }}</dd>
            </div>
        </dl>
    </section>

    <div class="registre-cloture"></div>
</div>
