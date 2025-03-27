<?php

namespace App\Controller;

use App\Repository\TicketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ResolvedTicketAndroidController extends AbstractController
{
    #[Route('/api/resolved_ticket_android', name: 'app_resolved_ticket_android')]
    public function resolvedTickets(TicketRepository $ticketRepository): Response
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
        $tickets = $ticketRepository->findAllResolvedTicket($username);
        $resolvedTickets = [];

        if (empty($tickets)) {
            return $this->json([
                'status'  => 0,
                'message' => 'Pas de tickets résolus'
            ]);
        }

        foreach ($tickets as $ticket) {
            $resolvedTickets[] = $ticket->toArrayAndroid() + [
                    'open' => $ticket->isOpenByUser($user)
                ];
        }

        return $this->json([
            'status'         => 1,
            'resolvedTickets' => $resolvedTickets
        ]);
    }
}
