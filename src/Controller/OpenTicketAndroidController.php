<?php

namespace App\Controller;

use App\Entity\OpenTicket;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OpenTicketAndroidController extends AbstractController
{
    #[Route('/api/open_ticket_android', name: 'app_open_ticket_android')]
    public function openTicketAndroid(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status'  => 0,
                'message' => 'Utilisateur non authentifié'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = $request->getContent();
        $data = json_decode($data, true);
        $Id = $data['id'];
        $ticket = $ticketRepository->findOneById($Id);
        $user = $ticket->getUser();
        if (!$ticket->isOpenByUser($user)) {
            $openTicket = new OpenTicket();
            $ticket->addOpen($openTicket);
            $openTicket->setUser($user);
            $entityManager->persist($openTicket);
            $entityManager->flush();
        }
        return $this->json(['success' => "Ticket ouvert avec succès"]);
    }
}
