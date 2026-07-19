<?php

namespace Api\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Ulid;
use Api\Entity\Lodging;
use Api\Exception\BusinessException;

/**
 * @extends ServiceEntityRepository<Lodging>
 */
class LodgingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lodging::class);
    }

    public function findBy(array $criteria, array|null $orderBy = null, int|null $limit = null, int|null $offset = null): array
    {

        $queryBuilder = $this->createQueryBuilder('l');

        foreach ($criteria as $criteriaName => $criteriaVal) {
            switch ($criteriaName) {
                case 'hostId':
                    if (Ulid::isValid($criteriaVal) === false)
                        throw new BusinessException(400, 'The given hostId is not a valid indentifier');

                    $ormCriterias['l.Host = :host'] = $criteriaVal;
                    $queryBuilder->andWhere('l.Host = :host');
                    $queryBuilder->setParameter('host', $criteriaVal, UuidType::NAME);
                    break;

                case 'title':
                    $ormCriterias['l.title like :title'] = '%' . strtolower($criteriaVal) . '%';
                    $queryBuilder->andWhere('l.title like :title');
                    $queryBuilder->setParameter('title', '%' . strtolower($criteriaVal) . '%');
                    break;

                default:
                    break;
            }
        }

        return $queryBuilder
            ->orderBy('l.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}
