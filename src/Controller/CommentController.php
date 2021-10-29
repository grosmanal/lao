<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/comments/{id}', name: 'comment_part', methods: [ 'GET' ] )]
    public function comment(Comment $comment): Response
    {
        // FIXME ce controller n'est pas sécurisé
        // il faut passer par une route dans l'API
        // ou ajouter de la sécurité sur la route
        return $this->render('patient/parts/care_request_comment.html.twig', [
            'comment' => $comment,
            'commentHidden' => true,
        ]);
    }
    
}