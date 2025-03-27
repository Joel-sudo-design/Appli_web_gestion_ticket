<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InProgressTicketAndroidController extends AbstractController
{
    #[Route('/api/in_progress_ticket_android', name: 'app_in_progress_ticket_android')]
    public function inProgressTickets(TicketRepository $ticketRepository): Response
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
        $tickets = $ticketRepository->findAllinProgressTicket($username);
        $inProgressTickets= [];

        if (empty($tickets)) {
            return $this->json([
                'status'  => 0,
                'message' => 'Pas de tickets en cours'
            ]);
        }

        foreach ($tickets as $ticket) {
            $inProgressTickets[] = $ticket->toArrayAndroid() + [
                    'open' => $ticket->isOpenByUser($user)
                ];
        }

        return $this->json([
            'status'         => 1,
            'inProgressTickets' => $inProgressTickets
        ]);
    }
}
