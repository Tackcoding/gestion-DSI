<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="filet pb-2">
        <h3 class="titre text-lg">Demandes en attente</h3>
    </div>

    <div class="carte overflow-x-auto">
        <table class="tableau min-w-full">
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Type</th>
                    <th>Période</th>
                    <th>Jours</th>
                    <th>Motif</th>
                    <th class="text-right">Décision</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enAttente as $demande)
                    <tr wire:key="val-{{ $demande->id }}">
                        <td>
                            <div class="font-medium">{{ $demande->agent->nom }}</div>
                            <div class="text-sm text-[var(--gris)]">
                                {{ $demande->agent->prenom }} &middot; {{ $demande->agent->fonction->libelle }}
                            </div>
                        </td>
                        <td>{{ $demande->type->libelle }}</td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $demande->date_debut->format('d/m/Y') }}
                            @if (! $demande->date_debut->isSameDay($demande->date_fin))
                                &rarr; {{ $demande->date_fin->format('d/m/Y') }}
                            @endif
                        </td>
                        <td class="text-[var(--gris)]">
                            {{ rtrim(rtrim(number_format($demande->nb_jours, 1, ',', ''), '0'), ',') }}
                        </td>
                        <td class="text-sm text-[var(--gris)]">
                            {{ $demande->motif ?: '—' }}
                            @if ($demande->justificatif_path)
                                <a href="{{ Storage::url($demande->justificatif_path) }}" target="_blank"
                                   class="lien-action block">Justificatif</a>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            <button wire:click="valider({{ $demande->id }})"
                                    class="lien-action">Valider</button>
                            <button wire:click="ouvrirRefus({{ $demande->id }})"
                                    class="lien-alerte ms-4">Refuser</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-[var(--gris)]">
                            Aucune demande en attente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $enAttente->links() }}</div>

    {{-- Modale de refus --}}
    @if ($refusId)
        <div class="voile">
            <div class="modale max-w-md">
                <h3 class="titre text-lg">Refuser la demande</h3>
                <p class="mt-1 text-sm text-[var(--gris)]">
                    Le motif sera visible par l'agent.
                </p>

                <div class="mt-4">
                    <label class="libelle">Motif du refus</label>
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
</div>
