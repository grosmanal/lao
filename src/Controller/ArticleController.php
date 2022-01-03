<?php

namespace App\Controller;

use App\Entity\Article;
use App\Service\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/article_mark_read/{id}', name: 'article_mark_read', methods: ['POST'])]
    #[IsGranted('ROLE_DOCTOR')]
    public function markRead(
        Article $article,
        UserProfile $userProfile,
        EntityManagerInterface $em,
        LoggerInterface $logger,
    ): Response {
        $currentDoctor = $userProfile->getDoctor();

        if (!$article->getReadByDoctors()->contains($currentDoctor)) {
            $article->addReadByDoctor($currentDoctor);

            $em->persist($article);
            $em->flush();
        } else {
            $logger->error(sprintf(
                'Article %d marked as read a second time by doctor %d (%s)',
                $article->getId(),
                $currentDoctor->getId(),
                $currentDoctor->getDisplayName(),
            ));
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
