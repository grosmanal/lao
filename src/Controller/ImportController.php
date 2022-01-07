<?php

namespace App\Controller;

use App\Form\ImportType;
use App\Service\Import\DataImporter;
use App\Service\UserProfile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportController extends AbstractAppController
{
    #[Route('/import', name: 'import')]
    #[IsGranted('ROLE_DOCTOR')]
    public function import(
        Request $request,
        UserProfile $userProfile,
        DataImporter $dataImporter,
    ): Response {
        $form = $this->createForm(ImportType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile */
            $dataFile = $form->get('file')->getData();

            // Test de l'existence du fichier
            if (!file_exists($dataFile->getRealPath())) {
                throw new \Exception(sprintf('Import : fichier %s introuvable', $dataFile->getRealPath()));
            }

            $results = $dataImporter->importFromFile(
                $userProfile->getDoctor(),
                $dataFile->getRealPath()
            );

            foreach ($results['errors'] as $line => $violations) {
                foreach ($violations as $violation) {
                    $this->addFlash('danger', sprintf(
                        "Line %d %s : %s",
                        $line,
                        $violation->getPropertyPath(),
                        $violation->getMessage()
                    ));
                }
            }

            if ($results['patients']) {
                $this->addFlash('success', sprintf('%d patients créés', count($results['patients'])));
            }
        }

        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
