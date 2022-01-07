<?php

namespace App\Controller;

use App\Entity\User;
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
        User $user,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        InitialAvatarGenerator $initialAvatarGenerator,
    ): Response {
        $this->denyAccessUnlessGranted('edit', $user);

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Alimentation de l'avatar si vide
            if (empty($user->getAvatarName()) && empty($user->getAvatarFile())) {
                $user->setAvatarName($initialAvatarGenerator->generate($user));
            }

            // Alimentation du mot de passe hashé avec le plain password
            if (!empty($form->get('plainPassword')->getData())) {
                $user->setPassword($userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                ));
            }
            $user->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', $translator->trans('user.flash.data_updated'));
        }

        return $this->render('user/user.html.twig', [
            'navbarTitle' => new TranslatableMessage('user.content.title', [
                '%displayName%' => $user->getDisplayName(),
            ]),
            'user' => $user,
            'userForm' => $form->createView(),
        ]);
    }
}
