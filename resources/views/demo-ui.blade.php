<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Démo des composants — MIDSP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Styles propres a cette page de demo uniquement (faux en-tete, sections). */
        body { margin: 0; }
        .demo-nav { display: flex; align-items: center; gap: 2rem; height: 64px; padding: 0 1.5rem; background: #fff; border-bottom: 1px solid #E3E0D5; max-width: 80rem; margin: 0 auto; }
        .demo-nav strong { color: #1D561B; font-size: 1.25rem; letter-spacing: .02em; }
        .demo-nav a { color: #1C1C1C; text-decoration: none; padding: 1.25rem 0; }
        .demo-nav a[aria-current] { color: #1D561B; box-shadow: inset 0 -3px 0 #C9A227; }
        .demo-contenu { max-width: 80rem; margin: 0 auto; padding: 0 1.5rem 4rem; }
        .demo-section { margin-top: 3.5rem; }
        .demo-section h2 { font-size: 1.125rem; font-weight: 600; color: #1D561B; margin: 0 0 1rem; padding-bottom: .5rem; border-bottom: 1px solid #C9A227; }
        .demo-rangee { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        .demo-note { font-size: .875rem; color: #66665F; margin: .5rem 0 0; }
        .demo-formulaire { max-width: 28rem; }
    </style>
</head>
<body>

{{-- Faux en-tete, juste pour situer la page. La vraie navigation est dans layouts/navigation.blade.php --}}
<header style="background:#fff">
    <nav class="demo-nav">
        <strong>MIDSP</strong>
        <a href="#">Accueil</a>
        <a href="#" aria-current="page">Événements</a>
        <a href="#">Absences</a>
        <a href="#">Matériel</a>
        <a href="#">Agents</a>
    </nav>
</header>

{{-- Bandeau blanc avec la marge du registre. Pour comparer la variante institutionnelle : class="bandeau bandeau-vert" --}}
<div class="bandeau">
    <div class="bandeau-interieur">
        <div class="bandeau-bloc">
            <p class="bandeau-surtitre">Couverture des événements</p>
            <h1 class="bandeau-titre">Événements</h1>
        </div>
        <div class="bandeau-actions">
            <x-ui.bouton variante="primaire" icone="plus">Nouvel événement</x-ui.bouton>
        </div>
    </div>
</div>

<main class="demo-contenu">

    {{-- ============================================================
         1. La liste, telle qu'elle devrait apparaitre sur /evenements
         ============================================================ --}}
    <div class="barre-outils">
        <div class="barre-outils-filtres">
            <x-ui.recherche placeholder="Intitulé ou lieu…" libelle="Rechercher un événement" />
            <x-ui.selection libelle="Filtrer par statut">
                <option value="">Tous les statuts</option>
                <option>Brouillon</option>
                <option>En attente</option>
                <option>Validé</option>
                <option>Refusé</option>
            </x-ui.selection>
            <span class="barre-outils-compte">4 événements</span>
        </div>
    </div>

    <x-ui.tableau :colonnes="['Événement', 'Période', 'Demandeur', 'Équipe' => 'tableau-nombre', 'Statut', 'Actions' => 'tableau-actions']">
        <tr>
            <td>
                <a href="#" class="tableau-titre">Journée de l'industrie</a>
                <span class="tableau-secondaire">Anosy</span>
            </td>
            <td class="tableau-periode">18/09/2026</td>
            <td>ANDRIAMALALA</td>
            <td class="tableau-nombre">3</td>
            <td><x-ui.badge etat="ok">Validé</x-ui.badge></td>
            <td class="tableau-actions">
                <x-ui.action icone="oeil" libelle="Détail" href="#" />
                <x-ui.action icone="crayon" libelle="Modifier" />
                <x-ui.action icone="corbeille" libelle="Supprimer" danger />
            </td>
        </tr>
        <tr>
            <td>
                <a href="#" class="tableau-titre">Accueil de délégation</a>
                <span class="tableau-secondaire">Ivato</span>
            </td>
            <td class="tableau-periode">22/09/2026 → 23/09/2026</td>
            <td>HANTANIRINA</td>
            <td class="tableau-nombre"><x-ui.badge etat="alerte">Aucune</x-ui.badge></td>
            <td><x-ui.badge etat="ok">Validé</x-ui.badge></td>
            <td class="tableau-actions">
                <x-ui.action icone="oeil" libelle="Détail" href="#" />
                <x-ui.action icone="crayon" libelle="Modifier" />
                <x-ui.action icone="corbeille" libelle="Supprimer" danger />
            </td>
        </tr>
        <tr>
            <td>
                <a href="#" class="tableau-titre">Salon de l'artisanat</a>
                <span class="tableau-secondaire">Tsimbazaza</span>
            </td>
            <td class="tableau-periode">02/10/2026 → 05/10/2026</td>
            <td>NAIVOSON</td>
            <td class="tableau-nombre">2</td>
            <td><x-ui.badge etat="attente">En attente</x-ui.badge></td>
            <td class="tableau-actions">
                <x-ui.action icone="oeil" libelle="Détail" href="#" />
                <x-ui.action icone="crayon" libelle="Modifier" />
                <x-ui.action icone="corbeille" libelle="Supprimer" danger />
            </td>
        </tr>
        <tr>
            <td>
                <a href="#" class="tableau-titre">Conférence de presse</a>
                <span class="tableau-secondaire">Ampandrianomby</span>
            </td>
            <td class="tableau-periode">09/10/2026</td>
            <td>RAKOTOARISOA</td>
            <td class="tableau-nombre">—</td>
            <td><x-ui.badge etat="neutre">Brouillon</x-ui.badge></td>
            <td class="tableau-actions">
                <x-ui.action icone="oeil" libelle="Détail" href="#" />
                <x-ui.action icone="crayon" libelle="Modifier" />
                <x-ui.action icone="corbeille" libelle="Supprimer" danger />
            </td>
        </tr>
    </x-ui.tableau>
    <p class="demo-note">Le titre de chaque ligne mène au détail. « Couvertures » remplace « Couv. » : un événement validé sans couverture est une alerte, pas un zéro. Ici le bouton principal est dans le bandeau ; dans l'application il reste dans la barre d'outils.</p>

    {{-- ============================================================
         2. Etat vide
         ============================================================ --}}
    <section class="demo-section">
        <h2>Quand la liste est vide</h2>
        <x-ui.tableau :colonnes="['Événement', 'Période', 'Demandeur', 'Équipe' => 'tableau-nombre', 'Statut', 'Actions' => 'tableau-actions']">
            <x-ui.vide colspan="6">
                <p>Aucun événement ne correspond à votre recherche.</p>
                <x-ui.bouton variante="secondaire">Effacer les filtres</x-ui.bouton>
            </x-ui.vide>
        </x-ui.tableau>
    </section>

    {{-- ============================================================
         3. Boutons
         ============================================================ --}}
    <section class="demo-section">
        <h2>Boutons</h2>
        <div class="demo-rangee">
            <x-ui.bouton variante="primaire" icone="plus">Nouvel événement</x-ui.bouton>
            <x-ui.bouton variante="secondaire">Exporter</x-ui.bouton>
            <x-ui.bouton variante="discret">Annuler</x-ui.bouton>
            <x-ui.bouton variante="danger" icone="corbeille">Supprimer</x-ui.bouton>
            <x-ui.bouton variante="primaire" disabled>Enregistrer</x-ui.bouton>
            <x-ui.bouton variante="discret" class="btn-icone" icone="fermer"><span class="masque-visuel">Fermer</span></x-ui.bouton>
        </div>
        <p class="demo-note">Une seule action primaire par page. Le rouge n'apparaît que pour supprimer ou refuser. Tabulez pour voir l'anneau de focus doré.</p>
    </section>

    {{-- ============================================================
         4. Champs de formulaire (pour les modales)
         ============================================================ --}}
    <section class="demo-section">
        <h2>Champs de formulaire</h2>
        <div class="demo-formulaire">
            <x-ui.champ libelle="Intitulé" name="intitule" placeholder="Journée de l'industrie" required />
            <x-ui.champ libelle="Lieu" name="lieu" aide="Ville, quartier ou salle" />
            <x-ui.champ libelle="Date de début" name="date_debut" type="date" erreur="La date de début est obligatoire." />
            <div class="champ-bloc">
                <label for="demo-statut" class="libelle">Statut</label>
                <x-ui.selection id="demo-statut" name="statut">
                    <option>Brouillon</option>
                    <option>En attente</option>
                    <option>Validé</option>
                </x-ui.selection>
            </div>
            <x-ui.champ libelle="Observations" name="observations" type="textarea" />
        </div>
    </section>

    {{-- ============================================================
         5. Badges
         ============================================================ --}}
    <section class="demo-section">
        <h2>États</h2>
        <div class="demo-rangee">
            <x-ui.badge etat="ok">Validé</x-ui.badge>
            <x-ui.badge etat="ok">Bon état</x-ui.badge>
            <x-ui.badge etat="attente">En attente</x-ui.badge>
            <x-ui.badge etat="attente">Réservé</x-ui.badge>
            <x-ui.badge etat="alerte">Refusé</x-ui.badge>
            <x-ui.badge etat="alerte">En panne</x-ui.badge>
            <x-ui.badge etat="neutre">Brouillon</x-ui.badge>
            <x-ui.badge etat="neutre">Inactif</x-ui.badge>
        </div>
        <p class="demo-note">Quatre états, quatre couleurs. Un point, pas une pastille : c'est un registre, pas un tableau de bord marketing.</p>
    </section>

</main>
</body>
</html>
