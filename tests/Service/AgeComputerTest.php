<?php

namespace App\Tests\Service;

use DateTimeImmutable;
use App\Service\AgeComputer;
use DateInterval;
use DateTime;

class AgeComputerTest extends AbstractServiceTest
{
    private AgeComputer $ageComputer;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->ageComputer = $container->get(AgeComputer::class);
    }


    public function dataProviderAgeAsString()
    {
        return [
            ['2021-04-01', '1 mois'],
            ['2021-03-01', '2 mois'],
            ['2020-11-01', '6 mois'],
            ['2020-06-01', '11 mois'],
            ['2020-05-01', '1 an'],
            ['2020-02-01', '1 an'],
            ['2019-11-01', '1 an et demi'],
            ['2019-11-01', '1 an et demi'],
            ['2019-06-01', '1 an et demi'],
            ['2019-06-30', '1 an et demi'],
            ['2019-05-01', '2 ans'],
            ['2018-11-01', '2 ans et demi'],
            ['2011-05-01', '10 ans'],
            ['2010-11-01', '10 ans et demi'],
            ['2010-05-01', '11 ans'],
            ['2009-11-01', '11 ans'],
        ];
    }

    /**
     * @dataProvider dataProviderAgeAsString
     */
    public function testAgeAsString(string $birthDateAsString, string $expected)
    {
        $fromDate = new \DateTimeImmutable('2021-05-01 00:00:00');
        $birthDate = new \DateTimeImmutable($birthDateAsString);
        $this->assertSame($expected, $this->ageComputer->getAgeAsString($birthDate, $fromDate));
    }
}
