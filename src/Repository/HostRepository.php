<?php

namespace Api\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Api\Entity\Host;

/**
 * @extends ServiceEntityRepository<Host>
 */
class HostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Host::class);
    }

    /**
     * Find the host associated with the given user id
     * @param int $userId
     * @return Host|null
     */
    public function findOneByUserId(int $userId): ?Host
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.user = :user_id')
            ->setParameter('user_id', $userId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
