<?php

namespace App\Controller;

use App\Email\EmailTicket;
use App\Entity\OpenTicket;
use App\Entity\Ticket;
use App\Entity\TicketResponse;
use App\Form\CreationTicketFormType;
use App\Form\ModifyInformationFormType;
use App\Repository\TicketRepository;
use App\Repository\TicketResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/home')]
class LoginController extends AbstractController
{
    public function __construct(private EmailTicket $emailTicket)
    {
    }
    #[Route('', name: 'app_home')]
    public function home(Request $request, EntityManagerInterface $entityManager, TicketRepository $ticketRepository): Response
    {
        $Ticket = new Ticket();
        $form = $this->createForm(CreationTicketFormType::class, $Ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            $Ticket->setUser($user);
            $Ticket->setStatus('En attente');
            $Object = new \DateTime('now');
            $Ticket->setDate($Object);
            $entityManager->persist($Ticket);
            $entityManager->flush();

            $this->emailTicket->sendEmailCreationTicket($user, $ticketRepository,
                (new TemplatedEmail())
                    ->from(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                    ->to(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                    ->subject('Création nouveau ticket')
                    ->htmlTemplate('email/creation_ticket.html.twig')
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('login/home.html.twig', [
            'TicketForm' => $form,
        ]);
    }
    #[Route('/information', name: 'app_modify_information')]
    public function information(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user= $this->getUser();
        $form = $this->createForm(ModifyInformationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('username')->getData()) {
                $user->setUsername($form->get('username')->getData());
                $this->addFlash('success', 'Votre nom a bien été modifié.');
            }

            if ($form->get('plainPassword')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_modify_information');
        }

        return $this->render('modify_information/modifyInformation.html.twig', [
            'modifyInformationForm' => $form,
        ]);
    }
    #[Route('/waiting_tickets', name: 'app_waiting_tickets')]
    public function waitingTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $UserId = $user->getId();
        $AllTickets = $TicketRepository->findWaitingTicketByUserId($UserId);
        $waitingTickets = [];
        foreach ($AllTickets as $ticket) {
            if ($ticket->isOpenByUser($user)) {
                $waitingTickets[] = $ticket->toArray() + ['open' => true];
            }
            else {
                $waitingTickets[] = $ticket->toArray() + ['open' => false];
            }
        }
        return $this->json($waitingTickets);
    }
    #[Route('/inProgress_tickets', name: 'app_inProgress_tickets')]
    public function inProgressTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $UserId = $user->getId();
        $AllTickets = $TicketRepository->findInProgressTicketByUserId($UserId);
        $waitingTickets = [];
        foreach ($AllTickets as $ticket) {
            if ($ticket->isOpenByUser($user)) {
                $waitingTickets[] = $ticket->toArray() + ['open' => true];
            }
            else {
                $waitingTickets[] = $ticket->toArray() + ['open' => false];
            }
        }
        return $this->json($waitingTickets);
    }
    #[Route('/resolved_tickets', name: 'app_resolved_tickets')]
    public function resolvedTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $AllTickets = $TicketRepository->findResolvedTicketByUserId($user->getId());
        $waitingTickets = [];
        foreach ($AllTickets as $ticket) {
            if ($ticket->isOpenByUser($user)) {
                $waitingTickets[] = $ticket->toArray() + ['open' => true];
            }
            else {
                $waitingTickets[] = $ticket->toArray() + ['open' => false];
            }
        }
        return $this->json($waitingTickets);
    }
    #[Route('/open', name: 'app_openTicket')]
    public function openTicket(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $Id = $data['id'];
        $ticket = $ticketRepository->findOneById($Id);
        $user = $this->getUser();
        if (!$ticket->isOpenByUser($user)) {
            $openTicket = new OpenTicket();
            $ticket->addOpen($openTicket);
            $openTicket->setUser($user);
            $entityManager->persist($openTicket);
            $entityManager->flush();
            return $this->json(['success' => 'Ticket ouvert']);
        }
        return $this->json(['error' => 'Ticket déjà ouvert']);
    }
    #[Route('/send_answer', name: 'app_send_answer')]
    public function sendAnswer(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $answer = new TicketResponse();
        $contenu = $data['answer'];
        $answer->setContenu($contenu);
        $id = $data['id'];
        $ticket = $ticketRepository->findOneById($id);
        $title = $ticket->getTitle();
        $user = $this->getUser();
        $answer->setUser($user);
        $answer->setTicket($ticket);
        $entityManager->persist($answer);
        $entityManager->flush();
        $this->emailTicket->sendEmailModificationStatusTicket($user, $id, $title,
            (new TemplatedEmail())
                ->from(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                ->to(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                ->subject('Réponse d\'un utilisateur')
                ->htmlTemplate('email/answer_admin.html.twig')
        );
        return $this->json(['success' => 'Réponse envoyée']);
    }
}
