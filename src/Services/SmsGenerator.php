<?php

namespace App\Services;

use Twilio\Rest\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SmsGenerator
{
    private $accountSid;
    private $authToken;
    private $fromNumber;
    private $logger;
    
    public function __construct(ParameterBagInterface $params, LoggerInterface $logger) 
    {
        $this->accountSid = $params->get('app.twilio_account_sid');
        $this->authToken = $params->get('app.twilio_auth_token');
        $this->fromNumber = $params->get('app.twilio_from_number');
        $this->logger = $logger;
    }
    
    /**
     * Envoie un SMS via l'API Twilio
     *
     * @param string $number Numéro du destinataire
     * @param string $name Nom de l'expéditeur
     * @param string $text Contenu du message
     * @return array Informations sur le message envoyé
     * @throws \Exception En cas d'erreur d'envoi
     */
    public function sendSms(string $number, string $name, string $text): array
    {
        try {
            // Nettoyage et validation du numéro de téléphone
            $toNumber = trim($number);
            if (empty($toNumber)) {
                throw new \InvalidArgumentException('Le numéro de téléphone ne peut pas être vide');
            }
            
            // Construction du message
            $message = $name . ' vous a envoyé le message suivant: ' . $text;
            
            // Création du client Twilio
            $client = $this->createTwilioClient();
            
            // Envoi du message
            $messageResult = $client->messages->create(
                $toNumber,
                [
                    'from' => $this->fromNumber,
                    'body' => $message,
                ]
            );
            
            $this->logger->info('SMS envoyé avec succès', [
                'sid' => $messageResult->sid,
                'status' => $messageResult->status
            ]);
            
            return [
                'success' => true,
                'sid' => $messageResult->sid,
                'status' => $messageResult->status
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'envoi du SMS', [
                'error' => $e->getMessage(),
                'recipient' => $number
            ]);
            
            throw $e;
        }
    }

    protected function createTwilioClient() {
        return new Client($this->accountSid, $this->authToken);
    }
}


