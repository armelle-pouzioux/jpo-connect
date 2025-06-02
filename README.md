
# JPO Connect

Plateforme d'inscription et de gestion des Journées Portes Ouvertes de La Plateforme.

## 🧑‍💻 Technologies utilisées

- **Frontend** : ReactJS
- **Backend** : PHP natif (POO, MVC)
- **Base de données** : MySQL
- **Hébergement** : Plesk
- **Autres** : Jest / PHPUnit, Mail PHP

## 🔧 Fonctionnalités principales

- Inscription et désinscription à une JPO
- Notifications email de rappel
- Moteur de recherche par ville
- Commentaires et modération
- Dashboard admin complet (CRUD, stats, rôles)
- Gestion des rôles (directeur, responsable, salarié)

## 🗂️ Arborescence du projet

```
/frontend      → React app
/backend       → PHP MVC
  /controller
  /model
  /view
  /routes
/database      → Script SQL
/public        → Fichiers accessibles (index.php, assets)
```

## 🛠️ Installation (local)

1. Cloner le dépôt : `git clone https://github.com/prenom-nom/jpo-connect`
2. Lancer `npm install` dans `/frontend`
3. Configurer `/backend/config/config.php` (connexion à la BDD)
4. Importer le fichier SQL fourni dans PhpMyAdmin
5. Lancer `npm run dev` pour le front et ouvrir `/public/index.php` via Plesk pour le back

## 📄 Auteur

- Armelle Pouzioux - Étudiante en développement web - La Plateforme
