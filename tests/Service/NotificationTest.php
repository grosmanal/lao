<?php

namespace App\Tests\Service;

use App\Exception\DifferentOfficeException;
use App\Repository\OfficeRepository;
use App\Repository\CommentRepository;
use App\Service\Notification;

class NotificationTest extends AbstractServiceTest
{
    private Notification $notification;
    private OfficeRepository $officeRepository;
    private CommentRepository $commentRepository;
    
    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
        
        $this->notification = $container->get(Notification::class);
        $this->officeRepository = $container->get(OfficeRepository::class);
        $this->commentRepository = $container->get(CommentRepository::class);
        
        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/comment.yaml',
            __DIR__ . '/../../fixtures/tests/notification.yaml',
        ]);
    }
    
    
    public function dataProviderHintMentionData()
    {
        $allMentionData = [ 'id' => 0, 'displayName' => 'tou·te·s', ];
        return [
            [
                1, [
                    $allMentionData,
                    [ 'id' => 1, 'displayName' => 'doctor_1_firstname', ],
                    [ 'id' => 3, 'displayName' => 'doctor_3_firstname', ],
                ]
            ],
            [
                2, [
                    $allMentionData,
                    [ 'id' => 2, 'displayName' => 'doctor_2_firstname', ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderHintMentionData
     */
    public function testHintMentionData($officeId, $expected)
    {
        $office = $this->officeRepository->find($officeId);
        $this->assertSame($expected, $this->notification->hintMentionData($office));
    }
    
    public function testCommentWithoutMention()
    {
        $commentId = 1;

        // Le commentaire 1 ne contient pas de mention
        $comment = $this->commentRepository->find($commentId);
        $this->assertEmpty($this->notification->generateNotificationsForComment($comment));
    }

    public function dataProviderNotificationGeneration()
    {
        return [
            [ 7, [ 1 ], ],
            [ 8, [ 1, 3 ], ],
            [ 10, [ 1 ], ], // Deux mentions au doctor 1 => une seule notification
            [ 11, [ 1, 3 ], ], // Mention au doctor 0 (all) => une notification pour 1 et 3
        ];
    }

    /**
     * @dataProvider dataProviderNotificationGeneration
     */
    public function testNotificationGeneration($commentId, $expectedDoctorsId)
    {
        // Instanciation du commentaire à analyser
        $comment = $this->commentRepository->find($commentId);
        
        $notifications = $this->notification->generateNotificationsForComment($comment);
        $this->assertCount(count($expectedDoctorsId), $notifications);
        
        // Extraction des id des doctors des notifications pour comparaison
        $actualDoctorsId = [];
        foreach ($notifications as $notification) {
            $actualDoctorsId[] = $notification->getDoctor()->getId();
        }
        $this->assertSame($expectedDoctorsId, $actualDoctorsId);
    }
    

    /**
     * Si une notification existe déjà pour un doctor,
     * il ne faut pas la recréer
     */
    public function testAlreadyExistingNotification()
    {
        $commentId = 9;

        // Le commentaire 9 à déjà été analysé : la notification 1 existe
        $comment = $this->commentRepository->find($commentId);
        $this->assertEmpty($this->notification->generateNotificationsForComment($comment));
    }
    
    
    /**
     * On ne doit pas pouvoir créer de notification pour un autre cabinet
     */
    public function testOtherOfficeNotification()
    {
        // Instanciation du commentaire à analyser
        $comment = $this->commentRepository->find(12);
        
        $this->expectException(DifferentOfficeException::class);
        $notifications = $this->notification->generateNotificationsForComment($comment);
    }
}
