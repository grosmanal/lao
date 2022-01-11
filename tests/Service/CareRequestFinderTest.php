<?php

namespace App\Tests\Service;

use App\Entity\Office;
use App\Input\SearchCriteria;
use App\Repository\CareRequestRepository;
use App\Repository\ComplaintRepository;
use App\Repository\DoctorRepository;
use App\Repository\OfficeRepository;
use App\Service\CareRequestFinder;

class CareRequestFinderTest extends AbstractServiceTest
{
    private CareRequestFinder $careRequestFinder;
    private Office $currentOffice;
    private DoctorRepository $doctorRepository;
    private ComplaintRepository $complaintRepository;
    private CareRequestRepository $careRequestRepository;

    public function setUp(): void
    {
        $this->setUpTestService([
            __DIR__ . '/../../fixtures/tests/careRequestFinderService/care_request.yaml',
        ]);

        $container = static::getContainer();
        $this->careRequestFinder = $container->get(CareRequestFinder::class);
        $this->currentOffice = $container->get(OfficeRepository::class)->find(1);
        $this->doctorRepository = $container->get(DoctorRepository::class);
        $this->complaintRepository = $container->get(ComplaintRepository::class);
        $this->careRequestRepository = $container->get(CareRequestRepository::class);
    }

    private function careRequestIds($careRequestFinderResults, $sortResults = true)
    {
        $results = array_values(array_map(function ($result) {
            return $result['careRequest']->getId();
        }, $careRequestFinderResults));

        if ($sortResults) {
            sort($results);
        }

        return $results;
    }

    private function createSearchCriteria(): SearchCriteria
    {
        return (new SearchCriteria())
            ->setIncludeActiveCareRequest(true)
            ->setIncludeArchivedCareRequest(true)
            ->setIncludeAbandonedCareRequest(true)
        ;
    }

    public function dataProviderFindByLabel()
    {
        return [
            [ 'patient_1', [1, 2, 3, 5, 7] ],
            [ 'patient_2', [6] ],
            [ 'patient_3', [] ], // Pas le même office
            [ 'ient_1_contact', [1, 2, 3, 5, 7] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByLabel
     */
    public function testFindByLabel($label, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setLabel($label)
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByRequestedDoctor()
    {
        return [
            [ null, [1, 2, 3, 5, 6, 7] ],
            [ 1, [1, 2], ],
            [ 2, [], ],// Pas le même office
            [ 3, [3] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByRequestedDoctor
     */
    public function testFindByRequestedDoctor($doctorId, $expected)
    {
        $searchCriteria = $this->createSearchCriteria();

        if ($doctorId) {
            $doctor = $this->doctorRepository->find($doctorId);
            $searchCriteria->setRequestedDoctor($doctor);
        }

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }

    public function dataProviderFindByContactedBy()
    {
        return [
            [ null, [1, 2, 3, 5, 6, 7] ],
            [ 1, [1, 2, 3, 5, 6, 7] ],
            [ 2, [] ], // Pas le même office
            [ 3, [] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByContactedBy
     */
    public function testFindByContactedBy($doctorId, $expected)
    {
        $searchCriteria = $this->createSearchCriteria();

        if ($doctorId) {
            $doctor = $this->doctorRepository->find($doctorId);
            $searchCriteria->setContactedBy($doctor);
        }

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByCreationFrom()
    {
        return [
            [ '2021-09-25', [1, 2, 3, 5, 6, 7] ],
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
        $searchCriteria = ($this->createSearchCriteria())
            ->setContactedFrom(new \DateTime($creationFrom))
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByCreationTo()
    {
        return [
            [ '2021-09-24 23:59:59', [] ],
            [ '2021-09-25 23:59:59', [3, 5, 7] ],
            [ '2021-09-26 23:59:59', [2, 3, 5, 7] ],
            [ '2021-09-27 23:59:59', [1, 2, 3, 5, 7] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByCreationTo
     */
    public function testFindByCreationTo(string $creationTo, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setContactedTo(new \DateTime($creationTo))
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByAvailability()
    {
        return [
            [1, '10:00', '11:00', [1, 2, 3, 5, 7] ],
            [2, '10:00', '11:00', [6] ],
        ];
    }

    /**
     * @dataProvider dataProviderFindByAvailability
     */
    public function testFindByAvailability($weekDay, $timeStart, $timeEnd, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setWeekDay($weekDay)
            ->setTimeStart(new \DateTime($timeStart))
            ->setTimeEnd(new \DateTime($timeEnd))
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
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
        $searchCriteria = ($this->createSearchCriteria())
            ->setIncludeVariableSchedules($variableSchedule)
            ->setWeekDay(3) // Horaire pour n'obtenir aucun disponibilité
            ->setTimeStart(new \DateTime('08:00'))
            ->setTimeEnd(new \DateTime('08:01'))
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByActive()
    {
        return [
            [ true, [1, 2, 3, 5, 6, 7] ],
            [ false, [2, 3] ], // La 1, 5, 6, 7 sont actives
        ];
    }

    /**
     * @dataProvider dataProviderFindByActive
     */
    public function testFindByActive(bool $active, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setIncludeActiveCareRequest($active)
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByArchived()
    {
        return [
            [ true, [1, 2, 3, 5, 6, 7] ],
            [ false, [1, 3, 5, 6, 7] ], // La 2 est archivée
        ];
    }

    /**
     * @dataProvider dataProviderFindByArchived
     */
    public function testFindByArchived(bool $archived, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setIncludeArchivedCareRequest($archived)
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderFindByAbandoned()
    {
        return [
            [ true, [1, 2, 3, 5, 6, 7] ],
            [ false, [1, 2, 5, 6, 7] ], // La 3 est abandonné
        ];
    }

    /**
     * @dataProvider dataProviderFindByAbandoned
     */
    public function testFindByAbandoned(bool $abandoned, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setIncludeAbandonedCareRequest($abandoned)
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function datProviderFindByComplaint()
    {
        return [
            [ 1, [ 1, 2, 3, 6] ],
            [ 2, [ 5, 7 ] ],
        ];
    }

    /**
     * @dataProvider datProviderFindByComplaint
     */
    public function testFindByComplaint($complaintId, $expected)
    {
        $searchCriteria = ($this->createSearchCriteria())
            ->setComplaint($this->complaintRepository->find($complaintId))
        ;

        $this->assertSame(
            $expected,
            $this->careRequestIds($this->careRequestFinder->find($searchCriteria, $this->currentOffice))
        );
    }


    public function dataProviderSortResults()
    {
        return [
            [ [1, 2, 5, 7] ],
            [ [1, 5, 2, 7] ],
            [ [2, 1, 5, 7] ],
            [ [2, 5, 1, 7] ],
            [ [5, 1, 2, 7] ],
            [ [5, 2, 1, 7] ],
            [ [5, 7, 2, 1] ],
            [ [7, 5, 2, 1] ],
        ];
    }

    /**
     * @dataProvider dataProviderSortResults
     */
    public function testSortResults($unsortedCareRequestIds)
    {
        $searchResults = [];
        foreach ($unsortedCareRequestIds as $careRequestId) {
            $searchResults[] = [
                'careRequest' => $this->careRequestRepository->find($careRequestId),
            ];
        }

        $this->careRequestFinder->sortSearchResult($searchResults);

        // $searchResults est désormais trié
        $this->assertSame([2, 1, 5, 7], $this->careRequestIds($searchResults, false));
    }
}
