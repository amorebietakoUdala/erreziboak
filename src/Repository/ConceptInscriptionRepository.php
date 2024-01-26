<?php

namespace App\Repository;

use App\Entity\ConceptInscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConceptInscription>
 *
 * @method ConceptInscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConceptInscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConceptInscription[]    findAll()
 * @method ConceptInscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConceptInscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConceptInscription::class);
    }

    // /**
    //  * @return ConceptInscription[] Returns an array of ConceptInscription objects
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
    public function findOneBySomeField($value): ?ConceptInscription
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
