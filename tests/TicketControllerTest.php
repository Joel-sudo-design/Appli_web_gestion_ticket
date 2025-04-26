<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Ticket;
use App\Entity\User; // Vérifiez que le namespace correspond à votre entité utilisateur

class TicketControllerTest extends WebTestCase
{
    public function testTicketCreation()
    {
        $client = static::createClient();

        // Simuler l'authentification en récupérant un utilisateur de test
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findUserByUsername('user');
        $client->loginUser($testUser);

        // Accède à la page protégée /home
        $crawler = $client->request('GET', '/home');

        // Vérifie que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Sélectionne le bouton "ENVOYER" pour obtenir le formulaire
        $buttonCrawlerNode = $crawler->selectButton('ENVOYER');
        $form = $buttonCrawlerNode->form();

        // Remplissage des champs du formulaire
        $form['creation_ticket_form[category]'] = 'Messagerie';
        $form['creation_ticket_form[priority]'] = 'Basse';
        $form['creation_ticket_form[title]'] = 'Ticket de test';
        $form['creation_ticket_form[description]'] = 'Ticket créé via test unitaire';

        // Simulation d'un upload de fichier
        $filePath = sys_get_temp_dir() . '/test_image.jpg';
        file_put_contents($filePath, 'contenu fictif');
        $uploadedFile = new UploadedFile(
            $filePath,
            'test_image.jpg',
            'image/jpeg',
            null,
            true // Mode test : on évite la vérification de l'existence réelle du fichier
        );
        $form['creation_ticket_form[imageFile][file]'] = $uploadedFile;

        // Soumission du formulaire
        $client->submit($form);

        // Vérifie que la réponse est une redirection après soumission
        $this->assertResponseRedirects();

        // Suit la redirection
        $client->followRedirect();

        // Récupère l'EntityManager pour vérifier l'insertion en base
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $ticket = $entityManager->getRepository(Ticket::class)->findOneBy([
            'title' => 'Ticket de test'
        ]);

        // Vérifie que le ticket a bien été créé en base
        $this->assertNotNull($ticket, 'Le ticket doit être enregistré en base de données.');
        $this->assertEquals(
            'Ticket créé via test unitaire',
            $ticket->getDescription(),
            'La description du ticket correspond à celle renseignée dans le formulaire.'
        );

        // Vérification de la méthode toArray()
        $ticketArray = $ticket->toArray();

        $this->assertArrayHasKey('id', $ticketArray);
        $this->assertArrayHasKey('category', $ticketArray);
        $this->assertArrayHasKey('priority', $ticketArray);
        $this->assertArrayHasKey('title', $ticketArray);
        $this->assertArrayHasKey('description', $ticketArray);
        $this->assertArrayHasKey('date', $ticketArray);
        $this->assertArrayHasKey('image', $ticketArray);
        $this->assertArrayHasKey('email', $ticketArray);
        $this->assertArrayHasKey('status', $ticketArray);
        $this->assertArrayHasKey('answers', $ticketArray);

        $this->assertEquals('Messagerie', $ticketArray['category']);
        $this->assertEquals('Basse', $ticketArray['priority']);
        $this->assertEquals('Ticket de test', $ticketArray['title']);
        $this->assertEquals('Ticket créé via test unitaire', $ticketArray['description']);
        $this->assertEquals($ticket->getImageName(), $ticketArray['image']);
        $this->assertEquals($ticket->getStatus(), $ticketArray['status']);
        $this->assertEquals($testUser->getEmailAddress(), $ticketArray['email']);
        $this->assertIsArray($ticketArray['answers'], 'La clé "answers" doit être un tableau.');

        // Nettoyage du fichier temporaire
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function testWaitingTickets()
    {
        $client = static::createClient();

        // Simuler l'authentification en récupérant un utilisateur de test
        $userRepository = static::getContainer()->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findUserByUsername('user');
        $client->loginUser($testUser);

        // Effectuer une requête GET vers la route /waiting_tickets
        $client->request('GET', '/home/waiting_tickets');

        // Vérifie que la réponse est réussie (HTTP 200)
        $this->assertResponseIsSuccessful();

        // Vérifie que la réponse est au format JSON
        $content = $client->getResponse()->getContent();
        $this->assertJson($content, 'La réponse doit être un JSON.');

        // Décodage de la réponse JSON
        $data = json_decode($content, true);
        $this->assertIsArray($data, 'La réponse décodée doit être un tableau.');

        // Vérifier la structure d'un ticket si des tickets existent
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
