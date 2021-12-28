<?php
namespace App\Tests\EndToEnd;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class AbstractEndToEndTestCase extends PantherTestCase
{
    protected Client $client;

    protected function setUpTestPanther()
    {
        $this->client = static::createPantherClient();
        $this->client->restart();
    }

    protected function loginUser(string $userEmail, string $password = 'password'): void
    {
        // Simulation du log de l'utilisateur
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Connexion')->form();
        $form->setValues([
            'email' => $userEmail,
            'password' => $password,
        ]);
        $this->client->submitForm('Connexion');
        $this->client->waitFor('.navbar', 5);
    }
    

    /**
     * Transforme une date Y-m-d en date américaine
     * pour compenser le fait que je n'arrive pas à configurer
     * le client pour qu'il soit en français
     */
    protected function toFormDate(string $date): string
    {
        return (new \DateTimeImmutable($date))->format("m/d/Y");
    }
}