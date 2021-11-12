<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Service\Notification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/comments/{id}', name: 'comment', methods: [ 'GET' ] )]
    public function comment(Comment $comment): Response
    {
        $this->denyAccessUnlessGranted('edit', $comment);

        return $this->render('patient/parts/care_request_comment.html.twig', [
            'comment' => $comment,
            'commentHidden' => true,
        ]);
    }
    

    #[Route('/comment_forms/{id}', name: 'comment_form', methods: [ 'GET' ] )]
    public function commentForm(Comment $comment, Notification $notification): Response
    {
        $this->denyAccessUnlessGranted('edit', $comment);

        return $this->render('patient/parts/care_request_comment_form.html.twig', [
            'comment' => $comment,
            'careRequest' => $comment->getCareRequest(),
            'officeDoctors' => $notification->hintMentionData($comment->getOffice()),
        ]);
    }
    
}