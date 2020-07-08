## Formation Cheffe de Projet Multimédia - Option Développement - OpenClassrooms
### Projet n°4 : Billetterie du Louvre

Projet développé avec le framework Symfony

### Cahier des charges

#### Objectif du projet : 
Créer un nouveau système de réservation et de gestion des tickets en ligne pour diminuer les longues files d’attente et tirer parti de l’usage croissant des smartphones.

#### Contraintes :

Types de billet : Possibilité de choisir un billet “journée” ou “demi-journée”. Le billet “demi-journée” permet un accès au musée à partir de 14h.

Fermeture du musée :
Fermeture du musée les mardis, le 1er mai, le 1er novembre et le 25 décembre.

##### Tarifs :  
* de 0 à 3 ans : gratuit  
* de 4 à 11 ans : 8€/jour, 4€/demi-journée  
* de 12 à 59 ans : 16€/jour, 8€/demi-journée  
* à partir de 60 ans : 12€/jour, 6€/demi-journée  
* un tarif réduit pour les étudiants, demandeurs d’emploi, employés du musée ou d’un service du Ministère de la Culture, militaire) : 10€/jour, 5€/demi-journée

##### Réservation et paiement :  
* Pas de réservation possible les jours passés, les dimanches, les jours fériés, et les jours où plus de 1000 billets ont été vendus  
* Pas de réservation de billet “journée” après 14h  
* Paiement par carte bancaire avec Stripe et redirection vers le site après succès du paiement  
* Confirmation de l’achat des billets par email - Pas de création de compte nécessaire

#### Spécifications techniques

Utilisation du framework Symfony version 5.0.4

##### Bundles : 
* Système de paiement : Stripe  
* Générateur d’url : UrlGeneratorInterface  
* Envoi de mail : Swiftmailer  
* Création numéro unique de commande : Ramsey/Uuid-doctrine  
* Tests unitaires : PHPUnit  

Utilisation du template Astral, sur le site de templates www.html5up.net
