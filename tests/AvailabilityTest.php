<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\Availability;
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
            'Ajout à une dispo vide' => [
                // current
                [],
                // new
                1, new Interval(1000, 1100),
                // expected
                [
                    1 => [ new Interval(1000, 1100), ],
                ]
            ],
            'Ajout où le jour est vide' => [
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
            'Ajout où le jour est vide avec un jour postérieur' => [
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
            'Ajout où les intervalles ne se touchent pas' => [
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
            'Ajout d’intervalles avant l’existante' => [
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
            'Ajout où les intervalles se touchent' => [
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
            'Ajout où les intervalles se touchent avec plusieurs intervalles' => [
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
            'Ajout où les intervalles se touchent avec plusieurs intervalles' => [
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
            'Ajout où les intervalles se touchent au début ET à la fin' => [
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
            'Ajout où les intervalles se touchent au début ET à la fin + autres intervalles' => [
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
            'Ajout où les intervalles se chevauchent au début' => [
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
            'Ajout où et les intervalles se chevauchent au début ET à la fin' => [
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
            'Ajout où le nouvel intervalle engloble l’ancien' => [
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
            'Ajout où l’ancien intervalle engloble le nouveau' => [
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
            'Ajout où le nouvel intervalle engloble l’ancien et touche le suivant' => [
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
            'Ajout où la nouvel intervalle engloble l’ancien et chevauche le suivant' => [
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
            'Suppression alors qu’aucun intervalle n’existe' => [
                // current
                [ ],
                // old
                1, new Interval(1000, 1100),
                // expected
                [ ]
            ],
            'Suppression alors qu’aucun intervalle n’existe sur ce jour' => [
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
            'Suppression alors que cet intervalle n’existe pas sur ce jour' => [
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
            'Suppression d’un intervalle existant' => [
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
            'Suppression de plusieurs intervalle existants (dont le dernière chevauche)' => [
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
            'Suppression d’un intervalle existant mais pas les autres' => [
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
            'Suppression du début d’un intervalle existant' => [
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
            'Suppression du milieu d’un intervalle existant' => [
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
            'Suppression de la fin d’un intervalle existant' => [
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
            'Suppression d’un intervalle touchant le début d’un intervalle existant' => [
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
            'Suppression d’un intervalle touchant la fin d’un intervalle existant' => [
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
            'Suppression d’un intervalle inexistant touchant un existant' => [
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
            'Un jour, pas de dispo, intervalle à cheval' => [
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
            'Un jour, dispos à cheval' => [
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
}
