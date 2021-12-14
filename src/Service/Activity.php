<?php

namespace App\Service;

use App\Entity\ActivityLoggableEntityInterface;
use App\Repository\ActivityLoggableRepositoryInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Activity
{
    const SORT_OLDER_FIRST = 'older_first';
    const SORT_NEWER_FIRST = 'newer_first';
    
    const ACTION_CREATION = 'creation';
    const ACTION_CREATION_MODIFICATION = 'creation_modification';
    const ACTION_MODIFICATION = 'modification';

    public function __construct(
        private ServiceLocator $serviceLocator,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }
    
    private function getValorisation(ActivityLoggableEntityInterface $entity, \DateTimeInterface $since)
    {
        // Si la date de modification est nulle : on prend la date de création
        if (empty($entity->getModificationDate())) {
            return [
                'action' => self::ACTION_CREATION,
                'date' => $entity->getCreationDate(),
            ];
        }
        
        // Si la date de création est postérieur à la date demandée, c'est elle qui compte
        if ($entity->getCreationDate() >= $since) {
            return [
                'action' => self::ACTION_CREATION_MODIFICATION,
                'date' => $entity->getCreationDate(),
            ];
        }

        return [
            'action' => self::ACTION_MODIFICATION,
            'date' => $entity->getModificationDate(),
        ];
    }
    
    private function getAuthorName(ActivityLoggableEntityInterface $entity, $action)
    {
        switch ($action) {
            case self::ACTION_CREATION:
                $author = $entity->getCreator();
                break;

            case self::ACTION_CREATION_MODIFICATION:
                $author = $entity->getCreator();
                break;

            case self::ACTION_MODIFICATION:
                $author = $entity->getModifier();
                break;

            default:
                throw new \LogicException(sprintf('Should not be here : unknown action %s', $action));
        }
        
        if ($author == null) {
            throw new \LogicException('No activity author found');
        }
        
        return $author->getDisplayName();
    }
    
    public function getActiveEntities($office, $since = null, $sort = self::SORT_NEWER_FIRST): array
    {
        if ($since === null) {
            $since = new \DateTimeImmutable();
        }
        
        $entities = [];
        foreach ($this->serviceLocator->getProvidedServices() as $serviceName) {
            /** @var ActivityLoggableRepositoryInterface */
            $repository = $this->serviceLocator->get($serviceName);

            $entities = array_merge($entities, $repository->findActiveSince($office, $since));
        }
        
        // Création d'un tableau contenant les entités associés à leur valorisation (action et date de l'action)
        $datedEntities = array_map(function(ActivityLoggableEntityInterface $entity) use($since) {
            $valorisation = $this->getValorisation($entity, $since);
            $route = $entity->getActivityRoute();
            return [
                'objectName' => $entity->getActivityObjectName(),
                'authorName' => $this->getAuthorName($entity, $valorisation['action']),
                'icon' => $entity->getActivityIcon(),
                'logMessage' => $entity->getActivityMessage($valorisation['action']),
                'url' => $this->urlGenerator->generate($route['name'], $route['parameters']),
                'valorisationDate' => $valorisation['date'],
            ];
        }, $entities);
        
        // Tri du tableau par date de valorisation
        usort($datedEntities, function($a, $b) use($sort) {
            if ($sort == self::SORT_OLDER_FIRST) {
                return $a['valorisationDate'] <=> $b['valorisationDate'];
            } else {
                return $b['valorisationDate'] <=> $a['valorisationDate'];
            }
        });

        return $datedEntities;
    }
}