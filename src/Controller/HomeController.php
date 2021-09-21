<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(Request $request, LoggerInterface $logger): Response
    {
        $availability = [
            1 => [
                '0800-0830' => false,
                '0830-0900' => true,
                '0900-0930' => true,
                '0930-1000' => false,
            ],
            2 => [
                '0800-0830' => true,
                '0830-0900' => true,
                '0900-0930' => false,
                '0930-1000' => false,
            ],
            3 => [
                '0800-0830' => false,
                '0830-0900' => false,
                '0900-0930' => true,
                '0930-1000' => false,
            ],
            4 => [
                '0800-0830' => false,
                '0830-0900' => true,
                '0900-0930' => false,
                '0930-1000' => true,
            ],
            5 => [
                '0800-0830' => false,
                '0830-0900' => false,
                '0900-0930' => false,
                '0930-1000' => true,
            ],
            6 => [
                '0800-0830' => false,
                '0830-0900' => false,
                '0900-0930' => true,
                '0930-1000' => true,
            ],
        ];
        return $this->render('home/index.html.twig', [
            'availability' => $availability,
            'controller_name' => 'HomeController',
        ]);
    }
}
