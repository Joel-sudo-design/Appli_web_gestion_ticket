<?php
namespace App\Security;

use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authorizationHeader = $request->headers->get('Authorization');

        if (!$authorizationHeader) {
            $this->logger->warning('Aucun en-tête Authorization trouvé.');
            throw new AuthenticationException('Aucun jeton API fourni');
        }

        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            $this->logger->warning('Format d\'en-tête Authorization invalide: ' . $authorizationHeader);
            throw new AuthenticationException('Format d\'en-tête Authorization invalide');
        }

        $token = substr($authorizationHeader, 7);

        if (empty($token)) {
            $this->logger->warning('Jeton API vide.');
            throw new AuthenticationException('Jeton API vide');
        }

        $this->logger->info('Jeton API reçu: ' . $token);

        $user = $this->userRepository->findOneBy(['apiToken' => $token]);

        if (!$user) {
            $this->logger->warning('Aucun utilisateur trouvé avec le jeton API: ' . $token);
        } else {
            $this->logger->info('Utilisateur trouvé: ' . $user->getUsername());
        }

        return new SelfValidatingPassport(new UserBadge($token, function ($token) {
            return $this->userRepository->findOneBy(['apiToken' => $token]);
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        $data = [
            'error' => 'Authentication failed: ' . $exception->getMessage()
        ];

        return new JsonResponse($data, JsonResponse::HTTP_UNAUTHORIZED);
    }
}

