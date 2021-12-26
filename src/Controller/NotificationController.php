<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use App\Service\UserProfile;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractAppController
{
    #[Route('/notifications', name: 'notifications')]
    #[IsGranted('ROLE_DOCTOR')]
    public function notifications(
        UserProfile $userProfile,
        NotificationRepository $notificationRepository,
    ): Response
    {
        $notifications = $notificationRepository->findUnreadForDoctor($userProfile->getDoctor());

        return $this->render('notification/notifications.html.twig', [
            'markAllReadUrl' => $this->generateUrl('notifications_mark_all'),
            'navbarTitle' => 'notifications.content.unread_notification_header',
            'headerLabel' => 'notifications.content.unread_notification_header',
            'geminiRoute' => 'notifications_read',
            'geminiRouteLabel' => 'notifications.content.read_notifications',
            'pagination' => null,
            'notifications' => $notifications,
        ]);
    }
    

    #[Route('/notifications_read', name: 'notifications_read')]
    #[IsGranted('ROLE_DOCTOR')]
    public function readNotifications(
        Request $request,
        UserProfile $userProfile,
        NotificationRepository $notificationRepository,
        PaginatorInterface $paginator,
    ): Response
    {
        $pagination = $paginator->paginate(
            $notificationRepository->readForDoctorQuery($userProfile->getDoctor()),
            $request->query->getInt('page', 1)
        );

        return $this->render('notification/notifications.html.twig', [
            'navbarTitle' => 'notifications.content.read_notification_header',
            'headerLabel' => 'notifications.content.read_notification_header',
            'geminiRoute' => 'notifications',
            'geminiRouteLabel' => 'notifications.content.notifications',
            'pagination' => $pagination,
            'notifications' => null,
        ]);
    }
    

    #[Route('/notifications_mark_all', name: 'notifications_mark_all')]
    #[IsGranted('ROLE_DOCTOR')]
    public function markAllNotifications(
        UserProfile $userProfile,
        NotificationRepository $notificationRepository
    ): RedirectResponse
    {
        $notificationRepository->markAllForDoctor($userProfile->getDoctor());
        return $this->redirectToRoute('notifications');
    }
}
