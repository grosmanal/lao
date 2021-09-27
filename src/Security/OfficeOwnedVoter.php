<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\Patient;
use App\Entity\OfficeOwnedInterface;
use App\Repository\DoctorRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OfficeOwnedVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(private DoctorRepository $doctorRepository, private Security $security)
    {
    }
    
    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }
        
        $reflectionClass = new \ReflectionClass($subject);
        if (!$reflectionClass->implementsInterface(OfficeOwnedInterface::class)) {
           return false;
        }

    return true;

    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var OfficeOwnedInterface $ressource */
        $ressource = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($ressource, $user);
            case self::EDIT:
                return $this->canEdit($ressource, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(OfficeOwnedInterface $ressource, User $user): bool
    {
        return $this->canEdit($ressource, $user);
    }

    private function canEdit(OfficeOwnedInterface $ressource, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            // Recherche de l'Office correspondant au docteur connecté
            $doctor = $this->doctorRepository->findOneByUser($user);

            if ($ressource->getOffice() == $doctor->getOffice()) {
                return true;
            }
        }

        return false;
    }
}