<?php

namespace Api\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Ulid;
use Api\Entity\Lodging;

/**
 * @extends ServiceEntityRepository<Lodging>
 */
class LodgingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly Connection $dbConnection)
    {
        parent::__construct($registry, Lodging::class);
    }

    /**
     * Find Lodging entities by making a lookup on the title and description
     * Return ids only
     * @param string $queryString
     * @param ?string $operator
     * @return Lodging[] Returns an array of Lodging entity
     *
     * @phpstan-param '='|'!='|'LIKE'|'REGEXP' $operator
     */
    public function findIdsByContent(string $queryString, ?string $operator = '='): array
    {

        // Find all entities which have a match
        // We have to use the "createNativeQuery" method because REGEXP is not supported by Doctrine DQL
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Api\Entity\Lodging', 'l');
        $rsm->addFieldResult('l', 'id', 'id');

        $sqlQuery = 'select id from lodging l where l.title ' . $operator . ' :value and l.description ' . $operator . ' :value';
        $query = $this->getEntityManager()->createNativeQuery($sqlQuery, $rsm);
        $query->setParameter(':value', match ($operator) {
            'REGEXP' => strtolower(preg_replace('#\s+#', '|', $queryString)),
            'LIKE' => '%' . $queryString . '%',
            default => $queryString,
        });

        $lookUpResults = $query->getResult();
        if (count($lookUpResults) > 0) {

            // We have to clear the current cached object then to execute a new query to avoid null datas
            $this->getEntityManager()->clear();
            $results = $this->findByIds(array_map(fn($lookUpResult) => $lookUpResult->getId(), $lookUpResults));
            return array_map(fn($entity) => $entity->getId(), $results);
        } else {
            return $lookUpResults;
        }
    }

    /**
     * @param Ulid[] Array of tag id
     * @return Ulid[] Returns an array of Lodging id
     */
    public function findIdsByTagIds(array $ids): array
    {
        return $this->findIdsByElementIds('TAG', $ids);
    }

    /**
     * @param Ulid[] Array of equipment id
     * @return Ulid[] Returns an array of Lodging id
     */
    public function findIdsByEquipmentIds(array $ids): array
    {
        return $this->findIdsByElementIds('EQUIPMENT', $ids);
    }

    /**
     * @param Ulid[] Array of location id
     * @return Ulid[] Returns an array of Lodging id
     */
    public function findByLocationIds(array $ids): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.location in (:ids)')
            ->setParameter('ids', array_map(fn(Ulid $id) => $id->toBinary(), $ids), ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Ulid[] Array of lodging id
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Lodging[] Returns an array of Lodging entity
     */
    public function findByIds(
        array $ids,
        array $criteria = array(),
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('l');

        $queryBuilder->where('l.id in (:ids)')
            ->setParameter('ids', array_map(fn(Ulid $id) => $id->toBinary(), $ids), ArrayParameterType::BINARY);

        foreach ($criteria as $criteriaName => $criteriaVal) {
            switch ($criteriaName) {
                case 'hostId':
                    $queryBuilder = $this->addHostCriteria($queryBuilder, $criteriaVal);
                    break;

                case 'rating':
                    $queryBuilder = $this->addRatingCriteria($queryBuilder, $criteriaVal);
                    break;

                default:
                    break;
            }
        }

        if ($orderBy !== null and count($orderBy) === 2) {
            $queryBuilder
                ->orderBy('l.' . $orderBy[0], strtoupper($orderBy[1]));
        } else {
            $queryBuilder
                ->orderBy('l.id', 'ASC');
        }

        return $queryBuilder
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Lodging[] Returns an array of Lodging entity
     */
    public function findBy(array $criteria, array|null $orderBy = null, int|null $limit = null, int|null $offset = null): array
    {

        $queryBuilder = $this->createQueryBuilder('l');

        foreach ($criteria as $criteriaName => $criteriaVal) {
            switch ($criteriaName) {
                case 'hostId':
                    $this->addHostCriteria($queryBuilder, $criteriaVal);
                    break;

                case 'title':
                    $ormCriterias['l.title like :title'] = '%' . strtolower($criteriaVal) . '%';
                    $queryBuilder->andWhere('l.title like :title');
                    $queryBuilder->setParameter('title', '%' . strtolower($criteriaVal) . '%');
                    break;

                case 'rating':
                    $queryBuilder = $this->addRatingCriteria($queryBuilder, $criteriaVal);
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

    /**
     * @param Ulid[] Array of element id
     * @phpstan-param 'EQUIPMENT'|'TAG' $element
     * @return Ulid[] Returns an array of Lodging id
     */
    private function findIdsByElementIds(string $element, array $ids): array
    {
        // Get lodging ids associated with the given tag
        $from = $element === 'EQUIPMENT' ? 'equipment_lodging el' : 'lodging_tag lt';
        $whereField = $element === 'EQUIPMENT' ? 'el.equipment_id' : 'lt.tag_id';
        return array_map(fn($binaryLodgingId) => Ulid::fromBinary($binaryLodgingId['lodging_id']), $this->dbConnection->fetchAllAssociative(
            '
                select
                    lodging_id
                from
                    ' . $from . '
                where ' . $whereField . ' in (:ids)',
            [
                'ids' => implode(',', array_map(fn($id) => $id->toBinary(), $ids)),
            ]
        ));
    }

    /**
     * Add "rating where" values to the given query builder
     * @param QueryBuilder $queryBuilder
     * @param string $criteriaVal
     * @return QueryBuilder
     */
    private function addRatingCriteria(QueryBuilder $queryBuilder, string $criteriaVal): QueryBuilder
    {
        $values = array();
        preg_match_all('#[0-9]#', $criteriaVal, $values);
        $criteriaVal = str_ireplace('rating', 'l.rating', $criteriaVal);
        // Replace each given value by a token
        $i = 0;
        foreach (array_unique($values[0]) as $value) {
            $criteriaVal = str_ireplace($value, ':rating_' . $i, $criteriaVal);
            $queryBuilder->setParameter('rating_' . $i, $value, ParameterType::INTEGER);
            $i++;
        }
        $queryBuilder->andWhere('(' . $criteriaVal . ')');

        return $queryBuilder;
    }

    /**
     * Add "Host where" values to the given query builder
     * @param QueryBuilder $queryBuilder
     * @param string $criteriaVal
     * @return QueryBuilder
     */
    private function addHostCriteria(QueryBuilder $queryBuilder, string $criteriaVal): QueryBuilder
    {
        $ormCriterias['l.Host = :host'] = $criteriaVal;
        $queryBuilder->andWhere('l.Host = :host');
        $queryBuilder->setParameter('host', $criteriaVal, UuidType::NAME);

        return $queryBuilder;
    }
}
