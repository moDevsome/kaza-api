<?php

namespace Api\Repository;

use Api\Entity\Equipment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Exception;

/**
 * @extends ServiceEntityRepository<Equipment>
 */
class EquipmentRepository extends ServiceEntityRepository
{

    /**
     * @param array Array of equipment id
     * @return Equipment[] Returns an array of Equipment objects
     */
    public function findByIds(array $ids): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.id in (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::INTEGER)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count the number of user which have lodging associated with the given equipment id
     * @param int $equipmentId
     * @param int $userId
     * @param bool $negativeLookUp - If TRUE, the query will return the count of equipment which are not used by the given user
     */
    public function countEquipmentByUserId(int $equipmentId, int $userId, bool $negativeLookUp = false): int
    {
        $userIdOperator = $negativeLookUp === true ? '!=' : '=';
        try {
            $countResult = $this->dbConnection->fetchAllAssociative(
                '
                select
                    count(u.id)
                from
                    equipment_lodging el
                left join lodging l on
                    l.id = el.lodging_id
                left join host h on
                    h.id = l.host_id
                left join `user` u on u.id = h.user_id
                where el.equipment_id = :equipment_id
                and u.id ' . $userIdOperator . ' :user_id',
                [
                    'equipment_id' => $equipmentId,
                    'user_id' => $userId
                ]
            );

            if (count($countResult) !== 1)
                throw new Exception(500, 'count($countResult) !== 1');

            return $countResult[0]['count(u.id)'];
        } catch (Exception $e) {
            throw new Exception('countEquipmentByUserId error: ' . $e->getMessage(), $e->getCode());
        }
    }

    public function __construct(ManagerRegistry $registry, private readonly Connection $dbConnection)
    {
        parent::__construct($registry, Equipment::class);
    }
}
