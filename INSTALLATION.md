# Installation — Application de gestion des événements, du matériel et des absences

MIDSP — Direction de la Veille Économique et de la Communication

---

## 1. Prérequis

| Élément | Version minimale | Vérification |
|---|---|---|
| PHP | 8.2 | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL | 8.0 | `mysql --version` |
| Apache | 2.4 | `apache2 -v` |
| Node.js | 20 | `node -v` — uniquement pour compiler les ressources |

**Extensions PHP requises** (`php -m` pour les lister) :

```
bcmath  ctype  curl  dom  fileinfo  gd  json  mbstring
openssl  pcre  pdo  pdo_mysql  tokenizer  xml  zip
```

L'extension `gd` est indispensable : elle sert à la génération des
procès-verbaux en PDF.

---

## 2. Récupération du projet

Depuis le dépôt Git :

```bash
git clone <url-du-depot> gestion-midsp
cd gestion-midsp
```

Ou par archive : décompresser, puis se placer dans le dossier.

---

## 3. Dépendances

```bash
composer install --no-dev --optimize-autoloader
```

L'option `--no-dev` exclut les outils de développement ; elle est
attendue en production.

> **Si Composer ne peut pas s'exécuter sur le serveur**, exécuter la
> commande sur une machine disposant de PHP 8.2, puis transférer le
> dossier `vendor/` avec le reste du projet.

---

## 4. Base de données

Créer la base et son utilisateur :

```sql
CREATE DATABASE midsp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'midsp'@'localhost' IDENTIFIED BY 'mot_de_passe_a_definir';
GRANT ALL PRIVILEGES ON midsp.* TO 'midsp'@'localhost';
FLUSH PRIVILEGES;
```

---

## 5. Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Puis renseigner `.env` :

```ini
APP_NAME="Gestion MIDSP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://adresse-du-serveur

APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=midsp
DB_USERNAME=midsp
DB_PASSWORD=mot_de_passe_a_definir

SESSION_DRIVER=database
```

**`APP_DEBUG=false` est impératif.** À `true`, une erreur affiche le
contenu de la configuration, mots de passe compris.

---

## 6. Structure de la base

```bash
php artisan migrate --force
php artisan db:seed --force
```

`--force` est nécessaire en production : sans lui, Laravel demande une
confirmation interactive.

Les données initiales chargées sont les référentiels : services,
fonctions, catégories de matériel, types d'absence, ainsi que les onze
agents de la direction et l'inventaire.

**Le compte administrateur créé par les données initiales utilise un
mot de passe de développement.** Le remplacer immédiatement :

```bash
php artisan tinker
```

```php
App\Models\User::where('email','tack@midsp.mg')->update([
    'password' => bcrypt('un_mot_de_passe_solide'),
]);
```

---

## 7. Stockage des justificatifs

```bash
php artisan storage:link
```

Sans ce lien, les justificatifs d'absence ne s'ouvriront pas.

---

## 8. Ressources front

Si Node est disponible sur le serveur :

```bash
npm install
npm run build
```

Sinon, compiler sur une autre machine et transférer le dossier
`public/build/`.

---

## 9. Droits

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

Remplacer `www-data` par l'utilisateur sous lequel tourne Apache.

Ce sont les deux seuls dossiers que l'application doit pouvoir écrire.

---

## 10. Apache

Le `DocumentRoot` doit pointer sur **`public/`**, et non sur la racine
du projet.

```apache
<VirtualHost *:80>
    ServerName gestion.midsp.local
    DocumentRoot /var/www/gestion-midsp/public

    <Directory /var/www/gestion-midsp/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/gestion-midsp-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-midsp-access.log combined
</VirtualHost>
```

Activer la réécriture d'URL :

```bash
a2enmod rewrite
systemctl reload apache2
```

> **Point de sécurité.** Si le `DocumentRoot` pointe sur la racine du
> projet au lieu de `public/`, le fichier `.env` devient accessible
> depuis un navigateur — avec le mot de passe de la base. C'est le point
> à vérifier en premier après la mise en ligne.

---

## 11. Optimisation

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

À relancer après toute modification de la configuration ou des routes.

---

## 12. Vérifications après installation

| # | Contrôle | Attendu |
|---|---|---|
| 1 | Ouvrir `https://adresse/` | Redirection vers la page de connexion |
| 2 | Se connecter en administrateur | Arrivée sur le tableau de bord |
| 3 | Ouvrir `/agents` | Les onze agents apparaissent |
| 4 | Ouvrir `/materiels` | L'inventaire apparaît |
| 5 | Ouvrir `https://adresse/.env` | **Erreur 403 ou 404** |
| 6 | Générer un procès-verbal | Le PDF s'ouvre, logo et sceau visibles |

Le contrôle 5 est le plus important. S'il affiche le contenu du fichier,
arrêter l'installation et corriger le `DocumentRoot`.

---

## 13. Sauvegarde

À prévoir quotidiennement :

```bash
mysqldump -u midsp -p midsp > /sauvegardes/midsp-$(date +%F).sql
tar czf /sauvegardes/justificatifs-$(date +%F).tar.gz storage/app/public
```

La base contient les réservations, les absences et les procès-verbaux.
Le dossier `storage/app/public` contient les justificatifs d'absence.

---

## 14. En cas de problème

| Symptôme | Cause probable |
|---|---|
| Page blanche | `storage/` non accessible en écriture |
| Erreur 500 sans message | `APP_DEBUG=false` — consulter `storage/logs/laravel.log` |
| Styles absents | `public/build/` manquant — relancer `npm run build` |
| Classe introuvable | Sensibilité à la casse sous Linux — vérifier les noms de fichiers |
| PDF sans images | Chemins absolus — vérifier `public/img/` |
| Session perdue à chaque page | Table `sessions` absente — relancer les migrations |

Les journaux applicatifs se trouvent dans `storage/logs/laravel.log`.

---

## 15. Contact

Application développée dans le cadre d'un stage à la Direction de la
Veille et de la Communication.

Pour toute question sur le fonctionnement métier, se référer au cahier
des charges qui accompagne cette documentation.
