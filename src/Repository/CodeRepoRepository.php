<?php

namespace App\Repository;

use App\Entity\CodeRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CodeRepo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeRepo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeRepo[]    findAll()
 * @method CodeRepo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeRepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeRepo::class);
    }

    // /**
    //  * @return CodeRepo[] Returns an array of CodeRepo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CodeRepo
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
