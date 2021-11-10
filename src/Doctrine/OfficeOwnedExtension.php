<?php
namespace App\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use App\Entity\Office;
use App\Entity\OfficeOwnedInterface;

class OfficeOwnedExtension implements QueryCollectionExtensionInterface
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
        if (!$reflectionClass->implementsInterface(OfficeOwnedInterface::class)) {
           return;
        }
        
        // La class App\Entity\Office implémente OfficeOwnedInterface (pour gérer la sécurité du get item)
        // mais ne doit pas être affectée par ce DoctrineExtension car seul le ROLE_ADMIN
        // est autorisé à faire un get collection
        if ($reflectionClass->getName() == Office::class) {
            return;
        }

        // Recherche du office id de l'utilisateur connecté
        /** @var App\Entity\User $user */
        $user = $this->security->getUser();
        $office = $user->getOffice();
        if ($office === null) {
            throw new \LogicException('Should not be here');
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if ($reflectionClass->hasProperty('office')) {
            // L'entité possède une propriété office
            // On peut donc directement la sélectionner
            $officeFieldAlias = $rootAlias;
        } elseif($reflectionClass->hasProperty('patient')) {
            // L'entité possède une propriété patient
            // Il faut ajouter une jointure sur cette table
            $officeFieldAlias = 'officeOwnedExtension_alias_patient';
            $queryBuilder->innerJoin(sprintf('%s.patient', $rootAlias), $officeFieldAlias);
        } elseif($reflectionClass->hasProperty('careRequest')) {
            // L'entité possède une propriété careRequest
            // Il faut ajouter une jointure sur la table care_request
            // puis sur la table patient
            $officeFieldAliasCareRequest = 'officeOwnedExtension_alias_care_request';
            $officeFieldAliasPatient = 'officeOwnedExtension_alias_patient';
            $officeFieldAlias = $officeFieldAliasPatient;
            $queryBuilder
                ->innerJoin(sprintf('%s.careRequest', $rootAlias), $officeFieldAliasCareRequest)
                ->innerJoin(sprintf('%s.patient', $officeFieldAliasCareRequest), $officeFieldAliasPatient)
                ;
        } else {
            throw new \LogicException('Should not be here');
        }

        $queryBuilder
            ->andWhere(sprintf('%s.office = :officeOwnedExtension_office_id', $officeFieldAlias))
            ->setParameter(':officeOwnedExtension_office_id', $office->getId())
            ;
    }
}
