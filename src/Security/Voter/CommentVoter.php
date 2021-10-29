<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Comment;

class CommentVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    
    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }
        
        if (!($subject instanceof Comment)) {
            return false;
        }
        
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Comment $ressource */
        $ressource = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($ressource, $user);
            case self::EDIT:
                return $this->canEdit($ressource, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }
    
    private function canView($comment, $user)
    {
        // La logique de test des correspondance des office est 
        // déjà faite dans OfficeOwnedVoter

        return true;
    }
    
    private function canEdit($comment, $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        
        // Seul l'auteur d'un commentaire peut le modfier
        if ($user !== $comment->getAuthor()->getUser()) {
            return false;
        }
        
        return true;
    }
}
