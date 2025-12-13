<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WaitingTicketAndroidController extends AbstractController
{
    #[Route('/api/waiting_ticket_android', name: 'app_waiting_ticket_android')]
    public function waitingTickets(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'status'  => 0,
                'message' => 'Utilisateur non authentifié'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Récupérer les tickets associés à l'utilisateur authentifié
        $username = $user->getUsername();
        $tickets = $ticketRepository->findAllWaitingTicket($username);
        $waitingTickets = [];

        if (empty($tickets)) {
            return $this->json([
                'status'  => 0,
                'message' => 'Pas de tickets en attente'
            ]);
        }

        foreach ($tickets as $ticket) {
            $waitingTickets[] = $ticket->toArrayAndroid() + [
                    'open' => $ticket->isOpenByUser($user)
                ];
        }

        return $this->json([
            'status'         => 1,
            'waitingTickets' => $waitingTickets
        ]);
    }
}
