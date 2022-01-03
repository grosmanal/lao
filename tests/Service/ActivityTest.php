<?php

namespace App\Tests\Service;

use App\Entity\Office;
use App\Repository\OfficeRepository;
use App\Service\Activity;
use DateTimeImmutable;

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


    public function dataProviderGetActiveEntities()
    {
        return [
            [ '2021-01-04', [
                'bi-file-person' => [], // Patient
                'bi-clipboard' => [], // Care request
                'bi-chat-left-text' => [ // Comment
                    new DateTimeImmutable("2021-05-01 15:10:00"), // Comment ID 4
                    new DateTimeImmutable("2021-05-02 15:10:00"), // Comment ID 5
                ],
            ] ],
            [ '2021-01-03', [
                'bi-file-person' => [ // Patient
                    new DateTimeImmutable("2021-01-03 15:00:01"), // Patient ID 4
                ],
                'bi-clipboard' => [ // Care request
                    new DateTimeImmutable("2021-01-03 15:05:02"), // Care request ID 4
                ],
                'bi-chat-left-text' => [ // Comment
                    new DateTimeImmutable("2021-01-03 15:10:03"), // Comment ID 3
                    new DateTimeImmutable("2021-05-01 15:10:00"), // Comment ID 4
                    new DateTimeImmutable("2021-05-02 15:10:00"), // Comment ID 5
                ],
            ] ],
            [ '2021-01-02', [
                'bi-file-person' => [ // Patient
                    new DateTimeImmutable("2021-01-02 15:00:01"), // Patient ID 3
                    new DateTimeImmutable("2021-01-03 15:00:01"), // Patient ID 4
                ],
                'bi-clipboard' => [ // Care request
                    new DateTimeImmutable("2021-01-02 15:05:02"), // Care request ID 3
                    new DateTimeImmutable("2021-01-03 15:05:02"), // Care request ID 4
                ],
                'bi-chat-left-text' => [ // Comment
                    new DateTimeImmutable("2021-01-02 15:10:03"), // Comment ID 2
                    new DateTimeImmutable("2021-01-03 15:10:03"), // Comment ID 3
                    new DateTimeImmutable("2021-05-01 15:10:00"), // Comment ID 4
                    new DateTimeImmutable("2021-05-02 15:10:00"), // Comment ID 5
                ],
            ] ],
            [ '2021-01-01', [
                'bi-file-person' => [ // Patient
                    new DateTimeImmutable("2021-01-01 15:00:01"), // Patient ID 2
                    new DateTimeImmutable("2021-01-02 15:00:01"), // Patient ID 3
                    new DateTimeImmutable("2021-01-01 15:00:01"), // Patient ID 4
                ],
                'bi-clipboard' => [ // Care request
                    new DateTimeImmutable("2021-01-01 15:05:02"), // Care request ID 2
                    new DateTimeImmutable("2021-01-02 15:05:02"), // Care request ID 3
                    new DateTimeImmutable("2021-01-01 15:05:02"), // Care request ID 4
                ],
                'bi-chat-left-text' => [ // Comment
                    new DateTimeImmutable("2021-01-01 15:10:03"), // Comment ID 1
                    new DateTimeImmutable("2021-01-02 15:10:03"), // Comment ID 2
                    new DateTimeImmutable("2021-01-01 15:10:03"), // Comment ID 3
                    new DateTimeImmutable("2021-05-01 15:10:00"), // Comment ID 4
                    new DateTimeImmutable("2021-05-02 15:10:00"), // Comment ID 5
                ],
            ] ],
            [ null, [
                'bi-file-person' => [ // Patient
                ],
                'bi-clipboard' => [ // Care request
                ],
                'bi-chat-left-text' => [ // Comment
                ],
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
            $since === null ? null : new \DateTimeImmutable($since)
        );

        // Classement des entité par classe
        $entitiesByIcon = [
            'bi-file-person' => [],
            'bi-clipboard' => [],
            'bi-chat-left-text' => [],
        ];
        foreach ($entities as $entity) {
            $entitiesByIcon[$entity['icon']][] = $entity['valorisationDate'];
        }

        foreach ($entitiesByIcon as $icon => $entityIdsOfIcon) {
            array_walk($entityIdsOfIcon, function ($id) use ($expected, $icon) {
                $this->assertContainsEquals($id, $expected[$icon]);
            });
            $this->assertCount(count($expected[$icon]), $entityIdsOfIcon);
        }
    }


    public function dataProviderGetActiveEntitiesSorted()
    {
        return [
            [Activity::SORT_OLDER_FIRST, [
                [ 'bi-file-person', new DateTimeImmutable('2021-01-02 15:00:01') ],     // 3
                [ 'bi-clipboard', new DateTimeImmutable('2021-01-02 15:05:02') ], // 3
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-01-02 15:10:03') ],     // 2
                [ 'bi-file-person', new DateTimeImmutable('2021-01-03 15:00:01') ],     // 4
                [ 'bi-clipboard', new DateTimeImmutable('2021-01-03 15:05:02') ], // 4
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-01-03 15:10:03') ],     // 3
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-05-01 15:10:00') ],     // 4
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-05-02 15:10:00') ],     // 5
            ]],
            [Activity::SORT_NEWER_FIRST, [
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-05-02 15:10:00') ],     // 5
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-05-01 15:10:00') ],     // 4
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-01-03 15:10:03') ],     // 3
                [ 'bi-clipboard', new DateTimeImmutable('2021-01-03 15:05:02') ], // 4
                [ 'bi-file-person', new DateTimeImmutable('2021-01-03 15:00:01') ],     // 4
                [ 'bi-chat-left-text', new DateTimeImmutable('2021-01-02 15:10:03') ],     // 2
                [ 'bi-clipboard', new DateTimeImmutable('2021-01-02 15:05:02') ], // 3
                [ 'bi-file-person', new DateTimeImmutable('2021-01-02 15:00:01') ],     // 3
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

        $entitiesId = array_map(function ($entity) {
            return [$entity['icon'], $entity['valorisationDate']];
        }, $entities);

        $this->assertEquals($expected, $entitiesId);
    }
}
