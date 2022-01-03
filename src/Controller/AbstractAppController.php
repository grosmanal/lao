<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Service\UserProfile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AbstractAppController extends AbstractController
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private UserProfile $userProfile,
    ) {
    }


    /**
     * @inheritdoc
     */
    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        // Ajout des paramètres nécessaire au template de base
        $baseViewParameters = [
            'unreadNotifications' =>
                $this->userProfile->currentUserIsDoctor() ?
                    $this->notificationRepository->findUnreadForDoctor($this->userProfile->getDoctor()) :
                    null,
        ];

        return parent::render($view, array_merge($baseViewParameters, $parameters), $response);
    }
}
