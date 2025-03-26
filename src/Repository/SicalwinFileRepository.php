<?php

namespace App\Repository;

use App\Entity\SicalwinFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\User;

/**
 * @method SicalwinFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method SicalwinFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method SicalwinFile[]    findAll()
 * @method SicalwinFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SicalwinFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SicalwinFile::class);
    }
}
