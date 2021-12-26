<?php

namespace App\Tests\Controller;

use App\Entity\CareRequest;
use App\Repository\DoctorRepository;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends AbstractControllerTestCase
{
    private PatientRepository $patientRepository;
    private DoctorRepository $doctorRepository;
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->setUpTestController([
            __DIR__ . '/../../fixtures/tests/homeController/care_request.yaml',
            __DIR__ . '/../../fixtures/tests/article.yaml',
        ]);
        
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->patientRepository = $container->get(PatientRepository::class);
        $this->doctorRepository = $container->get(DoctorRepository::class);
    }
    
    public function testPostAsAnonymous()
    {
        $crawler = $this->client->request('GET', "/home");
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND); // Redirection vers le login
        $this->assertResponseRedirects('/login');
    }

    public function testPostAsAdmin()
    {
        $this->loginUser('admin@example.com');
        $crawler = $this->client->request('GET', "/home");
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
    
    public function testGetAsDoctor()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/home");
        $this->assertResponseIsSuccessful();

        // Il doit y avoir deux articles à voir
        $this->assertSelectorExists('section.articles');
        $this->assertCount(1, $crawler->filter('section.articles ul > li'));

        // Il doit y avoir un patient en anomalie
        $this->assertSelectorExists('section.patients-anomaly');
        $this->assertCount(1, $crawler->filter('section.patients-anomaly ul > li'));
        
        // Le nombre de jours d'activité est par défaut de 7
        $this->assertSelectorTextContains('section.activity .card-header', '7');
        // Il doit y avoir 1 activité depuis 7 jours
        $this->assertCount(1, $crawler->filter('section.activity ul > li'));
    }
    
    public function testNoPatientAnomaly()
    {
        // Création d'une care request pour le patient en anomalie
        $careRequest = new CareRequest();
        $careRequest
            ->setPatient($this->patientRepository->find(2))
            ->setDoctorCreator($this->doctorRepository->find(1))
        ;
        $this->em->persist($careRequest);
        $this->em->flush();
        
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/home");
        $this->assertResponseIsSuccessful();

        // Il ne doit pas y avoir de patient en anomalie
        $this->assertSelectorNotExists('section.patients-anomaly');
    }
    
    public function testHiddenArticles()
    {
        $this->loginUser('user2@example.com');
        $crawler = $this->client->request('GET', "/home");
        $this->assertResponseIsSuccessful();

        // Il doit y avoir deux articles à voir (un visible et un invisible)
        $this->assertSelectorExists('section.articles');
        $this->assertCount(2, $crawler->filter('section.articles ul > li'));
        $this->assertCount(1, $crawler->filter('section.articles ul > li.d-none'));
    }
    

    public function dataProviderActivityDaysSince()
    {
        return [
            [null, 7, 30],
            [6, 6, 30],
            [7, 7, 30],
            [30, 30, 60],
            [60, 60, 90],
            [61, 61, null],
            [90, 90, null],
        ];
    }
    
    /**
     * @dataProvider dataProviderActivityDaysSince
     */
    public function testActivityDaysSince($urlParameter, $expectedSince, $expectedNext)
    {
        
        $this->loginUser('user1@example.com');
        $url = '/home' . ($urlParameter != null ? '?daysSince=' : '') . $urlParameter ?? '';

        $crawler = $this->client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        
        $this->assertSelectorTextContains('section.activity .card-header', $expectedSince);
        if ($expectedNext) {
            $this->assertSelectorTextContains('section.activity .card-body > a', $expectedNext);
        } else {
            $this->assertSelectorNotExists('section.activity .card-body > a');
        }
    }
    
    public function testNoActivity()
    {
        $this->loginUser('user1@example.com');
        $crawler = $this->client->request('GET', "/home?daysSince=1");
        $this->assertResponseIsSuccessful();
        
        // Aucune activité depuis un jour
        // On doit voir un message d'avertissement
        $this->assertSelectorExists('section.activity .card-body > p.alert-warning');
    }
}