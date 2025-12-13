<?php

namespace App\Security;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailCheckerIsverified implements UserCheckerInterface
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user -> isVerified()) {
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('joel@niit.fr', 'NIIT'))
                    ->to($user->getEmailAddress())
                    ->subject('Merci de confirmer votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            throw new EmailNotVerifiedException('Email not verified');
        }
}

    public function checkPostAuth(UserInterface $user): void
    {
        // TODO: Implement checkPostAuth() method.
    }
}