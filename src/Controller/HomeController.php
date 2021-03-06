<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Service\PatientAnomaly;
use App\Service\Activity;
use App\Service\UserProfile;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractAppController
{
    #[Route('/home', name: 'home')]
    #[IsGranted('ROLE_DOCTOR')]
    public function index(
        Request $request,
        UserProfile $userProfile,
        PatientAnomaly $patientAnomaly,
        ArticleRepository $articleRepository,
        PaginatorInterface $paginator,
        Activity $activity,
    ): Response {
        $currentOffice = $userProfile->getDoctor()->getOffice();

        $articles = $articleRepository->findPublishableNotReadByDoctor($userProfile->getDoctor());

        $daysSince = $request->query->get('daysSince', 7);
        // Calcul du nombre de jours pour le lien «afficher plus de jours»
        if ($daysSince <= 7) {
            $moreDays = 30;
        } elseif ($daysSince <= 30) {
            $moreDays = 60;
        } elseif ($daysSince <= 60) {
            $moreDays = 90;
        } else {
            $moreDays = null;
        }

        $since = (new \DateTime())->sub(new \DateInterval(sprintf('P%dD', $daysSince)));
        $activityEntities = $activity->getActiveEntities($currentOffice, $since);

        $activityPagination = $paginator->paginate(
            $activityEntities,
            $request->query->getInt('page', 1)
        );

        return $this->renderForm('home/home.html.twig', [
            'navbarTitle' => 'home.content.title',
            'patientsAnomaly' => $patientAnomaly->getPatientsAnomaly($currentOffice),
            'articles' => $articles,
            'daysSince' => $daysSince,
            'activityPagination' => $activityPagination,
            'moreDays' => $moreDays,
        ]);
    }
}
