<?php

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EmailNotVerifiedException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Email non vérifié, un lien de vérification vous a été envoyé par email';
    }

}