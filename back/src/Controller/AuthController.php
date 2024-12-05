<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{

    #[Route(path: '/api/login', name: 'auth', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $entityManager){
        $user = $this->getUser();

        return $this->json([
            'username'  => $user->getUserIdentifier()
        ]);
    }

}