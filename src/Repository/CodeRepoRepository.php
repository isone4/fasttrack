<?php

namespace App\Repository;

use App\Entity\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class CodeRepoRepository
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    private function connection(): Connection
    {
        /** @var Connection $connection */
        $connection = $this->registry->getConnection();

        return $connection;
    }

    public function findByCriteria(Criteria $criteria): array
    {
        $qb = $this->connection()->createQueryBuilder()
            ->select('*')
            ->from('code_repo')

        ;
        $count = $qb->executeQuery()->rowCount();

        if ($criteria->getSearchValue()) {
            $qb->andWhere('(orgname ILIKE :searchValue OR reponame ILIKE :searchValue OR provider ILIKE :searchValue)')
                ->setParameter('searchValue', '%'.$criteria->getSearchValue().'%');
        }
        if (in_array($criteria->getColumnName(), ['reponame', 'id', 'orgname', 'provider', 'trust', 'creationdate'], true)) {
            $qb->addOrderBy($criteria->getColumnName(), $criteria->getSortingMethod());
        }

        $countFiltered = $qb->executeQuery()->rowCount();

        $qb
            ->setMaxResults($criteria->getPerPage())
            ->setFirstResult($criteria->getOffset())
        ;

        return [
            'count' => $count,
            'filteredCount' => $countFiltered,
            'page' => $criteria->getPage(),
            'perPage' => $criteria->getPerPage(),
            'items' => $qb->executeQuery()->fetchAllAssociative()];
    }
}
