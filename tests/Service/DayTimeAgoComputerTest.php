<?php

namespace App\Tests\Service;

use App\Service\DayTimeAgoComputer;

class DayTimeAgoComputerTest extends AbstractServiceTest 
{
    private DayTimeAgoComputer $dayTimeAgoComputer;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->dayTimeAgoComputer = $container->get(DayTimeAgoComputer::class);
    }


    public function dataProviderFormatDiff(): array
    {
        return [
            [ '2021-12-08 15:00:00', '2021-12-10 15:00:00', 'il y a 2 jours' ],
            [ '2021-12-09 15:00:00', '2021-12-10 15:00:00', 'il y a 1 jour' ],
            [ '2021-12-09 00:00:00', '2021-12-10 15:00:00', 'il y a 1 jour' ],
            [ '2021-12-09 15:00:00', '2021-12-10 15:00:00', 'il y a 1 jour' ],
            [ '2021-12-09 23:59:59', '2021-12-10 15:00:00', 'il y a 15 heures' ],
            [ '2021-12-10 00:00:00', '2021-12-10 15:00:00', 'aujourd’hui' ],
            [ '2021-12-10 14:00:00', '2021-12-10 15:00:00', 'aujourd’hui' ],
            [ '2021-12-10 15:00:00', '2021-12-10 15:00:00', 'aujourd’hui' ],
        ];
    }

    /**
     * @dataProvider dataProviderFormatDiff
     */
    public function testFormatDiff($from, $to, $exected): void
    {
        $this->assertSame($exected, $this->dayTimeAgoComputer->formatDiff(
            new \DateTimeImmutable($from),
            new \DateTimeImmutable($to),
            'fr'
        ));
    }

}