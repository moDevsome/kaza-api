<?php

namespace Api\Repository;

use Api\Entity\LocationArea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * @extends ServiceEntityRepository<LocationArea>
 */
class LocationAreaRepository extends ServiceEntityRepository
{

    /**
     * Count the number of user which have lodging associated with the given location area id
     * @param int $locationAreaId
     * @param int $userId
     * @param bool $negativeLookUp - If TRUE, the query will return the count of location which are not used by the given user
     */
    public function countLocationAreaByUserId(int $locationAreaId, int $userId, bool $negativeLookUp = false): int
    {
        $userIdOperator = $negativeLookUp === true ? '!=' : '=';
        try {
            $countResult = $this->dbConnection->fetchAllAssociative(
                '
                select
                    count(u.id)
                from
                    location_area locarea
                left join location loc on
                    loc.area_id = locarea.id
                left join lodging l on
                    l.location_id = loc.id
                left join host h on
                    h.id = l.host_id
                left join `user` u on u.id = h.user_id
                where loc.id = :location_id
                and u.id ' . $userIdOperator . ' :user_id',
                [
                    'location_id' => $locationAreaId,
                    'user_id' => $userId
                ]
            );

            if (count($countResult) !== 1)
                throw new Exception(500, 'count($countResult) !== 1');

            return $countResult[0]['count(u.id)'];
        } catch (Exception $e) {
            throw new Exception('countLocationAreaByUserId error: ' . $e->getMessage(), $e->getCode());
        }
    }

    public function __construct(ManagerRegistry $registry, private readonly Connection $dbConnection)
    {
        parent::__construct($registry, LocationArea::class);
    }
}
