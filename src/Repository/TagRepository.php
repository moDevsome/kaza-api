<?php

namespace Api\Repository;

use Exception;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Component\Uid\Ulid;
use Api\Entity\Tag;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly Connection $dbConnection)
    {
        parent::__construct($registry, Tag::class);
    }

    /**
     * @param array Array of tag id
     * @return Tag[] Returns an array of Tag entity
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.id in (:ids)')
            ->setParameter('ids', array_map(fn(Ulid $id) => $id->toBinary(), $ids), ArrayParameterType::BINARY)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find Tag entities by making a lookup on the name
     * Return the full DB entity
     * @param string $queryString
     * @param ?string $operator
     * @return Tag[] Returns an array of tag entity
     *
     * @phpstan-param '='|'!='|'LIKE'|'REGEXP' $operator
     */
    public function findByName(string $queryString, ?string $operator = '='): array
    {

        // Find all entities which have a match
        // We have to use the "createNativeQuery" method because REGEXP is not supported by Doctrine DQL
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Api\Entity\Tag', 'l');
        $rsm->addFieldResult('l', 'id', 'id');
        $rsm->addFieldResult('l', 'name', 'name');

        $sqlQuery = 'select l.id, l.name from tag l where l.name ' . $operator . ' :value';
        $query = $this->getEntityManager()->createNativeQuery($sqlQuery, $rsm);
        $query->setParameter(':value', match ($operator) {
            'REGEXP' => strtolower(preg_replace('#\s+#', '|', $queryString)),
            'LIKE' => '%' . $queryString . '%',
            default => $queryString,
        });

        return $query->getResult();
    }

    /**
     * Count the number of user which have lodging associated with the given tag id
     * @param string $tagId
     * @param int $userId
     * @param bool $negativeLookUp - If TRUE, the query will return the count of tag which are not used by the given user
     */
    public function countTagByUserId(string $tagId, int $userId, bool $negativeLookUp = false): int
    {
        $userIdOperator = $negativeLookUp === true ? '!=' : '=';
        try {
            $countResult = $this->dbConnection->fetchAllAssociative(
                '
                select
                    count(u.id)
                from
                    lodging_tag lt
                left join lodging l on
                    l.id = lt.lodging_id
                left join host h on
                    h.id = l.host_id
                left join `user` u on u.id = h.user_id
                where lt.tag_id = :tag_id
                and u.id ' . $userIdOperator . ' :user_id',
                [
                    'tag_id' => $tagId,
                    'user_id' => $userId
                ]
            );

            if (count($countResult) !== 1)
                throw new Exception(500, 'count($countResult) !== 1');

            return $countResult[0]['count(u.id)'];
        } catch (Exception $e) {
            throw new Exception('countTagByUserId error: ' . $e->getMessage(), $e->getCode());
        }
    }
}
