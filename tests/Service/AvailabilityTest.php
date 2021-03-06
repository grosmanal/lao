<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\Availability;
use DateTime;
use DateTimeImmutable;
use Interval\Intervals;
use Interval\Interval;

class AvailabilityTest extends TestCase
{
    private Availability $availability;

    protected function setUp(): void
    {
        $this->availability = new Availability();
    }

    public function dataProviderIntervalsToRaw()
    {
        return [
            [
                // intervaled availabilities
                [
                    1 => [
                        new Interval(900, 930),
                        new Interval(1000, 1200),
                    ],
                    3 => [
                        new Interval(1000, 1200),
                    ],
                ],
                // expected
                [
                    1 => [
                        [900, 930],
                        [1000, 1200],
                    ],
                    3 => [
                        [1000, 1200],
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider dataProviderIntervalsToRaw
     */
    public function testIntervalsToRaw($intervaledAvailabilities, $expected)
    {
        $this->assertEquals($expected, $this->availability->intervalsToRaw($intervaledAvailabilities));
    }

    public function dataProviderRawToIntervals()
    {
        return [
            [
                // raw availabilities
                [
                    1 => [
                        [900, 930],
                        [1000, 1200],
                    ],
                    3 => [
                        [1000, 1200]
                    ]
                    ],
                // Expected
                [
                    1 => [
                        new Interval(900, 930),
                        new Interval(1000, 1200),
                    ],
                    3 => [
                        new Interval(1000, 1200),
                    ],
                ],
            ]
        ];
    }

    /**
     * @dataProvider dataProviderRawToIntervals
     */
    public function testRawToIntervals($rawAvailabilities, $expected)
    {
        $this->assertEquals($expected, $this->availability->rawToIntervals($rawAvailabilities));
    }

    public function dataProviderAddAvailability()
    {
        return [
            'Ajout ?? une dispo vide' => [
                // current
                [],
                // new
                1, new Interval(1000, 1100),
                // expected
                [
                    1 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Ajout o?? le jour est vide' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                ],
                // new
                2, new Interval(1000, 1100),
                // expected
                [
                    1 => [ new Interval(1000, 1100), ],
                    2 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Ajout o?? le jour est vide avec un jour post??rieur' => [
                // current
                [
                    2 => [ new Interval(1000, 1100), ],
                ],
                // new
                1, new Interval(900, 1000),
                // expected
                [
                    1 => [ new Interval(900, 1000), ],
                    2 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Ajout o?? les intervalles ne se touchent pas' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                ],
                // new
                1, new Interval(1500, 1600),
                // expected
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1500, 1600),
                    ],
                ]
            ],
            'Ajout d???intervalles avant l???existante' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                ],
                // new
                1, new Interval(900, 930),
                // expected
                [
                    1 => [
                        new Interval(900, 930),
                        new Interval(1000, 1100),
                    ],
                ]
            ],
            'Ajout o?? les intervalles se touchent' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                ],
                // new
                1, new Interval(1100, 1200),
                // expected
                [
                    1 => [ new Interval(1000, 1200), ],
                ]
            ],
            'Ajout o?? les intervalles se touchent avec plusieurs intervalles (1er interval)' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1500, 1600),
                    ],
                ],
                // new
                1, new Interval(1100, 1200),
                // expected
                [
                    1 => [
                        new Interval(1000, 1200),
                        new Interval(1500, 1600),
                    ],
                ]
            ],
            'Ajout o?? les intervalles se touchent avec plusieurs intervalles (2nd interval)' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1500, 1600),
                    ],
                ],
                // new
                1, new Interval(1600, 1700),
                // expected
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1500, 1700),
                    ],
                ]
            ],
            'Ajout o?? les intervalles se touchent au d??but ET ?? la fin' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1300, 1400),
                    ],
                ],
                // new
                1, new Interval(1100, 1300),
                // expected
                [
                    1 => [
                        new Interval(1000, 1400),
                    ],
                ]
            ],
            'Ajout o?? les intervalles se touchent au d??but ET ?? la fin + autres intervalles' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1300, 1400),
                        new Interval(1600, 1800),
                    ],
                ],
                // new
                1, new Interval(1100, 1300),
                // expected
                [
                    1 => [
                        new Interval(1000, 1400),
                        new Interval(1600, 1800),
                    ],
                ]
            ],
            'Ajout o?? les intervalles se chevauchent au d??but' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                    ],
                ],
                // new
                1, new Interval(1030, 1300),
                // expected
                [
                    1 => [
                        new Interval(1000, 1300),
                    ],
                ]
            ],
            'Ajout o?? et les intervalles se chevauchent au d??but ET ?? la fin' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1300, 1400),
                    ],
                ],
                // new
                1, new Interval(1030, 1330),
                // expected
                [
                    1 => [
                        new Interval(1000, 1400),
                    ],
                ]
            ],
            'Ajout o?? le nouvel intervalle engloble l???ancien' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                    ],
                ],
                // new
                1, new Interval(900, 1130),
                // expected
                [
                    1 => [
                        new Interval(900, 1130),
                    ],
                ]
            ],
            'Ajout o?? l???ancien intervalle engloble le nouveau' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1200),
                    ],
                ],
                // new
                1, new Interval(1030, 1130),
                // expected
                [
                    1 => [
                        new Interval(1000, 1200),
                    ],
                ]
            ],
            'Ajout o?? le nouvel intervalle engloble l???ancien et touche le suivant' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1130, 1200),
                    ],
                ],
                // new
                1, new Interval(900, 1130),
                // expected
                [
                    1 => [
                        new Interval(900, 1200),
                    ],
                ]
            ],
            'Ajout o?? la nouvel intervalle engloble l???ancien et chevauche le suivant' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1130, 1200),
                    ],
                ],
                // new
                1, new Interval(900, 1140),
                // expected
                [
                    1 => [
                        new Interval(900, 1200),
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderAddAvailability
     */
    public function testAddAvailability($current, $weekDay, $newInterval, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->availability->addAvailability($current, $weekDay, $newInterval)
        );
    }

    /**
     * @return array
     */
    public function dataProviderRemoveAvailability()
    {
        return [
            'Suppression alors qu???aucun intervalle n???existe' => [
                // current
                [ ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [ ]
            ],
            'Suppression alors qu???aucun intervalle n???existe sur ce jour' => [
                // current
                [
                    2 => [ new Interval(1000, 1100), ],
                ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [
                    2 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Suppression alors que cet intervalle n???existe pas sur ce jour' => [
                // current
                [
                    1 => [ new Interval(800, 900), ],
                    2 => [ new Interval(1000, 1100), ],
                ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [
                    1 => [ new Interval(800, 900), ],
                    2 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Suppression d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [ ]
            ],
            'Suppression de plusieurs intervalle existants' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1130, 1200),
                    ],
                ],
                // old
                1, new Interval(1000, 1200),
                // expected
                [ ]
            ],
            'Suppression de plusieurs intervalle existants (dont le derni??re chevauche)' => [
                // current
                [
                    1 => [
                        new Interval(1000, 1100),
                        new Interval(1130, 1200),
                    ],
                ],
                // old
                1, new Interval(1000, 1145),
                // expected
                [
                    1 => [ new Interval(1145, 1200), ]
                ]
            ],
            'Suppression d???un intervalle existant mais pas les autres' => [
                // current
                [
                    1 => [ new Interval(1000, 1100), ],
                    2 => [ new Interval(1000, 1100), ],
                ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [
                    2 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Suppression du d??but d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(930, 1100),
                // expected
                [
                    1 => [
                        new Interval(1100, 1200),
                    ],
                ]
            ],
            'Suppression du milieu d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(1030, 1100),
                // expected
                [
                    1 => [
                        new Interval(1000, 1030),
                        new Interval(1100, 1200),
                    ],
                ]
            ],
            'Suppression de la fin d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(1130, 1230),
                // expected
                [
                    1 => [
                        new Interval(1000, 1130),
                    ],
                ]
            ],
            'Suppression d???un intervalle touchant le d??but d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(900, 1000),
                // expected
                [
                    1 => [
                        new Interval(1000, 1200),
                    ],
                ]
            ],
            'Suppression d???un intervalle touchant la fin d???un intervalle existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(1200, 1300),
                // expected
                [
                    1 => [
                        new Interval(1000, 1200),
                    ],
                ]
            ],
            'Suppression d???un intervalle inexistant touchant un existant' => [
                // current
                [
                    1 => [ new Interval(1000, 1200), ],
                ],
                // old
                1, new Interval(900, 1000),
                // expected
                [
                    1 => [
                        new Interval(1000, 1200),
                    ],
                ]
            ],
        ];
    }


    /**
     * @dataProvider dataProviderRemoveAvailability
     */
    public function testRemoveAvailability($current, $weekDay, $oldInterval, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->availability->removeAvailability($current, $weekDay, $oldInterval)
        );
    }

    public function dataProviderWeekAvailability()
    {
        return [
            'Un jour, pas de dispo' => [
                [1], '0900', '1300', 'PT1H', [],
                // expected
                [
                    1 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                ]
            ],
            'Un jour, pas de dispo intervalle demi-heure' => [
                [1], '0900', '1300', 'PT30M', [],
                // expected
                [
                    1 => [
                        '0900-0930' => false,
                        '0930-1000' => false,
                        '1000-1030' => false,
                        '1030-1100' => false,
                        '1100-1130' => false,
                        '1130-1200' => false,
                        '1200-1230' => false,
                        '1230-1300' => false,
                    ]
                ]
            ],
            'Un jour, pas de dispo, intervalle ?? cheval' => [
                [1], '0900', '1315', 'PT1H', [],
                // expected
                [
                    1 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                ]
            ],
            'Plusieurs jours, pas de dispo' => [
                [1, 2, 3, 4], '0900', '1300', 'PT1H', [],
                // expected
                [
                    1 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    2 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    3 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    4 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                ]
            ],
            'Plusieurs jours, dispos un jour' => [
                [1, 2, 3, 4], '0900', '1300', 'PT1H', [
                    2 => [ [900, 1100], [1200, 1300] ],
                ],
                // expected
                [
                    1 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    2 => [ '0900-1000' => true, '1000-1100' => true, '1100-1200' => false, '1200-1300' => true ],
                    3 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    4 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                ]
            ],
            'Plusieurs jours, dispos plusieurs jours' => [
                [1, 2, 3, 4], '0900', '1300', 'PT1H', [
                    2 => [ [900, 1100], [1200, 1300] ],
                    4 => [ [900, 1000], [1200, 1300] ],
                ],
                // expected
                [
                    1 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    2 => [ '0900-1000' => true, '1000-1100' => true, '1100-1200' => false, '1200-1300' => true ],
                    3 => [ '0900-1000' => false, '1000-1100' => false, '1100-1200' => false, '1200-1300' => false ],
                    4 => [ '0900-1000' => true, '1000-1100' => false, '1100-1200' => false, '1200-1300' => true ],
                ]
            ],
            'Un jour, dispos ?? cheval' => [
                [1], '0900', '1300', 'PT1H', [
                    1 => [ [930, 1030] ]
                ],
                // expected
                [
                    1 => [ '0900-1000' => true, '1000-1100' => true, '1100-1200' => false, '1200-1300' => false ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWeekAvailability
     */
    public function testWeekAvailability($daysOfWeek, $startTime, $endTime, $interval, $availability, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->availability->weekAvailabilities(
                $daysOfWeek,
                $startTime,
                $endTime,
                $interval,
                $availability
            )
        );
    }


    private function intToDateTimeImmutable(int $time): \DateTimeImmutable
    {
        $hour = (int) floor($time / 100);
        $minute = $time - ($hour * 100);

        return new DateTimeImmutable(sprintf('%02d:%02d', $hour, $minute));
    }


    public function dataProviderCoverScore()
    {
        return [
            [ 2, 900, 930, Availability::NO_MATCH, null ],
            [ 1, 1300, 1400, Availability::NO_MATCH, null ],
            [ 1, 800, 900, Availability::NO_MATCH, null ],
            [ 1, 930, 945, Availability::NO_MATCH, null ],
            [ 1, 830, 930, Availability::PARTIAL_COVER, [new Interval(900, 930)] ],
            [ 1, 900, 1000, Availability::PARTIAL_COVER, [new Interval(900, 930)] ],
            [ 1, 945, 1015, Availability::PARTIAL_COVER, [new Interval(1000, 1100)] ],
            [ 1, 1030, 1130, Availability::PARTIAL_COVER , [new Interval(1000, 1100)] ],
            [ 1, 900, 930, Availability::BOTH_EXACT_EDGE, [new Interval(900, 930)] ],
            [ 1, 1000, 1030, Availability::ONE_EXACT_EDGE, [new Interval(1000, 1100)] ],
            [ 1, 1030, 1100, Availability::ONE_EXACT_EDGE, [new Interval(1000, 1100)] ],
            [ 3, 1500, 1600, Availability::FULLY_COVERED, [new Interval(1300, 1800)] ],
            [ 1, 915, 1015, Availability::PARTIAL_COVER, [new Interval(900, 930), new Interval(1000, 1100)] ],
        ];
    }

    /**
     * @dataProvider dataProviderCoverScore
     */
    public function testCoverScore($weekDay, $startTime, $endTime, $expectedScore, $expectedMatches)
    {
        $rawAvailabilities = [
            1 => [
                [ 900, 930 ],
                [ 1000, 1100 ],
            ],
            3 => [
                [ 1300, 1800 ],
            ],
        ];

        $computedScore = $this->availability->computeCoverScore(
            $rawAvailabilities,
            $weekDay,
            $this->intToDateTimeImmutable($startTime),
            $this->intToDateTimeImmutable($endTime),
        );
        $this->assertEquals($expectedScore, $computedScore['score']);
        if ($expectedScore !== Availability::NO_MATCH) {
            $this->assertEquals($expectedMatches, $computedScore['matches']);
        }
    }
}
