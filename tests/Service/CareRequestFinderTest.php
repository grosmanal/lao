<?php

namespace App\Tests\Service;

use App\Entity\Office;
use App\Input\SearchCriteria;
use App\Repository\CareRequestRepository;
use App\Repository\DoctorRepository;
use App\Repository\OfficeRepository;
use App\Service\CareRequestFinder;

class CareRequestFinderTest extends AbstractServiceTest
{
    private CareRequestFinder $careRequestFinder;
    private Office $currentOffice;
    private DoctorRepository $doctorRepository;
    private CareRequestRepository $careRequestRepository;
    
    public function setUp(): void
    {
        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/care_request.yaml',
        ]);

        $container = static::getContainer();
        $this->careRequestFinder = $container->get(CareRequestFinder::class);
        $this->currentOffice = $container->get(OfficeRepository::class)->find(1);
        $this->doctorRepository = $container->get(DoctorRepository::class);
        $this->careRequestRepository = $container->get(CareRequestRepository::class);
    }
    
    private function careRequestIds($careRequestFinderResults)
    {
        return array_values(array_map(function($result) {
            return $result['careRequest']->getId();
        }, $careRequestFinderResults));
    }


    public function dataProviderFindByLabel()
    {
        return [
            [ 'patient_1', [1, 2, 3, 5] ],
            [ 'patient_2', [6] ],
            [ 'patient_3', [] ], // Pas le même office
            [ 'ient_1_contact', [1, 2, 3, 5] ],
        ];
    }
    
    /**
     * @dataProvider dataProviderFindByLabel
     */
    public function testFindByLabel($label, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setLabel($label)
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    public function dataProviderFindByCreator()
    {
        return [
            [ 1, [1, 2, 3, 5, 6] ],
            [ 2, [] ], // Pas le même office
            [ 3, [] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByCreator
     */
    public function testFindByCreator($doctorId, $expected)
    {
        $doctor = $this->doctorRepository->find($doctorId);
        
        $searchCriteria = (new SearchCriteria())
            ->setCreator($doctor)
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    
    public function dataProviderFindByCreationFrom()
    {
        return [
            [ '2021-09-25', [1, 2, 3, 5, 6] ],
            [ '2021-09-26', [1, 2, 6] ],
            [ '2021-09-27', [1, 6] ],
            [ '2021-09-28', [6] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByCreationFrom
     */
    public function testFindByCreationFrom(string $creationFrom, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setCreationFrom(new \DateTime($creationFrom))
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    
    public function dataProviderFindByCreationTo()
    {
        return [
            [ '2021-09-24 23:59:59', [] ],
            [ '2021-09-25 23:59:59', [3, 5] ],
            [ '2021-09-26 23:59:59', [2, 3, 5] ],
            [ '2021-09-27 23:59:59', [1, 2, 3, 5] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByCreationTo
     */
    public function testFindByCreationTo(string $creationTo, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setCreationTo(new \DateTime($creationTo))
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    
    public function dataProviderFindByAvailability()
    {
        return [
            [1, '10:00', '11:00', [1, 2, 3, 5] ],
            [2, '10:00', '11:00', [6] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByAvailability
     */
    public function testFindByAvailability($weekDay, $timeStart, $timeEnd, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setWeekDay($weekDay)
            ->setTimeStart(new \DateTime($timeStart))
            ->setTimeEnd(new \DateTime($timeEnd))
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    
    public function dataProviderFindByVariableSchedule()
    {
        return [
            [ true, [6] ],
            [ false, [] ],
        ];
    }
    
    /**
     * @dataProvider dataProviderFindByVariableSchedule
     */
    public function testFindByVariableSchedule(bool $variableSchedule, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setIncludeVariableSchedules($variableSchedule)
            ->setWeekDay(3) // Horaire pour n'obtenir aucun disponibilité
            ->setTimeStart(new \DateTime('08:00'))
            ->setTimeEnd(new \DateTime('08:01'))
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    

    public function dataProviderFindByActive()
    {
        return [
            [ true, [1, 2, 3, 5, 6] ],
            [ false, [2, 3] ], // La 1, 5, 6 sont actives
        ];
    }
    
    /**
     * @dataProvider dataProviderFindByActive
     */
    public function testFindByActive(bool $active, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setIncludeActiveCareRequest($active)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    

    public function dataProviderFindByArchived()
    {
        return [
            [ true, [1, 2, 3, 5, 6] ],
            [ false, [1, 3, 5, 6] ], // La 2 est archivée
        ];
    }
    
    /**
     * @dataProvider dataProviderFindByArchived
     */
    public function testFindByArchived(bool $archived, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setIncludeArchivedCareRequest($archived)
            ->setIncludeActiveCareRequest(true)
            ->setIncludeAbandonnedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    

    public function dataProviderFindByAbandonned()
    {
        return [
            [ true, [1, 2, 3, 5, 6] ],
            [ false, [1, 2, 5, 6] ], // La 3 est abandonné
        ];
    }
    
    /**
     * @dataProvider dataProviderFindByAbandonned
     */
    public function testFindByAbandonned(bool $abandonned, $expected)
    {
        $searchCriteria = (new SearchCriteria())
            ->setIncludeAbandonnedCareRequest($abandonned)
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
        ;

        $this->assertSame($expected, $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice)));
    }
    
    
    public function dataProviderSortResults()
    {
        return [
            [ [1, 2, 5] ],
            [ [1, 5, 2] ],
            [ [2, 1, 5] ],
            [ [2, 5, 1] ],
            [ [5, 1, 2] ],
            [ [5, 2, 1] ],
        ];
    }

    /**
     * @dataProvider dataProviderSortResults
     */
    public function testSortResults($unsortedCareRequestIds)
    {
        $searchResults = [];
        foreach ($unsortedCareRequestIds as $careRequestId)
        {
            $searchResults[] = [
                'careRequest' => $this->careRequestRepository->find($careRequestId),
            ];
        }

        $this->careRequestFinder->sortSearchResult($searchResults);

        // $searchResults est désormais trié
        $this->assertSame([2, 1, 5], $this->careRequestIds($searchResults));
    }
}