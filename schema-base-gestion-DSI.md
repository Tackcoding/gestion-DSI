# Schéma de base de données — gestion-DSI

Application de gestion des événements, du matériel et des absences
MIDSP / DVEC — Laravel 12 + MySQL 8

---

## 1. Vue d'ensemble

Trois blocs, articulés autour d'une entité centrale : **agent**.

| Bloc | Tables |
|---|---|
| Socle | `services`, `fonctions`, `users`, `agents` |
| Événements & matériel | `evenements`, `couvertures`, `couverture_agent`, `materiels`, `reservations`, `mouvements` |
| Absences | `types_absence`, `demandes_absence`, `droits_conges` |

Le lien entre les deux modules métier : **un agent en absence validée ne peut pas être mobilisé sur une couverture.**

---

## 2. Socle

### services

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| code | VARCHAR(20) | UNIQUE |
| libelle | VARCHAR(150) | NOT NULL |
| actif | BOOLEAN | DEFAULT true |
| timestamps | | |

### fonctions

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| libelle | VARCHAR(150) | NOT NULL, UNIQUE |
| timestamps | | |

Valeurs issues de la liste RH : Chef de service, Directeur Comm, Chargé de Comm, Secrétaire qualifié, Dépositaire comptable.

### users

Table fournie par Breeze, complétée d'une colonne de rôle.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| name | VARCHAR(255) | NOT NULL |
| email | VARCHAR(255) | UNIQUE |
| password | VARCHAR(255) | NOT NULL |
| role | VARCHAR(30) | NOT NULL, DEFAULT 'agent' |
| actif | BOOLEAN | DEFAULT true |
| timestamps, remember_token | | |

`role` est piloté par un Enum PHP (voir §5).

### agents

La personne physique. Distincte du compte de connexion : tous les agents existent dans le système, tous n'ont pas forcément de compte.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| im | VARCHAR(20) | NULL, UNIQUE |
| nom | VARCHAR(100) | NOT NULL |
| prenom | VARCHAR(150) | NOT NULL |
| fonction_id | BIGINT UNSIGNED | FK → fonctions, RESTRICT |
| service_id | BIGINT UNSIGNED | FK → services, RESTRICT |
| user_id | BIGINT UNSIGNED | NULL, UNIQUE, FK → users, SET NULL |
| telephone | VARCHAR(30) | NULL |
| actif | BOOLEAN | DEFAULT true |
| timestamps, softDeletes | | |

> `im` est **nullable** : un agent de la liste RH n'en a pas encore.
> `UNIQUE` sur une colonne nullable en MySQL autorise plusieurs NULL — c'est le comportement voulu.

**Index** : `(service_id, actif)`, `(nom, prenom)`

---

## 3. Module événements & matériel

### evenements

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| intitule | VARCHAR(255) | NOT NULL |
| description | TEXT | NULL |
| lieu | VARCHAR(255) | NULL |
| date_debut | DATE | NOT NULL |
| date_fin | DATE | NOT NULL |
| statut | VARCHAR(20) | NOT NULL, DEFAULT 'brouillon' |
| demandeur_id | BIGINT UNSIGNED | FK → agents, RESTRICT |
| timestamps, softDeletes | | |

**Index** : `(date_debut, date_fin)`, `(statut)`
**Règle applicative** : `date_fin >= date_debut`

### couvertures

Une sortie terrain datée, rattachée à un événement. Un événement pluriannuel ou multi-jours en compte plusieurs (le CEO Summit du fichier Excel en a deux).

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| evenement_id | BIGINT UNSIGNED | FK → evenements, CASCADE |
| date | DATE | NOT NULL |
| heure_depart | TIME | NULL |
| lieu_depart | VARCHAR(255) | NULL |
| heure_retour | TIME | NULL |
| lieu_retour | VARCHAR(255) | NULL |
| observation | TEXT | NULL |
| timestamps | | |

**Index** : `(evenement_id)`, `(date)`

### couverture_agent

Table pivot : l'équipe mobilisée. Remplace la cellule texte « Mahery, Andri, Ando » de l'Excel actuel, qui n'est pas exploitable.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| couverture_id | BIGINT UNSIGNED | FK → couvertures, CASCADE |
| agent_id | BIGINT UNSIGNED | FK → agents, CASCADE |
| role_sur_couverture | VARCHAR(50) | NULL |
| timestamps | | |

**Contrainte** : `UNIQUE (couverture_id, agent_id)` — un agent n'est mobilisé qu'une fois sur la même couverture.

C'est cette table qui permet de répondre à « combien de sorties pour tel agent ce mois-ci ? » et « qui était présent le 9 avril ? ».

### materiels

