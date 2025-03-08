<?php

namespace App\Controller;

use App\Form\SmsType;
use App\Services\SmsGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class SmsController extends AbstractController
{
    private $logger;
    private $params;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;
        $this->params = $params;
    }

    //La vue du formulaire d'envoi du sms
    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SmsType::class);
        
        return $this->render('sms/index.html.twig', [
            'form' => $form->createView(),
            'smsSent' => false
        ]);
    }

    //Gestion de l'envoi du sms
    #[Route('/sendSms', name: 'send_sms', methods: ['POST'])]
    public function sendSms(Request $request, SmsGenerator $smsGenerator): Response
    {
        $form = $this->createForm(SmsType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $number = $data['number'];
            $name = $data['name'];
            $text = $data['text'];
            
            // En environnement de test, on utilise le numéro de test configuré
            if ($this->getParameter('app.env') === 'dev') {
                $number = $this->params->get('app.twilio_to_number');
            }
            
            try {
                // Appel du service
                $smsGenerator->sendSms($number, $name, $text);
                
                $this->addFlash('success', 'Le SMS a été envoyé avec succès.');
                $this->logger->info('SMS envoyé', [
                    'recipient' => $number,
                    'sender' => $name,
                    'length' => strlen($text)
                ]);
                
                // Redirection pour éviter la re-soumission du formulaire
                return $this->redirectToRoute('app_home', ['smsSent' => true]);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi: ' . $e->getMessage());
                $this->logger->error('Échec de l\'envoi SMS', [
                    'error' => $e->getMessage(),
                    'recipient' => $number
                ]);
            }
        }
        
        return $this->render('sms/index.html.twig', [
            'form' => $form->createView(),
            'smsSent' => false
        ]);
    }
}