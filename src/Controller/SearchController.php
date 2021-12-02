<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Input\SearchCriteria;
use App\Service\CareRequestFinder;
use App\Service\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    #[IsGranted("ROLE_DOCTOR")]
    public function index(
        Request $request,
        Security $security,
        CareRequestFinder $careRequestFinder,
        UserProfile $userProfile,
    ): Response {
        $searchCreteria = new SearchCriteria();
        
        // Valeurs par défaut du formulaire
        $searchCreteria
            ->setIncludeActiveCareRequest(true)
            ->setWeekDay(0)
        ;
        
        $form = $this->createForm(SearchType::class, $searchCreteria, [
            'daysOfWeek' => $this->getParameter('app.availability')['daysOfWeek'],
            'current_doctor' => $userProfile->getDoctor(),
            'method' => 'GET',
        ]);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $security->getUser();
            /** @var App\Entity\Doctor */
            $doctor = $user;
            
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $searchCreteria = $form->getData();
            
            $searchResults = $careRequestFinder->find($searchCreteria, $doctor->getOffice());
            
            // Ajout de l'url de la care request pour chaque résultat
            $searchResults = array_map(function($searchResult) {
                return array_merge($searchResult, [
                    'url' => $this->generateUrl('patient', [
                        'id' => $searchResult['careRequest']->getPatient()->getId(),
                        'careRequest' => $searchResult['careRequest']->getId(),
                        '_fragment' => sprintf('care-request-heading-%d', $searchResult['careRequest']->getId()),
                    ]),
                ]);
            }, $searchResults);

            // Tri par priorité et date de care request
            usort($searchResults, function($a, $b) {
                /** @var \App\Entity\CareRequest */
                $aCR = $a['careRequest'];

                /** @var \App\Entity\CareRequest */
                $bCR = $b['careRequest'];

                if ($aCR->getPriority() == true && $bCR->getPriority() == false) {
                    // a est prioritaire alors que b ne l'est pas : la placer avant b
                    return -1;
                } elseif ($aCR->getPriority() == false && $bCR->getPriority() == true) {
                    // b est prioritaire alors que a ne l'est pas : la placer avant a
                    return 1;
                } else {
                    // a et b sont identique en terme de priorité : on les classe par date de création
                    return $aCR->getCreationDate() <=> $bCR->getCreationDate();
                }
            });
        }

        return $this->renderForm('search/search.html.twig', [
            'form' => $form,
            'searchResults' => $searchResults ?? null,
        ]);
    }
}
