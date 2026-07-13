<?php

namespace Api\Repository;

use Api\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{

    /**
     * Count the number of user which have lodging associated with the given location id
     * @param int $locationId
     * @param int $userId
     * @param bool $negativeLookUp - If TRUE, the query will return the count of location which are not used by the given user
     */
    public function countLocationByUserId(int $locationId, int $userId, bool $negativeLookUp = false): int
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

    public function __construct(ManagerRegistry $registry,  private readonly Connection $dbConnection)
    {
        parent::__construct($registry, Location::class);
    }
}
