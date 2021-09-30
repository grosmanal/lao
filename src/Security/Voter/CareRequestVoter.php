<?php

namespace App\Security\Voter;

use App\Repository\DoctorRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CareRequestVoter extends Voter
{
    public function __construct(private DoctorRepository $doctorRepository, private Security $security)
    {
    }
    
    protected function supports(string $attribute, $subject): bool
    {
        if ($attribute === 'edit' && $subject instanceof \App\Entity\CareRequest) {
            return true;
        }
        return false;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            // Recherche du docteur correspondant au user
            $doctor = $this->doctorRepository->findOneByUser($user);

            // Le doctorCreator doit appartenir au même Office que le user en cours
            if ($subject->getDoctorCreator()->getOffice() == $doctor->getOffice()) {
                return true;
            }
        }

        return false;
    }
}
