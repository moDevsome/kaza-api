<?php

namespace Api\Repository;

use Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Component\Uid\Ulid;
use Api\Entity\Location;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry,  private readonly Connection $dbConnection)
    {
        parent::__construct($registry, Location::class);
    }

    /**
     * @param array Array of location id
     * @return Location[] Returns an array of Location entity
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.id in (:ids)')
            ->setParameter('ids', array_map(fn(Ulid $id) => $id->toBinary(), $ids), ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count the number of user which have lodging associated with the given location id
     * @param string $locationId
     * @param int $userId
     * @param bool $negativeLookUp - If TRUE, the query will return the count of location which are not used by the given user
     */
    public function countLocationByUserId(string $locationId, int $userId, bool $negativeLookUp = false): int
    {
        $userIdOperator = $negativeLookUp === true ? '!=' : '=';
        try {
            $countResult = $this->dbConnection->fetchAllAssociative(
                '
                select
                    count(u.id)
                from
                    location loc
                left join lodging l on
                    l.location_id = loc.id
                left join host h on
                    h.id = l.host_id
                left join `user` u on u.id = h.user_id
                where loc.id = :location_id
                and u.id ' . $userIdOperator . ' :user_id',
                [
                    'location_id' => $locationId,
                    'user_id' => $userId
                ]
            );

            if (count($countResult) !== 1)
                throw new Exception(500, 'count($countResult) !== 1');

            return $countResult[0]['count(u.id)'];
        } catch (Exception $e) {
            throw new Exception('countLocationByUserId error: ' . $e->getMessage(), $e->getCode());
        }
    }
}
