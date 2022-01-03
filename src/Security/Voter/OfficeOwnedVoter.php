<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\OfficeOwnedInterface;
use App\Repository\DoctorRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OfficeOwnedVoter extends Voter
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
            return false; // @codeCoverageIgnore
        }

        /** @var OfficeOwnedInterface $ressource */
        $ressource = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($ressource, $user);
            case self::EDIT:
                return $this->canEdit($ressource, $user);
        }

        throw new \LogicException('This code should not be reached!'); // @codeCoverageIgnore
    }

    private function canView(OfficeOwnedInterface $ressource, User $user): bool
    {
        return $this->canEdit($ressource, $user);
    }

    private function canEdit(OfficeOwnedInterface $ressource, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($this->security->isGranted('ROLE_DOCTOR')) {
            // Recherche de l'Office correspondant au docteur connecté
            $doctor = $this->doctorRepository->find($user->getId());

            if (!$doctor) {
                throw new \LogicException('Should not be here'); // @codeCoverageIgnore
            }

            if ($ressource->ownedByOffice() === $doctor->getOffice()) {
                return true;
            }
        }

        return false;
    }
}
