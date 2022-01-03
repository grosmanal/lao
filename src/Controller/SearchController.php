<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Input\SearchCriteria;
use App\Service\CareRequestFinder;
use App\Service\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SearchController extends AbstractAppController
{
    #[Route('/search', name: 'search')]
    #[IsGranted("ROLE_DOCTOR")]
    public function index(
        Request $request,
        CareRequestFinder $careRequestFinder,
        UserProfile $userProfile,
    ): Response {
        $searchCreteria = new SearchCriteria();

        $currentDoctor = $userProfile->getDoctor();

        // Valeurs par défaut du formulaire
        $searchCreteria
            ->setIncludeActiveCareRequest(true)
            ->setWeekDay(0)
        ;

        $form = $this->createForm(SearchType::class, $searchCreteria, [
            'daysOfWeek' => $this->getParameter('app.availability')['daysOfWeek'],
            'current_doctor' => $currentDoctor,
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $searchCreteria = $form->getData();

            $searchResults = $careRequestFinder->find($searchCreteria, $currentDoctor->getOffice());

            // Ajout de l'url de la care request pour chaque résultat
            $searchResults = array_map(function ($searchResult) {
                return array_merge($searchResult, [
                    'url' => $this->generateUrl('patient', [
                        'id' => $searchResult['careRequest']->getPatient()->getId(),
                        'careRequest' => $searchResult['careRequest']->getId(),
                        '_fragment' => sprintf('care-request-heading-%d', $searchResult['careRequest']->getId()),
                    ]),
                ]);
            }, $searchResults);

            $careRequestFinder->sortSearchResult($searchResults);
        }

        return $this->renderForm('search/search.html.twig', [
            'navbarTitle' => 'search.title',
            'form' => $form,
            'searchResults' => $searchResults ?? null,
        ]);
    }
}
