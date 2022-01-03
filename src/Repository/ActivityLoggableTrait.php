<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

trait ActivityLoggableTrait
{
    public function addWhereSince(QueryBuilder $qb, string $entityAlias, \DateTimeInterface $since): QueryBuilder
    {
        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->gte(sprintf("%s.createdAt", $entityAlias), ':since'),
                    $qb->expr()->gte(sprintf("COALESCE(%s.modifiedAt, '0001-01-01')", $entityAlias), ':since'),
                )
            )
            ->setParameter(':since', $since)
        ;
    }
}
