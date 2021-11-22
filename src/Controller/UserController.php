<?php

namespace App\Controller;

use App\Repository\OfficeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(OfficeRepository $officeRepository): Response
    {
        $offices = $officeRepository->findAll();

        return $this->render('user/users.html.twig', [
            'offices' => $offices,
        ]);
    }
}
