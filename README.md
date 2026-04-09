# 🏂 SnowTricks (Symfony + Docker)

[![Codacy Badge]](https://app.codacy.com/gh/devjuju/SnowTricksApp/dashboard)

Application collaborative autour des figures de snowboard développée avec Symfony.

## 📌 Contexte

Ce projet a été réalisé pour Jimmy Sweat, entrepreneur passionné de snowboard, souhaitant créer une plateforme collaborative pour :

- découvrir des figures (tricks)
- permettre aux utilisateurs d’en ajouter
- échanger via un système de commentaires

## 🧩 Fonctionnalités principales

- 📋 Liste des figures (page d’accueil)
- ➕ Création de figure
- ✏️ Modification de figure
- 👁️ Consultation d’une figure
- 💬 Système de commentaires
- 🔐 Authentification utilisateur

## 🚀 Stack technique

- Backend : Symfony
- Frontend : Tailwind CSS (compilé en local)
- Environnement : Docker
- Base de données : MySQL 8
- Assets : Node.js 20
- Emails : MailHog

## 📊 Qualité du code

Le projet a obtenu un **Grade A sur Codacy**, garantissant un haut niveau de qualité et de bonnes pratiques.

- 🔍 Analyse statique automatique (Codacy)
- 🧹 Code propre et maintenable
- 📏 Respect des standards Symfony
- 🧱 Architecture MVC claire
- 🔐 Bonnes pratiques de sécurité

👉 Rapport complet : https://app.codacy.com/gh/devjuju/SnowTricksApp/dashboard

## 🐳 Démarrage rapide (TL;DR)

```bash
git clone https://github.com/devjuju/SnowTricksApp.git
cd snowtricks

docker-compose up -d --build

docker-compose exec php composer install
docker-compose exec node npm install
docker-compose exec node npm run build

docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console doctrine:fixtures:load
```

👉 Application disponible sur : **http://localhost:8187**

## ⚙️ Installation détaillée

<details> <summary>📦 Voir les étapes complètes</summary>

1. Lancer les conteneurs

```bash
docker-compose up -d --build
```

2. Installer les dépendances

```bash
docker-compose exec php composer install
docker-compose exec node npm install
```

3. Compiler les assets (Tailwind)

```bash
docker-compose exec node npm run build
```

Ou en mode développement :

```bash
docker-compose exec node npm run dev
```

4. Configuration de la base de données
   Vérifier votre fichier .env ou .env.local :

```env
DATABASE_URL="mysql://snowtricks_user:snowtricks_pass@database:3306/snowtricks_db"
```

5. Base de données

```bash
docker-compose exec php php bin/console doctrine:database:create
docker-compose exec php php bin/console doctrine:migrations:migrate
docker-compose exec php php bin/console doctrine:fixtures:load
```

</details>

## 🌐 Accès aux services

| Service     | URL                                            |
| ----------- | ---------------------------------------------- |
| Application | [http://localhost:8187](http://localhost:8187) |
| phpMyAdmin  | [http://localhost:8186](http://localhost:8186) |
| MailHog     | [http://localhost:8125](http://localhost:8125) |

## 🔐 Accès base de données (phpMyAdmin)

- Serveur : database
- Utilisateur : root
- Mot de passe : snowtricks_pass

## 👤 Comptes de test

| Rôle   | Pseudo     | Email                | Mot de passe |
| ------ | ---------- | -------------------- | ------------ |
| Member | JimmySweat | jimmy@snowtricks.com | Snow2025!    |
| Member | SnowFox    | snowfox@tricks.com   | FoxRider123  |

## 🏗️ Architecture du projet

```text
.
├── app/
├── php/
├── apache/
├── mysql/
├── docker-compose.yml
```

Architecture basée sur le pattern MVC de Symfony :

- Controller : gestion des requêtes
- Entity : représentation des données
- Repository : accès base de données
- Twig : rendu des vues

## 🐳 Architecture Docker

| Service    | Description                       |
| ---------- | --------------------------------- |
| php        | Apache + PHP (Symfony)            |
| node       | Compilation des assets (Tailwind) |
| database   | MySQL 8                           |
| phpmyadmin | Interface base de données         |
| mailhog    | Test des emails                   |

## 🧪 Commandes utiles

```bash
# Accéder au conteneur PHP
docker-compose exec php bash

# Accéder au conteneur Node
docker-compose exec node sh

# Voir les logs
docker-compose logs -f

# Stopper les conteneurs
docker-compose down

# Reset complet (⚠️ supprime la base)
docker-compose down -v
```

## 🔒 Sécurité

- Authentification utilisateur
- Protection CSRF
- Validation des formulaires
- Gestion des rôles

## 📈 Qualité du code

- Respect des standards Symfony
- Code structuré (MVC)
- Utilisation de Doctrine ORM
- Analyse via SymfonyInsight / Codacy

## 💡 Choix techniques

### 🐳 Docker

- Environnement reproductible
- Aucune configuration locale requise
- Lancement rapide pour l’évaluateur

### 🎨 Tailwind CSS (sans CDN)

- Compilation via Node.js
- Meilleures performances
- Approche moderne du CSS

## 🚀 Améliorations possibles

- Pagination des figures
- API REST
- Tests automatisés
- Amélioration UX/UI

## ⚠️ En cas de problème

```bash
docker-compose down -v
docker-compose up -d --build
```

Puis relancer :

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate
```

## 👨‍💻 Auteur

Projet réalisé dans le cadre de la formation OpenClassrooms.
