# Symfony 6 REST API — CRUD Livres & Auteurs

> ⚠️ **Projet en cours de développement** — Certaines fonctionnalités peuvent être incomplètes ou nécessiter des ajustements.

API RESTful développée avec **Symfony 6**, gérant les livres et leurs auteurs.

## ✨ Fonctionnalités implémentées

- CRUD complet pour les entités **Book** et **Author**
- Relation Many-to-One / One-to-Many entre Livres et Auteurs
- Sécurité basée sur **JWT** avec rôles (`ROLE_USER`, `ROLE_ADMIN`)
- Validation des données via le composant **Validator**
- Mise en cache des réponses
- Pagination
- HATEOAS / respect du **niveau 3 du modèle de Richardson**

## 🚧 Fonctionnalités en développement

- Tests unitaires et fonctionnels
- Documentation API complète (Swagger/OpenAPI)
- Optimisation des performances
- Gestion avancée des erreurs

## 📦 Technologies

- **PHP** ≥ 8.1
- **Symfony** 6.4
- **Doctrine ORM** 3.6
- **LexikJWTAuthenticationBundle** 3.2
- **Willdurand HateoasBundle** 3.0
- Symfony Validator, Serializer, Security, Cache

## 🚀 Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL
- OpenSSL (pour la génération des clés JWT)

### Étapes d'installation

1. Cloner le dépôt :

```bash
git clone https://github.com/GabrielG-prog/book-api-symfony.git
cd ton-projet
```

2. Installer les dépendances :

```bash
composer install
```

3. Configurer les variables d'environnement :

```bash
cp .env .env.local
# Éditer .env.local avec vos paramètres de base de données
```

4. Créer la base de données :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Générer les clés JWT :

```bash
php bin/console lexik:jwt:generate-keypair
```

6. (Optionnel) Charger les données de test :

```bash
php bin/console doctrine:fixtures:load
```

7. Lancer le serveur de développement :

```bash
symfony server:start
```

L'API sera accessible sur `http://localhost:8000`

## 📚 Utilisation

### Authentification

Pour obtenir un token JWT :

```bash
POST /api/login
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "password"
}
```

Utiliser le token dans les requêtes suivantes :

```bash
Authorization: Bearer {votre-token}
```

### Exemples de requêtes

**Lister tous les livres :**

```bash
GET /api/books
```

**Créer un auteur :**

```bash
POST /api/authors
Content-Type: application/json
Authorization: Bearer {token}

{
  "firstName": "Victor",
  "lastName": "Hugo"
}
```

**Récupérer un livre spécifique :**

```bash
GET /api/books/{id}
```

## 🗂️ Structure du projet

```
src/
├── Controller/     # Contrôleurs de l'API
├── Entity/         # Entités Doctrine (Book, Author)
├── Repository/     # Repositories Doctrine
└── ...

config/
├── packages/       # Configuration des bundles
└── routes/         # Routes de l'API
```

## 🔐 Sécurité

Le projet utilise LexikJWTAuthenticationBundle pour l'authentification. Les endpoints sont protégés selon les rôles :

- `ROLE_USER` : Lecture des ressources
- `ROLE_ADMIN` : Création, modification, suppression

## 🛠️ Développement

**Outils disponibles :**

```bash
# Maker Bundle pour générer du code
php bin/console make:entity
php bin/console make:controller

# Fixtures pour les données de test
php bin/console doctrine:fixtures:load
```

## 📝 À faire

- [ ] Ajouter des tests automatisés
- [ ] Documenter l'API avec OpenAPI/Swagger
- [ ] Implémenter le versioning de l'API
- [ ] Optimiser les requêtes SQL
- [ ] Ajouter des logs détaillés
- [ ] Configurer un système de CI/CD