<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(Request $request, LoggerInterface $logger): RedirectResponse
    {
        return $this->redirectToRoute('patient', [ 'id' => 1 ]);
    }
}
