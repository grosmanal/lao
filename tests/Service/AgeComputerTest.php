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
            ['P1M', '1 mois'],
            ['P2M', '2 mois'],
            ['P6M', '6 mois'],
            ['P11M', '11 mois'],
            ['P11M30D', '11 mois'],
            ['P1Y', '1 an'],
            ['P1Y3M', '1 an'],
            ['P1Y6M', '1 an et demi'],
            ['P1Y7M', '1 an et demi'],
            ['P1Y11M', '1 an et demi'],
            ['P1Y11M30D', '1 an et demi'],
            ['P2Y', '2 ans'],
            ['P2Y6M', '2 ans et demi'],
            ['P10Y', '10 ans'],
            ['P10Y6M', '10 ans et demi'],
            ['P11Y', '11 ans'],
            ['P11Y6M', '11 ans'],
        ];
    }

    /**
     * @dataProvider dataProviderAgeAsString
     */
    public function testAgeAsString(string $intervalAge, string $expected)
    {
        $birthDate = (new DateTime())->sub(new DateInterval($intervalAge));
        $this->assertSame($expected, $this->ageComputer->getAgeAsString($birthDate));
    }
}
