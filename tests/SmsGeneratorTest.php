<?php

namespace App\Tests;

use App\Services\SmsGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Twilio\Rest\Client;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Api\V2010\Account\MessageInstance;

// Classe d'extension pour les tests
class TestingSmsGenerator extends SmsGenerator {
    private $mockedClient;
    
    public function setMockedClient($client) {
        $this->mockedClient = $client;
    }
    
    protected function createTwilioClient() {
        if ($this->mockedClient) {
            return $this->mockedClient;
        }
        return parent::createTwilioClient();
    }
}

class SmsGeneratorTest extends TestCase
{
    private $parameterBag;
    
    /**
     * @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $logger;
    private $smsGenerator;
    private $twilioClient;
    private $messageList;
    
    protected function setUp(): void
    {
        // Créer ParameterBag avec valeurs de test
        $paramBag = new ParameterBag([
            'app.twilio_account_sid' => 'test_account_sid',
            'app.twilio_auth_token' => 'test_auth_token',
            'app.twilio_from_number' => 'test_from_number'
        ]);
        
        // Mock du logger
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        // Mock pour Twilio
        $this->messageList = $this->createMock(MessageList::class);
        $this->twilioClient = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->twilioClient->method('__get')
            ->with('messages')
            ->willReturn($this->messageList);
        
        // Créer notre service avec la classe de test
        $this->smsGenerator = new TestingSmsGenerator($paramBag, $this->logger);
        $this->smsGenerator->setMockedClient($this->twilioClient);
    }
    
    public function testSendSmsSuccessfully()
    {
        // Arrange
        $number = '+33612345678';
        $name = 'Test Sender';
        $text = 'Test message';
        
        // Créer un mock de MessageInstance au lieu de stdClass
        $messageResult = $this->getMockBuilder(MessageInstance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        
        // Configurez la méthode __get pour renvoyer les valeurs appropriées
        $messageResult->method('__get')
            ->will($this->returnValueMap([
                ['sid', 'test_sid'],
                ['status', 'delivered']
            ]));
        
        $this->messageList->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($number),
                $this->equalTo([
                    'from' => 'test_from_number',
                    'body' => 'Test Sender vous a envoyé le message suivant: Test message'
                ])
            )
            ->willReturn($messageResult);
            
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                $this->equalTo('SMS envoyé avec succès'),
                $this->callback(function($context) {
                    return $context['sid'] === 'test_sid' && $context['status'] === 'delivered';
                })
            );
            
        // Act
        $result = $this->smsGenerator->sendSms($number, $name, $text);
        
        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('test_sid', $result['sid']);
        $this->assertEquals('delivered', $result['status']);
    }
    
    public function testSendSmsEmptyNumberThrowsException()
    {
        // Arrange
        $number = '  '; // Numéro vide avec espaces
        $name = 'Test Sender';
        $text = 'Test message';
        
        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le numéro de téléphone ne peut pas être vide');
        
        $this->smsGenerator->sendSms($number, $name, $text);
    }
    
    public function testSendSmsHandlesException()
    {
        // Arrange
        $number = '+33612345678';
        $name = 'Test Sender';
        $text = 'Test message';
        
        $this->messageList->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('Test exception'));
            
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Erreur lors de l\'envoi du SMS'),
                $this->callback(function($context) use ($number) {
                    return $context['error'] === 'Test exception' && $context['recipient'] === $number;
                })
            );
            
        // Assert & Act
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        $this->smsGenerator->sendSms($number, $name, $text);
    }
    
    public function testSmsMessageFormat()
    {
        // Arrange
        $number = '+33612345678';
        $name = 'John Doe';
        $text = 'Hello, this is a test';
        $expectedMessage = 'John Doe vous a envoyé le message suivant: Hello, this is a test';
        
        $messageResult = $this->getMockBuilder(MessageInstance::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        
        // Configurez la méthode __get pour renvoyer les valeurs appropriées
        $messageResult->method('__get')
            ->will($this->returnValueMap([
                ['sid', 'test_sid'],
                ['status', 'sent']
            ]));
        
        $this->messageList->expects($this->once())
            ->method('create')
            ->with(
                $this->anything(),
                $this->callback(function($params) use ($expectedMessage) {
                    return $params['body'] === $expectedMessage;
                })
            )
            ->willReturn($messageResult);
            
        // Act
        $this->smsGenerator->sendSms($number, $name, $text);
        
        // No assert needed as the expectation is in the mock setup
    }
}