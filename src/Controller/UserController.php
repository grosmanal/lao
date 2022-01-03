<?php

namespace App\Controller;

use App\Entity\Doctor;
use App\Form\UserType;
use App\Repository\OfficeRepository;
use App\Service\InitialAvatarGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractAppController
{
    #[Route('/users', name: 'users')]
    #[IsGranted('ROLE_ADMIN')]
    public function users(OfficeRepository $officeRepository): Response
    {
        $offices = $officeRepository->findAll();

        return $this->render('user/users.html.twig', [
            'offices' => $offices,
        ]);
    }

    /**
     * Modification du détail d'un utilisateur
     */
    #[Route('/users/{id}', name: 'user')]
    public function user(
        Doctor $doctor,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        InitialAvatarGenerator $initialAvatarGenerator,
    ): Response {
        $this->denyAccessUnlessGranted('edit', $doctor);

        $form = $this->createForm(UserType::class, $doctor);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Alimentation de l'avatar si vide
            if (empty($doctor->getAvatarName()) && empty($doctor->getAvatarFile())) {
                $doctor->setAvatarName($initialAvatarGenerator->generate($doctor));
            }

            // Alimentation du mot de passe hashé avec le plain password
            if (!empty($form->get('plainPassword')->getData())) {
                $doctor->setPassword($userPasswordHasher->hashPassword(
                    $doctor,
                    $form->get('plainPassword')->getData()
                ));
            }
            $doctor->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($doctor);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('user.flash.data_updated'));
        }

        return $this->render('user/user.html.twig', [
            'navbarTitle' => new TranslatableMessage('user.content.title', [
                '%displayName%' => $doctor->getDisplayName(),
            ]),
            'doctor' => $doctor,
            'userForm' => $form->createView(),
        ]);
    }
}
