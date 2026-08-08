<?php

namespace Api\Repository;

use Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\Query\ResultSetMapping;
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
     * Find Location entities by making a lookup on the name
     * Return the full DB entity
     * @param string $queryString
     * @param ?string $operator
     * @return Location[] Returns an array of location entity
     *
     * @phpstan-param '='|'!='|'LIKE'|'REGEXP' $operator
     */
    public function findByName(string $queryString, ?string $operator = '='): array
    {

        // Find all entities which have a match
        // We have to use the "createNativeQuery" method because REGEXP is not supported by Doctrine DQL
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Api\Entity\Location', 'l');
        $rsm->addFieldResult('l', 'location_id', 'id');
        $rsm->addFieldResult('l', 'location_name', 'name');
        $sqlQuery = 'select l.id location_id, l.name location_name from location l
                    where l.name ' . $operator . ' :value';
        $query = $this->getEntityManager()->createNativeQuery($sqlQuery, $rsm);

        $query->setParameter(':value', match ($operator) {
            'REGEXP' => strtolower(preg_replace('#\s+#', '|', $queryString)),
            'LIKE' => '%' . $queryString . '%',
            default => $queryString,
        });
        $lookUpResults = $query->getResult();
        if (count($lookUpResults) > 0) {

            // We have to clear the current cached object then to execute a new query to avoid null area
            // Use JOIN in the query above does not work
            $this->getEntityManager()->clear();
            return $this->findByIds(array_map(fn($lookUpResult) => $lookUpResult->getId(), $lookUpResults));
        } else {
            return $lookUpResults;
        }
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
