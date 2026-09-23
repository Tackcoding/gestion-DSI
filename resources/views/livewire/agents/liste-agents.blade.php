<div class="space-y-4">

    @if (session('message'))
        <div class="message">{{ session('message') }}</div>
    @endif

    @if (session('erreur'))
        <div class="message-erreur">{{ session('erreur') }}</div>
    @endif

    <div class="barre-outils">
        <div class="barre-outils-filtres">
            <x-ui.recherche wire:model.live.debounce.300ms="recherche"
                            placeholder="Nom, prénom ou IM…"
                            libelle="Rechercher un agent" />

            <x-ui.selection wire:model.live="filtreFonction" libelle="Filtrer par fonction">
                <option value="">Toutes les fonctions</option>
                @foreach ($fonctions as $f)
                    <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                @endforeach
            </x-ui.selection>

            @if (method_exists($agents, 'total'))
                <span class="barre-outils-compte">
                    {{ $agents->total() }} {{ $agents->total() > 1 ? 'agents' : 'agent' }}
                </span>
            @endif
        </div>

        <div class="barre-outils-actions">
            <x-ui.bouton variante="primaire" icone="plus" wire:click="ouvrirCreation">
                Nouvel agent
            </x-ui.bouton>
        </div>
    </div>

    <x-ui.tableau :colonnes="['IM', 'Agent', 'Fonction', 'Service', 'Statut', 'Compte', 'Actions' => 'tableau-actions']">
        @forelse ($agents as $agent)
            <tr wire:key="agent-{{ $agent->id }}">
                <td class="tableau-periode text-[var(--midsp-gris)]">{{ $agent->im ?? '—' }}</td>

                <td>
                    <span class="tableau-titre">{{ $agent->nom }}</span>
                    <span class="tableau-secondaire">{{ $agent->prenom }}</span>
                </td>

                <td>{{ $agent->fonction->libelle }}</td>
                <td>{{ $agent->service->code }}</td>

                <td>
                    <x-ui.badge :etat="$agent->actif ? 'ok' : 'neutre'">
                        {{ $agent->actif ? 'Actif' : 'Inactif' }}
                    </x-ui.badge>
                </td>

                {{-- Compte de connexion --}}
                <td>
                    @if ($agent->user)
                        <x-ui.badge :etat="$agent->user->actif ? 'ok' : 'neutre'">
                            {{ $agent->user->actif ? $agent->user->role->libelle() : 'Désactivé' }}
                        </x-ui.badge>
                    @elseif ($agent->codesActivation->isNotEmpty())
                        <x-ui.badge etat="attente">Code en attente</x-ui.badge>
                    @else
                        <span class="text-[var(--midsp-gris-acier)]">—</span>
                    @endif
                </td>

                <td class="tableau-actions">
                    @can('gerer-comptes')
                        <x-ui.action icone="cle" libelle="Compte" wire:click="ouvrirCompte({{ $agent->id }})" />
                    @endcan
                    <x-ui.action icone="crayon" libelle="Modifier" wire:click="ouvrirEdition({{ $agent->id }})" texte />
                    <x-ui.action icone="corbeille" libelle="Supprimer" wire:click="confirmerSuppression({{ $agent->id }})" danger />
                </td>
            </tr>
        @empty
            <x-ui.vide colspan="7">
                @if ($recherche || $filtreFonction)
                    <p>Aucun agent ne correspond à votre recherche.</p>
                @else
                    <p>Aucun agent enregistré pour le moment.</p>
                    <x-ui.bouton variante="secondaire" icone="plus" wire:click="ouvrirCreation">
                        Ajouter le premier agent
                    </x-ui.bouton>
                @endif
            </x-ui.vide>
        @endforelse
    </x-ui.tableau>

    {{ $agents->links('pagination.midsp') }}

    {{-- Création / modification --}}
    @if ($modaleOuverte)
        <x-ui.modale :titre="$agentId ? 'Modifier l\'agent' : 'Nouvel agent'"
                     largeur="xl" fermer="$set('modaleOuverte', false)">

            <div class="grille-2">
                <x-ui.champ libelle="Nom" wire:model="nom" :erreur="$errors->first('nom')" required />
                <x-ui.champ libelle="Prénom" wire:model="prenom" :erreur="$errors->first('prenom')" required />
            </div>

            <div class="grille-2 mt-4">
                <x-ui.champ libelle="IM" wire:model="im" :erreur="$errors->first('im')" aide="Immatriculation, si l'agent en a une" />
                <x-ui.champ libelle="Téléphone" type="tel" wire:model="telephone" />
            </div>

            <div class="grille-2 mt-4">
                <div class="champ-bloc">
                    <label for="agent-fonction" class="libelle">Fonction <span aria-hidden="true">*</span></label>
                    <x-ui.selection id="agent-fonction" wire:model="fonction_id" :class="$errors->has('fonction_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($fonctions as $f)
                            <option value="{{ $f->id }}">{{ $f->libelle }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('fonction_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>

                <div class="champ-bloc">
                    <label for="agent-service" class="libelle">Service <span aria-hidden="true">*</span></label>
                    <x-ui.selection id="agent-service" wire:model="service_id" :class="$errors->has('service_id') ? 'champ-erreur' : ''">
                        <option value="">— Choisir —</option>
                        @foreach ($services as $sv)
                            <option value="{{ $sv->id }}">{{ $sv->libelle }}</option>
                        @endforeach
                    </x-ui.selection>
                    @error('service_id') <p class="champ-message">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-2">
                <x-ui.case wire:model="actif">Agent actif</x-ui.case>
            </div>

            {{-- Carrière : reprise sur le formulaire officiel de congé / permission.
                 Tout est facultatif ; une information absente reste en pointillés sur le PDF. --}}
            <fieldset class="encadre mt-4">
                <legend class="eyebrow px-1">Carrière</legend>
                <p class="champ-aide mt-0">Reprise sur le formulaire officiel de congé et de permission.</p>

                <div class="grille-2 mt-3">
                    <x-ui.champ libelle="Grade" wire:model="grade" :erreur="$errors->first('grade')" />
                    <x-ui.champ libelle="Chapitre" wire:model="chapitre" :erreur="$errors->first('chapitre')" />
                </div>

                <div class="grille-3 mt-4">
                    <x-ui.champ libelle="Classe" wire:model="classe" :erreur="$errors->first('classe')" />
                    <x-ui.champ libelle="Échelon" wire:model="echelon" :erreur="$errors->first('echelon')" />
                    <x-ui.champ libelle="Indice" wire:model="indice" :erreur="$errors->first('indice')" />
                </div>

                <div class="mt-4">
                    <x-ui.champ libelle="Date d'entrée dans l'Administration" type="date"
                                wire:model="date_entree_administration"
                                :erreur="$errors->first('date_entree_administration')" />
                </div>
            </fieldset>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('modaleOuverte', false)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="primaire" wire:click="enregistrer" wire:loading.attr="disabled">Enregistrer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif

    {{-- Confirmation de suppression --}}
    @if ($suppressionId)
        <x-ui.modale titre="Supprimer cet agent ?" largeur="md" fermer="$set('suppressionId', null)">
            <p class="text-sm text-[var(--midsp-gris)]">
                Il disparaîtra des listes. La suppression est logique et réversible.
            </p>

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="$set('suppressionId', null)">Annuler</x-ui.bouton>
                <x-ui.bouton variante="danger" icone="corbeille" wire:click="supprimer">Supprimer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
    {{-- Compte de connexion : directeur et administrateur --}}
    @if ($compteAgent)
        <x-ui.modale :titre="'Accès de ' . $compteAgent->nom_complet" fermer="fermerCompte">

            {{-- Code qui vient d'être généré : affiché une seule fois --}}
            @if ($codeGenere)
                <div class="encart">
                    <p class="font-medium">Code d'accès à transmettre à l'agent :</p>
                    <p class="my-2 font-mono text-3xl font-bold tracking-[0.2em] text-[var(--midsp-vert-profond)]">{{ $codeGenere }}</p>
                    <p>Valable une seule fois, jusqu'au {{ $codeExpireLe }}.</p>
                    <p class="mt-1">
                        <strong>Notez-le maintenant : il ne sera plus affiché.</strong>
                        L'agent l'utilise sur la page de connexion, lien « Première connexion ? Activer mon compte ».
                    </p>
                </div>
            @endif

            @if ($compteAgent->user)
                {{-- L'agent a déjà un compte --}}
                <dl class="mt-4 space-y-1 text-sm">
                    <div class="flex gap-2"><dt class="w-24 text-[var(--midsp-gris)]">E-mail</dt><dd>{{ $compteAgent->user->email }}</dd></div>
                    <div class="flex gap-2"><dt class="w-24 text-[var(--midsp-gris)]">Rôle</dt><dd>{{ $compteAgent->user->role->libelle() }}</dd></div>
                    <div class="flex gap-2">
                        <dt class="w-24 text-[var(--midsp-gris)]">État</dt>
                        <dd>
                            <x-ui.badge :etat="$compteAgent->user->actif ? 'ok' : 'neutre'">
                                {{ $compteAgent->user->actif ? 'Actif' : 'Désactivé' }}
                            </x-ui.badge>
                        </dd>
                    </div>
                </dl>

                @if ($peutGererCompte)
                    <div class="encadre mt-4">
                        <div class="grille-2 items-end">
                            <div class="champ-bloc">
                                <label for="compte-role" class="libelle">Changer le rôle</label>
                                <x-ui.selection id="compte-role" wire:model="nouveauRole" :class="$errors->has('nouveauRole') ? 'champ-erreur' : ''">
                                    @foreach ($rolesAttribuables as $r)
                                        <option value="{{ $r->value }}">{{ $r->libelle() }}</option>
                                    @endforeach
                                </x-ui.selection>
                            </div>
                            <x-ui.bouton variante="secondaire" wire:click="changerRole">Enregistrer le rôle</x-ui.bouton>
                        </div>
                        @error('nouveauRole') <p class="champ-message">{{ $message }}</p> @enderror
                    </div>

                    <div class="encadre mt-4">
                        <p class="text-sm">
                            <strong>Mot de passe oublié ?</strong>
                            Un nouveau code permet à l'agent de choisir un nouveau mot de passe. Son rôle ne change pas.
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <x-ui.bouton variante="secondaire" icone="cle" wire:click="genererCode">Générer un code de réinitialisation</x-ui.bouton>
                            @if ($compteAgent->user->actif)
                                <x-ui.bouton variante="danger" wire:click="basculerActivation"
                                             wire:confirm="Désactiver ce compte ? L'agent ne pourra plus se connecter.">
                                    Désactiver le compte
                                </x-ui.bouton>
                            @else
                                <x-ui.bouton variante="secondaire" wire:click="basculerActivation">Réactiver le compte</x-ui.bouton>
                            @endif
                        </div>
                        @error('roleCode') <p class="champ-message">{{ $message }}</p> @enderror
                    </div>
                @else
                    <p class="encart encart-neutre mt-4">
                        @if ($compteAgent->user->id === auth()->id())
                            C'est votre propre compte : modifiez votre e-mail et votre mot de passe depuis « Mon compte ».
                        @else
                            Seul l'administrateur peut modifier ce compte.
                        @endif
                    </p>
                @endif
            @else
                {{-- Pas encore de compte --}}
                @if ($codeEnAttente && ! $codeGenere)
                    <div class="encart encart-neutre">
                        Un code est déjà en attente (rôle {{ $codeEnAttente->role->libelle() }}),
                        valable jusqu'au {{ $codeEnAttente->expire_le->format('d/m/Y à H:i') }}.
                        En générer un nouveau annule le précédent.
                        <button type="button" wire:click="annulerCode" class="lien ms-1 text-sm">Annuler ce code</button>
                    </div>
                @endif

                @unless ($codeGenere)
                    <p class="mt-4 text-sm text-[var(--midsp-gris)]">
                        Cet agent n'a pas encore de compte. Choisissez son rôle et générez un code :
                        il choisira lui-même son e-mail et son mot de passe.
                    </p>

                    <div class="grille-2 mt-4 items-end">
                        <div class="champ-bloc">
                            <label for="compte-role-code" class="libelle">Rôle</label>
                            <x-ui.selection id="compte-role-code" wire:model="roleCode" :class="$errors->has('roleCode') ? 'champ-erreur' : ''">
                                @foreach ($rolesAttribuables as $r)
                                    <option value="{{ $r->value }}">{{ $r->libelle() }}</option>
                                @endforeach
                            </x-ui.selection>
                        </div>
                        <x-ui.bouton variante="primaire" icone="cle" wire:click="genererCode" wire:loading.attr="disabled">Générer le code</x-ui.bouton>
                    </div>
                    @error('roleCode') <p class="champ-message">{{ $message }}</p> @enderror
                @endunless
            @endif

            <x-slot:pied>
                <x-ui.bouton variante="secondaire" wire:click="fermerCompte">Fermer</x-ui.bouton>
            </x-slot:pied>
        </x-ui.modale>
    @endif
</div>
