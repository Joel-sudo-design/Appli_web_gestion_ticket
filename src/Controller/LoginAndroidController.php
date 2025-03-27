<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class LoginAndroidController extends AbstractController
{
    #[Route('/login_api', name: 'api_login', methods: ['POST'])]
    public function apilogin(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        try {
            // Récupération des données de la requête
            $data = json_decode($request->getContent(), true);
            $username = $data['username'] ?? '';
            $password = $data['password'] ?? '';

            // Vérification des champs
            if (empty($username) || empty($password)) {
                return new JsonResponse(['error' => 'Email et mot de passe sont requis.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Recherche de l'utilisateur par email
            $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

            if (!$user) {
                return new JsonResponse(['error' => 'Email invalide.'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Vérification du mot de passe
            if (!password_verify($password, $user->getPassword())) {
                return new JsonResponse(['error' => 'Mot de passe invalide.'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            // Génération d'un nouveau token unique
            do {
                $apiToken = bin2hex(random_bytes(32));
                $existingUser = $entityManager->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
            } while ($existingUser !== null);

            // Attribution du token à l'utilisateur
            $user->setApiToken($apiToken);
            $entityManager->flush();

            // Retourner le token dans la réponse
            return new JsonResponse(['token' => $apiToken], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            // Log de l'erreur pour le débogage
            $logger->error('Erreur lors de la connexion API : ' . $e->getMessage());

            // Retourner une réponse d'erreur générique
            return new JsonResponse(['error' => 'Erreur serveur. Veuillez réessayer plus tard.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
