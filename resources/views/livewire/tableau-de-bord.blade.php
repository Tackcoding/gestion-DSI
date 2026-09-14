<div class="registre">

    {{-- Aujourd'hui : la premiere question du responsable en arrivant --}}
    <section class="registre-section">
        <p class="registre-mention">{{ now()->translatedFormat('l j F Y') }}</p>
        <h3 class="titre">Aujourd'hui</h3>

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
                            <a href="{{ route('evenements.detail', $couverture->evenement) }}"
                               class="font-medium">
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

                        <div class="flex flex-wrap gap-1">
                            @forelse ($couverture->agents as $agent)
                                <span class="badge badge-ok">{{ $agent->nom }}</span>
                            @empty
                                <span class="badge badge-alerte">Aucune équipe affectée</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="registre-entree">
                    <p class="text-[var(--midsp-gris)]">
                        Aucune couverture prévue aujourd'hui.
                        <a href="{{ route('evenements.index') }}" class="lien-action ms-1">
                            Voir les événements
                        </a>
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Ce qui vient --}}
    <section class="registre-section">
        <p class="registre-mention">À venir</p>
        <h3 class="titre">Prochaines couvertures</h3>

        <div class="mt-3">
            @forelse ($prochainesCouvertures as $couverture)
                <div wire:key="prochaine-{{ $couverture->id }}" class="registre-entree">
                    <div class="flex items-baseline justify-between gap-4">
                        <a href="{{ route('evenements.detail', $couverture->evenement) }}">
                            {{ $couverture->evenement->intitule }}
                        </a>
                        <span class="legende shrink-0">
                            {{ $couverture->date->translatedFormat('j M') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="registre-entree">
                    <p class="text-[var(--midsp-gris)]">
                        Rien de programmé pour les jours à venir.
                    </p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- L'etat du registre --}}
    <section class="registre-section">
        <p class="registre-mention">État du registre</p>
        <h3 class="titre">Repères</h3>

        <dl class="mt-3">
            <div class="registre-entree flex items-baseline justify-between">
                <dt class="text-[var(--midsp-gris)]">Événements en cours</dt>
                <dd class="titre">{{ $evenementsEnCours }}</dd>
            </div>
            <div class="registre-entree flex items-baseline justify-between">
                <dt class="text-[var(--midsp-gris)]">Agents actifs</dt>
                <dd class="titre">{{ $agentsActifs }}</dd>
            </div>
            <div class="registre-entree flex items-baseline justify-between">
                <dt class="text-[var(--midsp-gris)]">Références de matériel</dt>
                <dd class="titre">{{ $referencesMateriel }}</dd>
            </div>
        </dl>
    </section>

    {{-- Le registre se ferme --}}
    <div class="registre-cloture"></div>
</div>