Gestion **par quantité**, pas par numéro d'inventaire : l'inventaire réel (Roll up ×2, Cubes ×3…) ne distingue pas les exemplaires.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| designation | VARCHAR(255) | NOT NULL |
| description | TEXT | NULL |
| quantite_totale | SMALLINT UNSIGNED | NOT NULL, DEFAULT 1 |
| etat | VARCHAR(20) | NOT NULL, DEFAULT 'bon' |
| actif | BOOLEAN | DEFAULT true |
| timestamps, softDeletes | | |

Données initiales : Roll up MIDSP (2), Light box (1), Oriflamme MIDSP support béton (2), Lettrine MIDSP (1), Cubes MIDSP (3).

### reservations

L'**intention** d'utiliser du matériel. Table à part entière et non simple pivot, car elle porte ses propres attributs.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| evenement_id | BIGINT UNSIGNED | FK → evenements, CASCADE |
| materiel_id | BIGINT UNSIGNED | FK → materiels, RESTRICT |
| quantite | SMALLINT UNSIGNED | NOT NULL, DEFAULT 1 |
| date_debut | DATETIME | NOT NULL |
| date_fin | DATETIME | NOT NULL |
| statut | VARCHAR(20) | NOT NULL, DEFAULT 'demandee' |
| demandeur_id | BIGINT UNSIGNED | FK → agents, RESTRICT |
| validateur_id | BIGINT UNSIGNED | NULL, FK → agents, SET NULL |
| valide_le | DATETIME | NULL |
| motif_refus | TEXT | NULL |
| timestamps | | |

**Index** : `(materiel_id, date_debut, date_fin)`, `(evenement_id)`, `(statut)`

> Les dates sont propres à la réservation, pas héritées de l'événement : on retire souvent le matériel la veille et on le rend le lendemain.

### mouvements

Le **fait physique**. Distinct de la réservation : réservé ≠ sorti.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| reservation_id | BIGINT UNSIGNED | FK → reservations, RESTRICT |
| type | VARCHAR(10) | NOT NULL — `sortie` \| `retour` |
| quantite | SMALLINT UNSIGNED | NOT NULL |
| date_mouvement | DATETIME | NOT NULL |
| agent_id | BIGINT UNSIGNED | FK → agents, RESTRICT — qui prend/rend |
| etat_constate | VARCHAR(20) | NULL |
| observation | TEXT | NULL |
| timestamps | | |

**Index** : `(reservation_id, type)`

Ce découpage répond directement à « où est le light box aujourd'hui ? » :
sortie sans retour associé = matériel en circulation.

---

## 4. Module absences

### types_absence

Table de référence — un administrateur doit pouvoir en ajouter.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| code | VARCHAR(30) | UNIQUE |
| libelle | VARCHAR(150) | NOT NULL |
| decompte_solde | BOOLEAN | DEFAULT true |
| quota_type | VARCHAR(20) | `bloc` \| `fil_de_eau` \| `aucun` |
| necessite_justificatif | BOOLEAN | DEFAULT false |
| actif | BOOLEAN | DEFAULT true |
| timestamps | | |

| code | libelle | decompte | quota_type |
|---|---|---|---|
| conge_bloc | Congé annuel (bloc) | oui | bloc |
| conge_courant | Congé au fil de l'eau | oui | fil_de_eau |
| permission | Permission | non | aucun |
| maladie | Absence maladie | oui | fil_de_eau |
| formation | Formation / mission externe | non | aucun |
| non_justifiee | Absence non justifiée | non | aucun |

### demandes_absence

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| agent_id | BIGINT UNSIGNED | FK → agents, CASCADE |
| type_id | BIGINT UNSIGNED | FK → types_absence, RESTRICT |
| date_debut | DATE | NOT NULL |
| date_fin | DATE | NOT NULL |
| demi_journee | BOOLEAN | DEFAULT false |
| nb_jours | DECIMAL(4,1) | NOT NULL — calculé à l'enregistrement |
| motif | TEXT | NULL |
| origine | VARCHAR(15) | `demande` \| `constat` |
| statut | VARCHAR(20) | DEFAULT 'demandee' |
| validateur_id | BIGINT UNSIGNED | NULL, FK → agents, SET NULL |
| valide_le | DATETIME | NULL |
| motif_refus | TEXT | NULL |
| justificatif_path | VARCHAR(255) | NULL |
| timestamps, softDeletes | | |

**Index** : `(agent_id, date_debut, date_fin)`, `(statut)`, `(type_id)`

> `origine` distingue la demande faite par l'agent du constat posé par un responsable (absence non justifiée), qui n'a pas de workflow de validation.
> `demi_journee` porte la permission d'une demi-journée mentionnée dans les règles internes.

### droits_conges

Les droits annuels. Le **solde restant n'est pas stocké** : il se calcule à partir des demandes validées, sinon il devient faux à la première correction rétroactive.

