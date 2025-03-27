<?php

namespace App\Controller;

use App\Email\EmailTicket;
use App\Entity\OpenTicket;
use App\Entity\Ticket;
use App\Entity\TicketResponse;
use App\Entity\User;
use App\Form\CreationTicketFormType;
use App\Form\ModifyInformationFormType;
use App\Form\RegistrationFormType;
use App\Repository\OpenTicketRepository;
use App\Repository\TicketRepository;
use App\Repository\TicketResponseRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    public function __construct(private EmailTicket $emailTicket, private EmailVerifier $emailVerifier)
    {
    }
    #[Route('', name: 'app_admin')]
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

            return $this->redirectToRoute('app_admin');
        }

        return $this->render('login/admin.html.twig', [
            'TicketForm' => $form,
        ]);
    }
    #[Route('/information', name: 'app_modify_information_admin')]
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
            return $this->redirectToRoute('app_modify_information_admin');
        }

        return $this->render('modify_information/modifyInformationAdmin.html.twig', [
            'modifyInformationForm' => $form,
        ]);
    }
    #[Route('/statistic', name: 'app_statistic_admin')]
    public function statistic(): Response
    {
        return $this->render('statistic/statistic.html.twig');
    }
    #[Route('/waiting_tickets', name: 'app_admin_waiting_tickets')]
    public function waitingTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $AllTickets = $TicketRepository->findAllWaitingTicket('En attente');
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
    #[Route('/inProgress_tickets', name: 'app_admin_inProgress_tickets')]
    public function inProgressTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $AllTickets = $TicketRepository->findAllinProgressTicket(value: 'En cours');
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
    #[Route('/resolved_tickets', name: 'app_admin_resolved_tickets')]
    public function resolvedTicket(TicketRepository $TicketRepository): Response
    {
        $user = $this->getUser();
        $AllTickets = $TicketRepository->findAllResolvedTicket('Résolu');
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
    #[Route('/open', name: 'app_admin_openTicket')]
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
    #[Route('/status_inProgress_ticket', name: 'app_admin_status_inProgress_ticket')]
    public function StatusInProgressTicket(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager, OpenTicketRepository $openTicketRepository): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $id = $data['id'];
        $ticket = $ticketRepository->findOneById($id);
        $title = $ticket->getTitle();
        $user = $ticket->getUser();
        $ticket ->setStatus('En cours');
        $openTicket = $openTicketRepository->findAllByTicketId($id);
        foreach ($openTicket as $open) {
            $entityManager->remove($open);
        }
        $entityManager->persist($ticket);
        $entityManager->flush();
        $this->emailTicket->sendEmailModificationStatusTicket($user, $id, $title,
            (new TemplatedEmail())
                ->from(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                ->to($user->getEmailAddress())
                ->subject('Avancement de votre ticket')
                ->htmlTemplate('email/in_progress_ticket.html.twig')
        );
        return $this->json(['success' => 'Ticket en cours']);
    }
    #[Route('/status_resolved_ticket', name: 'app_admin_status_resolved_ticket')]
    public function statusResolvedTicket(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager, OpenTicketRepository $openTicketRepository): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $id = $data['id'];
        $ticket = $ticketRepository->findOneById($id);
        $title = $ticket->getTitle();
        $user = $ticket->getUser();
        $ticket ->setStatus('Résolu');
        $openTicket = $openTicketRepository->findAllByTicketId($id);
        foreach ($openTicket as $open) {
            $entityManager->remove($open);
        }
        $entityManager->persist($ticket);
        $entityManager->flush();
        $this->emailTicket->sendEmailModificationStatusTicket($user, $id, $title,
            (new TemplatedEmail())
                ->from(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                ->to($user->getEmailAddress())
                ->subject('Avancement de votre ticket')
                ->htmlTemplate('email/resolved_ticket.html.twig')
        );
        return $this->json(['success' => 'Ticket résolu']);
    }
    #[Route('/send_answer', name: 'app_admin_send_answer')]
    public function sendAnswer(Request $request, TicketRepository $ticketRepository, EntityManagerInterface $entityManager, TicketResponseRepository $responseRepository): Response
    {
        $data = $request->getContent();
        $data = json_decode($data, true);
        $answer = new TicketResponse();
        $contenu = $data['answer'];
        $answer->setContenu($contenu);
        $id = $data['id'];
        $ticket = $ticketRepository->findOneById($id);
        $user = $ticket->getUser();
        $title = $ticket->getTitle();
        $admin = $this->getUser();
        $answer->setUser($admin);
        $answer->setTicket($ticket);
        $entityManager->persist($answer);
        $entityManager->flush();
        $this->emailTicket->sendEmailModificationStatusTicket($user, $id, $title,
            (new TemplatedEmail())
                ->from(new Address('joeldermont@gmail.com', 'NIIT - Support technique'))
                ->to($user->getEmailAddress())
                ->subject('Réponse à votre ticket')
                ->htmlTemplate('email/answer.html.twig')
        );
        return $this->json(['success' => 'Réponse envoyée']);
    }
    #[Route('/register', name: 'app_register_admin')]
    public function registerAdmin(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('joeldermont@gmail.com', 'NIIT'))
                    ->to($user->getEmailAddress())
                    ->subject('Merci de confirmer votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_send_email');
        }

        return $this->render('registration/register_admin.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}


