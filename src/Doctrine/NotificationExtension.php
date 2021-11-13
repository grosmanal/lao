<?php
namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use App\Entity\Notification;

class NotificationExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private Security $security)
    {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null): void
    {
        // Les admins peuvent tout faire (gnark, gnark, gnark…)
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $reflectionClass = new \ReflectionClass($resourceClass);
        if ($reflectionClass->getName() != Notification::class) {
            return;
        }
        
        // Recherche de l'utilisateur connecté
        /** @var App\Entity\Doctor $doctor */
        $doctor = $this->security->getUser();
        
        $rootAlias = $queryBuilder->getRootAliases()[0];
        
        $queryBuilder
            ->andWhere(sprintf('%s.doctor = :doctor', $rootAlias))
            ->setParameter(':doctor', $doctor)
            ;
    }
}