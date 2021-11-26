<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Doctor;
use App\Entity\CareRequest;
use App\Form\CommentType;
use App\Service\Notification;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CommentFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Notification $notification,
    ) {
    }
    
    public function createNew(Doctor $author, CareRequest $careRequest)
    {
        $newComment = new Comment();
        $newComment
            ->setAuthor($author)
            ->setCareRequest($careRequest)
        ;

        return $this->formFactory->create(CommentType::class, $newComment, [
            'api_action' => 'POST',
            'api_url' => $this->urlGenerator->generate('api_comments_post_collection'),
            'office_doctors' => $this->notification->hintMentionData($author->getOffice()),
        ]);
    }

    public function create(Comment $comment)
    {
        return $this->formFactory->create(CommentType::class, $comment, [
            'api_action' => 'PUT',
            'api_url' => $this->urlGenerator->generate('api_comments_put_item', ['id' => $comment->getId()]),
            'office_doctors' => $this->notification->hintMentionData($comment->getOffice()),
        ]);
    }
}