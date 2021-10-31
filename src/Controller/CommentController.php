<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
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
    
}