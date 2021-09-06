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
            'Ajout où la nouvelle intervalle engloble l’ancienne' => [
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
            'Ajout où l’ancienne intervalle engloble la nouvelle' => [
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
            'Ajout où la nouvelle intervalle engloble l’ancienne et touche la suivante' => [
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
            'Ajout où la nouvelle intervalle engloble l’ancienne et chevauche la suivante' => [
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
        $result = $this->availability->addAvailability($current, $weekDay, $newInterval);
        $this->assertEquals($expected, $result);
    }
}
