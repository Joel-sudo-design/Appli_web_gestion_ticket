<?php

namespace App\Email;

use App\Entity\User;
use App\Repository\TicketRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailTicket
{
    private MailerInterface $mailer;
    private string $ticketUrl;

    public function __construct(MailerInterface $mailer, string $ticketUrl)
    {
        $this->mailer = $mailer;
        $this->ticketUrl = $ticketUrl;
    }
    public function sendEmailCreationTicket(User $user, TicketRepository $ticketRepository, TemplatedEmail $email ): void
    {
        $context = $email->getContext();
        $lastTicket = $ticketRepository->findLastByUserId($user->getId());
        $id = $lastTicket->getId();
        $userEmail = $user->getEmailAddress();
        $titleTicket = $lastTicket->getTitle();
        $ticketPriority = $lastTicket->getPriority();
        $url = $this->ticketUrl;
        $context['id'] = $id;
        $context['userEmail'] = $userEmail;
        $context['title'] = $titleTicket;
        $context['Priority'] = $ticketPriority;
        $context['url'] = $url;
        $email->context($context);
        $this->mailer->send($email);
    }
    public function sendEmailModificationStatusTicket(User $user, $id, $title, TemplatedEmail $email ): void
    {
        $context = $email->getContext();
        $url = $this->ticketUrl;
        $context['id'] = $id;
        $context['url'] = $url;
        $context['title'] = $title;
        $email->context($context);
        $this->mailer->send($email);
    }
}
