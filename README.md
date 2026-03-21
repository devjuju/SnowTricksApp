# 🏂 SnowTricks (Symfony + Docker)

Plateforme collaborative de figures de snowboard développée avec Symfony et conteneurisée avec Docker.

Ce projet permet aux utilisateurs de consulter, créer et commenter des figures de snowboard dans un environnement reproductible et prêt à l’emploi.

---

## 🚀 Fonctionnalités

- 📚 Annuaire des figures de snowboard
- ➕ Création de nouvelles figures
- ✏️ Modification des figures
- 👀 Consultation détaillée d’une figure
- 💬 Système de commentaires par figure
- 🔐 Authentification des utilisateurs
- 🧑‍💻 Gestion des comptes utilisateurs
- 🛠️ Espace administrateur

Framework utilisé : Symfony

---

## 🧱 Architecture

Le projet repose sur une architecture Docker avec plusieurs services :

- **PHP (Symfony)** → exécution de l’application
- **Nginx** → serveur web
- **MySQL** → base de données

---

## 🛠️ Prérequis

- Docker
- Docker Compose

---

## ⚙️ Installation

### 1. Cloner le projet

```bash id="clone"
git clone https://github.com/ton-username/snowtricks.git
cd snowtricks
```

---

### 2. Lancer les conteneurs

```bash id="up"
docker-compose up -d --build
```

---

### 3. Installer les dépendances Symfony

Entrer dans le container PHP :

```bash id="execphp"
docker exec -it snowtricks_php bash
```

Puis :

```bash id="composer"
composer install
```

---

### 4. Configurer la base de données

Créer la base et exécuter les migrations :

```bash id="db"
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

(optionnel) Charger des données :

```bash id="fixtures"
php bin/console doctrine:fixtures:load
```

---

## 🌐 Accès à l’application

Une fois les conteneurs lancés :

👉 http://localhost:8080

---

## ⚙️ Configuration

### Variables d’environnement

Dans `.env` ou `.env.local` :

```env id="env"
DATABASE_URL="mysql://symfony:symfony@database:3306/snowtricks"
```

---

## 🐳 Services Docker

### PHP

- Contient Symfony
- Port interne : 9000

### Nginx

- Expose l’application sur :
  👉 http://localhost:8080

### MySQL

- Host : `database`
- Port : `3306`
- Base : `snowtricks`
- User : `symfony`
- Password : `symfony`

---

## 📁 Structure du projet

```id="structure"
├── docker/
│   ├── php/
│   ├── nginx/
│
├── docker-compose.yml
├── .env
├── src/
├── templates/
├── public/
├── config/
├── migrations/
```

---

## 🧠 Architecture applicative

Le projet suit le pattern MVC :

- **Model** → Doctrine Entities
- **View** → Twig templates
- **Controller** → gestion des routes et logique métier

---

## 🔐 Sécurité

- Authentification Symfony
- Gestion des rôles
- Protection des routes sensibles
- Validation des formulaires

---

## 💬 Système de commentaires

Chaque figure possède un espace de discussion permettant aux utilisateurs d’échanger autour des tricks.

---

## 🚧 Améliorations possibles

- Upload d’images pour les figures
- Pagination des listes
- API REST / GraphQL
- Système de likes
- Notifications utilisateurs
- Modération des commentaires
- Tests automatisés (PHPUnit)

---

## 🧪 Commandes utiles

```bash id="cmd1"
docker-compose up -d --build
docker-compose down
docker-compose logs -f
```

Entrer dans le container PHP :

```bash id="cmd2"
docker exec -it snowtricks_php bash
```

---

## 👨‍💻 Auteur

Projet réalisé dans un cadre pédagogique afin de pratiquer Symfony et Docker dans un environnement proche de la production.

---

## 📄 Licence

Projet libre à usage éducatif et personnel.
