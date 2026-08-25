<div class="space-y-8">

    {{-- Aujourd'hui : la premiere question du responsable en arrivant --}}
    <section>
        <div class="filet flex items-baseline justify-between pb-2">
            <h3 class="titre text-lg">Aujourd'hui</h3>
            <span class="eyebrow">{{ now()->translatedFormat('l j F Y') }}</span>
        </div>

        @forelse ($couverturesDuJour as $couverture)
            <div wire:key="jour-{{ $couverture->id }}" class="carte mt-3 p-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <a href="{{ route('evenements.detail', $couverture->evenement) }}"
                           class="font-medium hover:underline">
                            {{ $couverture->evenement->intitule }}
                        </a>
                        <p class="mt-0.5 text-sm text-[var(--gris)]">
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
            <div class="carte mt-3 p-6 text-sm text-[var(--gris)]">
                Aucune couverture prévue aujourd'hui.
                <a href="{{ route('evenements.index') }}" class="lien-action ms-1">Voir les événements</a>
            </div>
        @endforelse
    </section>

    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Prochaines sorties --}}
        <section class="lg:col-span-2">
            <div class="filet pb-2">
                <h3 class="titre text-lg">Prochaines couvertures</h3>
            </div>

            <div class="carte mt-3 overflow-hidden">
                @forelse ($prochainesCouvertures as $couverture)
                    <div wire:key="prochaine-{{ $couverture->id }}"
                         class="flex items-baseline justify-between gap-4 border-b border-[var(--trait)] px-4 py-3 last:border-0">
                        <a href="{{ route('evenements.detail', $couverture->evenement) }}"
                           class="text-sm hover:underline">
                            {{ $couverture->evenement->intitule }}
                        </a>
                        <span class="shrink-0 text-sm text-[var(--gris)]">
                            {{ $couverture->date->translatedFormat('j M') }}
                        </span>
                    </div>
                @empty
                    <p class="px-4 py-6 text-sm text-[var(--gris)]">
                        Rien de programmé pour les jours à venir.
                    </p>
                @endforelse
            </div>
        </section>

        {{-- Reperes --}}
        <section>
            <div class="filet pb-2">
                <h3 class="titre text-lg">Repères</h3>
            </div>

            <dl class="carte mt-3 divide-y divide-[var(--trait)]">
                <div class="flex items-baseline justify-between px-4 py-3">
                    <dt class="text-sm text-[var(--gris)]">Événements en cours</dt>
                    <dd class="titre text-xl">{{ $evenementsEnCours }}</dd>
                </div>
                <div class="flex items-baseline justify-between px-4 py-3">
                    <dt class="text-sm text-[var(--gris)]">Agents actifs</dt>
                    <dd class="titre text-xl">{{ $agentsActifs }}</dd>
                </div>
                <div class="flex items-baseline justify-between px-4 py-3">
                    <dt class="text-sm text-[var(--gris)]">Références de matériel</dt>
                    <dd class="titre text-xl">{{ $referencesMateriel }}</dd>
                </div>
            </dl>
        </section>
    </div>
</div>
