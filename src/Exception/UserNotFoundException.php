<?php
namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserNotFoundException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Utente non trovato. Verifica l\'email.';
    }
}