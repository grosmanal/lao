<?php

namespace App\Controller;

use App\Service\UserProfile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractAppController
{
    #[Route('/notifications', name: 'notifications')]
    public function index(): Response
    {
        // Les notifications non lues sont déjà lue dans AbstractAppController
        // et injectés dans la vue twig

        return $this->render('notification/notifications.html.twig', [
        ]);
    }
}
