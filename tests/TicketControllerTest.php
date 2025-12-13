<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;
use App\Entity\User;

class TicketControllerTest extends WebTestCase
{
    private function loginAsUser($client): void
    {
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('CONNEXION')->form([
            'username' => 'user',
            'password' => 'xm7dVM@!pqajU',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/home');

        $client->followRedirect();
    }

    public function testTicketCreation()
    {
        $client = static::createClient();

        $this->loginAsUser($client);

        // Aller sur la page contenant le formulaire
        $crawler = $client->request('GET', '/home');

        // Récupérer le formulaire
        $form = $crawler->selectButton('ENVOYER')->form();

        // Créer un fichier temporaire
        $filePath = sys_get_temp_dir() . '/test_image.jpg';
        file_put_contents($filePath, 'contenu fictif');

        $uploadedFile = new UploadedFile(
            $filePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true
        );

        // Remplir les champs du formulaire
        $form['creation_ticket_form[category]'] = 'Messagerie';
        $form['creation_ticket_form[priority]'] = 'Basse';
        $form['creation_ticket_form[title]'] = 'Ticket de test';
        $form['creation_ticket_form[description]'] = 'Ticket créé via test unitaire';
        $form['creation_ticket_form[imageFile][file]'] = $uploadedFile;

        // Soumettre le formulaire
        $client->submit($form);

        $this->assertResponseRedirects();

        $client->followRedirect();

        // Vérifier en BDD
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy([
            'title' => 'Ticket de test'
        ]);

        $this->assertNotNull($ticket, 'Le ticket doit être enregistré en base.');
        $this->assertEquals('Ticket créé via test unitaire', $ticket->getDescription());

        // Nettoyer le fichier temporaire
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testWaitingTickets()
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findUserByUsername('user');

        $client->loginUser($testUser);

        $client->request('GET', '/home/waiting_tickets');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

        if (!empty($data)) {
            $this->assertArrayHasKey('id', $data[0]);
            $this->assertArrayHasKey('category', $data[0]);
            $this->assertArrayHasKey('priority', $data[0]);
            $this->assertArrayHasKey('title', $data[0]);
            $this->assertArrayHasKey('description', $data[0]);
            $this->assertArrayHasKey('date', $data[0]);
            $this->assertArrayHasKey('image', $data[0]);
            $this->assertArrayHasKey('email', $data[0]);
            $this->assertArrayHasKey('status', $data[0]);
            $this->assertArrayHasKey('answers', $data[0]);
        }
    }

    protected static function getKernelClass(): string
    {
        return \App\Kernel::class;
    }

    protected static function createKernel(array $options = []): \Symfony\Component\HttpKernel\KernelInterface
    {
        return new \App\Kernel('test', true);
    }
}
