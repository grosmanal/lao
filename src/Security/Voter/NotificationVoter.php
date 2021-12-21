<?php

namespace App\Security\Voter;

use App\Entity\Notification;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    
    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::VIEW, self::EDIT])
            && $subject instanceof \App\Entity\Notification;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false; // @codeCoverageIgnore
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                // return true or false
                break;
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        throw new \LogicException('This code should not be reached!'); // @codeCoverageIgnore
    }
    
    private function canView(Notification $notification, UserInterface $user)
    {
        return $this->canEdit($notification, $user);
    }
    
    private function canEdit(Notification $notification, UserInterface $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // On ne peut modifier que les notifications qui sont nous affectés 
        if ($user !== $notification->getDoctor()) {
            return false;
        }

        return true;
    }
}
