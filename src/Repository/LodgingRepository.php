<?php

namespace Api\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Ulid;
use Api\Entity\Lodging;
use Api\Exception\BusinessException;

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
     * @return Lodging[] Returns an array of Lodging entity
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.id in (:ids)')
            ->setParameter('ids', array_map(fn(Ulid $id) => $id->toBinary(), $ids), ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();
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
}
