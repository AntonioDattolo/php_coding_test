<?php
namespace App\Security;

use App\Repository\UserRepository; 
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
// use Symfony\Component\Security\Core\Exception\UserNotFoundException; // predefinita di symfony
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Exception\UserNotFoundException;

class LoginAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UserRepository $userRepository; // Dichiarazione della proprietà

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository // Inietta il repository
    ) {
        $this->userRepository = $userRepository; // Assegna il repository alla proprietà
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email'); // Recupera l'email dal form
        $password = $request->request->get('password'); // Recupera la password dal form

        
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);
       
        $user = $this->userRepository->findOneBy(['email' => $email]);
            
        if (!$user) {
            throw new UserNotFoundException('User not found.');
        }

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Reindirizza alla home dopo il login
        return new RedirectResponse($this->urlGenerator->generate('car_index'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Personalizza il messaggio di errore in base al tipo di eccezione
        if ($exception instanceof UserNotFoundException) {
            $errorMessage = 'User not found.';
        } elseif ($exception instanceof BadCredentialsException) {
            $errorMessage = 'Password errata. Riprova.';
        } else {
            $errorMessage = 'Errore di autenticazione. Riprova.';
        }

        // Aggiungi il messaggio di errore come flash message
        $session = $request->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add('error', $errorMessage);
        }

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}