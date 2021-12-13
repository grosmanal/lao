<?php

namespace App\Tests\Service;

use App\Entity\Office;
use App\Entity\Patient;
use App\Entity\CareRequest;
use App\Entity\Comment;
use App\Repository\OfficeRepository;
use App\Service\Activity;

class ActivityTest extends AbstractServiceTest
{
    private Activity $activty;
    private Office $currentOffice;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->activty = $container->get(Activity::class);
        
        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/activityService/patient.yaml',
            __DIR__ . '/../../fixtures/tests/activityService/care_request.yaml',
            __DIR__ . '/../../fixtures/tests/activityService/comment.yaml',
        ]);

        // Recherche de l'office 1 pour l'affecté en tant qu'office courant
        $this->currentOffice = $container->get(OfficeRepository::class)->find(1);
    }
    
    
    function dataProviderGetActiveEntities()
    {
        return [
            [ '2021-01-04', [
                Patient::class => [],
                CareRequest::class => [],
                Comment::class => [4, 5],
            ] ],
            [ '2021-01-03', [
                Patient::class => [4],
                CareRequest::class => [4],
                Comment::class => [3, 4, 5],
            ] ],
            [ '2021-01-02', [
                Patient::class => [3, 4],
                CareRequest::class => [3, 4],
                Comment::class => [2, 3, 4, 5],
            ] ],
            [ '2021-01-01', [
                Patient::class => [2, 3, 4],
                CareRequest::class => [2, 3, 4],
                Comment::class => [1, 2, 3, 4, 5],
            ] ],
        ];
    }

    /**
     * @dataProvider dataProviderGetActiveEntities
     */
    public function testGetActiveEntities($since, $expected)
    {
        $entities = $this->activty->getActiveEntities(
            $this->currentOffice,
            new \DateTimeImmutable($since)
        );
        
        // Classement des entité par classe
        $entitiesByClass = [
            Patient::class => [],
            CareRequest::class => [],
            Comment::class => [],
        ];
        foreach($entities as $entity) {
            $entitiesByClass[get_class($entity)][] = $entity->getId();
        }
        
        foreach ($entitiesByClass as $class => $entityIdsOfClass) {
            array_walk($entityIdsOfClass, function($id) use ($expected, $class) {
                $this->assertContainsEquals($id, $expected[$class]);
            });
            $this->assertCount(count($expected[$class]), $entityIdsOfClass);
        }
        
        //$this->assertSame($expected, $entitiesByClass);
    }
    
    
    public function dataProviderGetActiveEntitiesSorted()
    {
        return [
            [Activity::SORT_OLDER_FIRST, [
                [ Patient::class, 3 ],     // 2021-01-02 15:00:01
                [ CareRequest::class, 3 ], // 2021-01-02 15:05:02
                [ Comment::class, 2 ],     // 2021-01-02 15:10:03
                [ Patient::class, 4 ],     // 2021-01-03 15:00:01
                [ CareRequest::class, 4 ], // 2021-01-03 15:05:02
                [ Comment::class, 3 ],     // 2021-01-03 15:10:03
                [ Comment::class, 4 ],     // 2021-05-01 15:10:00
                [ Comment::class, 5 ],     // 2021-05-02 15:10:00
            ]],
            [Activity::SORT_NEWER_FIRST, [
                [ Comment::class, 5 ],     // 2021-05-02 15:10:00
                [ Comment::class, 4 ],     // 2021-05-01 15:10:00
                [ Comment::class, 3 ],     // 2021-01-03 15:10:03
                [ CareRequest::class, 4 ], // 2021-01-03 15:05:02
                [ Patient::class, 4 ],     // 2021-01-03 15:00:01
                [ Comment::class, 2 ],     // 2021-01-02 15:10:03
                [ CareRequest::class, 3 ], // 2021-01-02 15:05:02
                [ Patient::class, 3 ],     // 2021-01-02 15:00:01
            ]],
        ];
    }
    
    /**
     * @dataProvider dataProviderGetActiveEntitiesSorted
     */
    public function testGetActiveEntitiesSorted($sort, $expected)
    {
        $entities = $this->activty->getActiveEntities(
            $this->currentOffice,
            new \DateTimeImmutable('2021-01-02'),
            $sort
        );
        
        $entitiesId = array_map(function($entity) {
            return [get_class($entity), $entity->getId()];
        }, $entities);
        
        $this->assertSame($expected, $entitiesId);
    }
}
