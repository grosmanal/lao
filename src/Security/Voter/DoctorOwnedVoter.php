<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Doctor;
use App\Entity\DoctorOwnedInterface;
use App\Repository\DoctorRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DoctorOwnedVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    public function __construct(private DoctorRepository $doctorRepository, private Security $security)
    {
    }
    
    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }
        
        $reflectionClass = new \ReflectionClass($subject);
        if (!$reflectionClass->implementsInterface(DoctorOwnedInterface::class)) {
           return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$this->security->isGranted('ROLE_DOCTOR')) {
            return false;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false; // @codeCoverageIgnore
        }

        $doctor = $this->doctorRepository->find($user->getId());
        if (!$doctor) {
            throw new \LogicException('Should not be here : every entity Doctor must have the ROLE_DOCTOR'); // @codeCoverageIgnore
        }

        /** @var DoctorOwnedInterface $ressource */
        $ressource = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($ressource, $doctor);
            case self::EDIT:
                return $this->canEdit($ressource, $doctor);
        }

        throw new \LogicException('This code should not be reached!'); // @codeCoverageIgnore
    }

    private function canView(DoctorOwnedInterface $ressource, Doctor $doctor): bool
    {
        // Les docteurs du même office peuvent voir
        if ($ressource->ownedByDoctor()->getOffice() == $doctor->getOffice()) {
            return true;
        }
        
        return false;
    }

    private function canEdit(DoctorOwnedInterface $ressource, Doctor $doctor): bool
    {
        // Seule le docteur possédant l'objet peut le modifier
        if ($ressource->ownedByDoctor() === $doctor) {
            return true;
        }

        return false;
    }
}