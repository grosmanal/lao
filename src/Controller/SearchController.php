<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Input\SearchCriteria;
use App\Service\CareRequestFinder;
use App\Service\UserProfile;
use Knp\Component\Pager\PaginatorInterface;
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
        PaginatorInterface $paginator,
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

            $careRequestFinder->sortSearchResult($searchResults);

            // Ajout pour chaque résultat :
            // - de la position du résultat dans la liste
            // - l'url de la care request
            $position = 0;
            $searchResults = array_map(function ($searchResult) use (&$position) {
                return array_merge($searchResult, [
                    'position' => ++$position,
                    'url' => $this->generateUrl('patient', [
                        'id' => $searchResult['careRequest']->getPatient()->getId(),
                        'careRequest' => $searchResult['careRequest']->getId(),
                        '_fragment' => sprintf('care-request-heading-%d', $searchResult['careRequest']->getId()),
                    ]),
                ]);
            }, $searchResults);

            $paginatedSearchResults = $paginator->paginate(
                $searchResults,
                $request->query->getInt('page', 1),
            );
        }

        return $this->renderForm('search/search.html.twig', [
            'navbarTitle' => 'search.title',
            'form' => $form,
            'resultCount' => isset($searchResults) ? count($searchResults) : null,
            'paginatedResults' => $paginatedSearchResults ?? null,
        ]);
    }
}
