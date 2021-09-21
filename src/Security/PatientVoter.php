<?php

namespace App\Security;

use App\Entity\OfficeOwnedInterface;
use App\Entity\Patient;
use App\Repository\OfficeRepository;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class PatientVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    public function __construct(private OfficeRepository $officeRepository, private Security $security)
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
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$this->security->isGranted('ROLE_DOCTOR')) {
            return false;
        }

        // Recherche de l'Office correspondant au docteur connecté
        $office = $this->officeRepository->findOneByUser($token->getUser());

        if ($subject->getOffice() !== $office) {
            return false;
        }

        return true;
    }
}