| Colonne | Type | Contraintes |
|---|---|---|
| id | BIGINT UNSIGNED | PK, auto |
| agent_id | BIGINT UNSIGNED | FK → agents, CASCADE |
| annee | SMALLINT UNSIGNED | NOT NULL |
| jours_bloc | DECIMAL(4,1) | DEFAULT 15.0 |
| jours_fil_de_eau | DECIMAL(4,1) | DEFAULT 15.0 |
| timestamps | | |

**Contrainte** : `UNIQUE (agent_id, annee)`

---

## 5. Enums PHP (pas de tables)

Ces valeurs pilotent du code : le typage PHP les protège mieux qu'une table.

```php
enum StatutEvenement: string {
    case Brouillon = 'brouillon';
    case Valide    = 'valide';
    case EnCours   = 'en_cours';
    case Termine   = 'termine';
    case Annule    = 'annule';
}

enum StatutReservation: string {
    case Demandee = 'demandee';
    case Validee  = 'validee';
    case Refusee  = 'refusee';
    case Annulee  = 'annulee';
}

enum StatutDemandeAbsence: string {
    case Demandee = 'demandee';
    case Validee  = 'validee';
    case Refusee  = 'refusee';
    case Annulee  = 'annulee';
}

enum TypeMouvement: string {
    case Sortie = 'sortie';
    case Retour = 'retour';
}

enum EtatMateriel: string {
    case Bon         = 'bon';
    case Moyen       = 'moyen';
    case HorsService = 'hors_service';
}

enum RoleUtilisateur: string {
    case Agent          = 'agent';
    case ChefService    = 'chef_service';
    case Directeur      = 'directeur';
    case Depositaire    = 'depositaire';
    case Administrateur = 'administrateur';
}
```

En revanche `services`, `fonctions` et `types_absence` **restent des tables** : leurs valeurs sont libres, administrables, et aucun code ne dépend d'une valeur précise.

---

## 6. Règles métier structurantes

### 6.1 Disponibilité d'un agent

Un agent est disponible sur `[debut, fin]` s'il n'a **ni** absence validée **ni** couverture qui chevauche la période.

```
chevauchement ⟺ debut_A < fin_B  ET  fin_A > debut_B
```

« En mission » ne se saisit jamais : c'est déduit de `couverture_agent`. Le stocker en double garantirait deux versions contradictoires.

### 6.2 Disponibilité du matériel

```
quantite_disponible = materiels.quantite_totale
                    − Σ reservations.quantite
                      WHERE materiel_id = X
                        AND statut = 'validee'
                        AND chevauchement avec la période demandée
```

**Point d'attention MySQL** : contrairement à PostgreSQL, MySQL ne sait pas empêcher les chevauchements au niveau de la base. La vérification et l'insertion doivent donc être enveloppées dans une transaction avec verrou :

```php
DB::transaction(function () use ($data) {
    $materiel = Materiel::lockForUpdate()->find($data['materiel_id']);
    // calcul de la quantité déjà réservée sur la période
    // rejet si dépassement
    // création de la réservation
});
```

Sans ce verrou, deux demandes simultanées sur le dernier light box passeraient toutes les deux.

### 6.3 Solde de congés

```
solde_bloc = droits_conges.jours_bloc
           − Σ demandes_absence.nb_jours
             WHERE agent_id = X
               AND annee(date_debut) = A
               AND statut = 'validee'
               AND type.quota_type = 'bloc'
```

Idem pour `fil_de_eau`. Toujours calculé, jamais stocké.

---

## 7. Cardinalités

```
services      1 ──── n  agents
fonctions     1 ──── n  agents
users         1 ──── 0..1 agents

agents        1 ──── n  evenements        (demandeur)
evenements    1 ──── n  couvertures
couvertures   n ──── n  agents            (via couverture_agent)

evenements    1 ──── n  reservations
materiels     1 ──── n  reservations
reservations  1 ──── n  mouvements

agents        1 ──── n  demandes_absence
types_absence 1 ──── n  demandes_absence
agents        1 ──── n  droits_conges     (une ligne par année)
```

---

## 8. Points à valider auprès de la DSI

1. **Niveaux de validation** — le chef de service valide-t-il pour son service, avec un directeur au-dessus ? Détermine les rôles et les règles d'accès.
2. **Matériel cassé ou non rendu** — quelle procédure ? Le champ `etat_constate` suffit-il, ou faut-il un signalement formel ?
3. **Prêt à d'autres directions** — possible ? Si oui, `reservations` a besoin d'un emprunteur externe.
4. **Absence non justifiée** — qui la constate, et l'agent peut-il la contester ?
5. **Droits de congés** — 15 + 15 est-il uniforme, ou variable selon l'ancienneté ou le statut administratif ?
