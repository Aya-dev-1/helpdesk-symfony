# Guide de Test du Système HelpDesk

## 1. Test de Connexion

### Comptes de test disponibles :
- **Admin** : `admin@helpdesk.com` / `password123`
- **Technicien** : `tech@helpdesk.com` / `password123`
- **Utilisateur 1** : `user1@helpdesk.com` / `password123` (Sara)
- **Utilisateur 2** : `user2@helpdesk.com` / `password123` (Anas)

### URLs à tester :
1. **Page de connexion** : http://127.0.0.1:8000/login
   - Testez le bouton "Voir/Masquer" pour le mot de passe
   - Vérifiez la validation du formulaire
   - Testez les messages d'erreur avec des identifiants incorrects

## 2. Test du Tableau de Bord

### Pour chaque rôle :
- **Utilisateur** : Vérifiez vos propres statistiques
- **Technicien** : Vérifiez les tickets qui vous sont assignés
- **Admin** : Vérifiez toutes les statistiques globales

URL : http://127.0.0.1:8000/

## 3. Test des Tickets

### 3.1 Création de ticket
URL : http://127.0.0.1:8000/ticket/new

Testez :
- Création avec tous les champs
- Upload de fichiers (PDF, images)
- Validation des champs requis
- Redirection après création

### 3.2 Liste des tickets
URL : http://127.0.0.1:8000/ticket/

Vérifiez :
- Les filtres par statut
- Les badges de couleur selon la priorité
- Les icônes Bootstrap
- L'état "vide" si aucun ticket

### 3.3 Détails d'un ticket
Cliquez sur un ticket dans la liste.

Testez :
- Affichage des détails
- Ajout de commentaires
- Changement de statut (si technicien)
- Upload de fichiers dans les commentaires

## 4. Test des Commentaires

Dans un ticket :
1. Ajoutez un commentaire en tant qu'utilisateur
2. Connectez-vous comme technicien et répondez
3. Vérifiez que les notifications sont envoyées

## 5. Test des Rôles

### Sécurité à vérifier :
- Un utilisateur ne peut pas voir les tickets des autres
- Un technicien ne voit que ses tickets assignés
- Un admin peut tout voir
- Les pages nécessitent une authentification

## 6. Test des Notifications

Vérifiez dans le dossier `var/mail/` que les emails sont créés :
- Quand un ticket est créé
- Quand un commentaire est ajouté

## 7. Test Responsive

Testez sur mobile/tablette :
- Le menu hamburger fonctionne
- Les tableaux s'adaptent
- Les formulaires sont utilisables

## 8. Test des Données de Test

Vérifiez que les fixtures ont bien chargé :
- 4 utilisateurs (Admin, Amine, Sara, Anas)
- 5 tickets avec différents statuts
- Commentaires sur certains tickets

## Commandes Utiles

```bash
# Vider le cache
php bin/console cache:clear

# Recharger les fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Voir les routes
php bin/console debug:router

# Voir les logs
tail -f var/log/dev.log
```

## Problèmes Courants

1. **Erreur "file scheme not supported"** : ✓ Corrigé (MAILER_DSN=null://null)
2. **Page blanche** : Vérifiez les logs dans `var/log/dev.log`
3. **Erreur 500** : Vérifiez que la base de données est bien créée
4. **Problème de connexion** : Vérifiez que les fixtures sont chargées

## Bonnes Pratiques à Vérifier

- ✅ Code sécurisé (hashage des mots de passe)
- ✅ Validation des formulaires
- ✅ Gestion des erreurs
- ✅ Code bien structuré (MVC)
- ✅ Utilisation de Bootstrap 5
- ✅ Responsive design
- ✅ Flash messages utilisateur
- ✅ Role-based access control