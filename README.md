Collecte des informations sur l’espace de travailFiltrage des informations les plus pertinentes# Application d'envoi de SMS avec Twilio et Symfony

Cette application web permet d'envoyer des SMS via l'API Twilio dans un environnement Symfony. Elle comprend un formulaire simple pour saisir un numéro de téléphone, le nom de l'expéditeur et le message à envoyer.

## Fonctionnalités

- Formulaire d'envoi de SMS avec validation des champs
- Compteur de caractères en temps réel (limité à 160 caractères)
- Mode de développement qui redirige tous les SMS vers un numéro de test
- Gestion des erreurs et messages flash
- Journalisation des envois et des erreurs
- Tests unitaires complets

## Prérequis

- PHP 8.0 ou supérieur
- Composer
- Symfony CLI
- Compte Twilio avec:
  - SID de compte (Account SID)
  - Token d'authentification (Auth Token)
  - Numéro de téléphone Twilio

## Installation

1. Clonez le dépôt:
   ```bash
   git clone https://github.com/ludovicdevio/symfony-sms-sender
   cd symfony-sms-sender
   ```

2. Installez les dépendances:
   ```bash
   composer install
   ```

3. Créez un fichier `.env.local` en copiant le fichier .env:
   ```bash
   cp .env .env.local
   ```

4. Configurez vos identifiants Twilio dans le fichier `.env.local`:
   ```bash
   ###> symfony/twilio-notifier ###
   TWILIO_DSN=twilio://ACCOUNT_SID:AUTH_TOKEN@default?from=FROM_NUMBER
   twilio_account_sid=VOTRE_ACCOUNT_SID
   twilio_auth_token=VOTRE_AUTH_TOKEN
   twilio_from_number=VOTRE_NUMERO_TWILIO
   twilio_to_number=NUMERO_TEST_POUR_DEV
   ###< symfony/twilio-notifier ###
   ```

## Utilisation

1. Lancez le serveur web:
   ```bash
   symfony server:start -d
   ```

2. Accédez à l'application dans votre navigateur à l'adresse: `https://localhost:8000`

![alt text](sms.png)

3. Remplissez le formulaire avec:
   - Le numéro de téléphone du destinataire (format international recommandé: +33XXXXXXXXX)
   - Le nom de l'expéditeur
   - Le texte du message (limité à 160 caractères)

4. Cliquez sur "Envoyer le SMS" pour envoyer le message

## Mode Développement

En environnement de développement (`APP_ENV=dev`), tous les SMS sont automatiquement redirigés vers le numéro de test configuré dans la variable `twilio_to_number`. Cela vous permet de tester l'application sans risquer d'envoyer des SMS à de vrais utilisateurs pendant la phase de développement.

## Tests

L'application dispose de tests unitaires pour le service SmsGenerator. Pour exécuter les tests:

```bash
php bin/phpunit
```

Les tests vérifient:
- L'envoi correct des SMS
- La validation des numéros de téléphone
- La gestion des erreurs
- Le formatage des messages

## Structure du projet

- SmsController.php: Gère les routes et la logique de l'application
- SmsGenerator.php: Service d'envoi de SMS via Twilio
- SmsType.php: Définition du formulaire d'envoi de SMS
- index.html.twig: Interface utilisateur du formulaire
- SmsGeneratorTest.php: Tests unitaires pour le service d'envoi de SMS

## Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus d'informations.

## Crédits

Développé avec Symfony et l'API Twilio.
