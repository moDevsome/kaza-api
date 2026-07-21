<?php

namespace Api\Repository;

use Api\Entity\ContentTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContentTranslation>
 */
class ContentTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContentTranslation::class);
    }

    /**
     * @param string $translationValue
     * @param string $tag
     * @param ?string $translationKey
     * @return ContentTranslation[] Returns an array of ContentTranslation entity
     */
    public function findByTranslationValue(string $translationValue, string $tag, ?string $translationKey = null): array
    {

        $queryBuilder = $this->createQueryBuilder('ct');
        if ($translationKey !== null) {
            $queryBuilder->where('ct.translationValue = :value and ct.tag = :tag and ct.translationKey = :key')
                ->setParameter('value', $translationValue)
                ->setParameter('tag', $tag)
                ->setParameter(':key', $translationKey);
        } else {
            $queryBuilder->where('ct.translationValue = :value and ct.tag = :tag')
                ->setParameter('value', $translationValue)
                ->setParameter('tag', $tag);
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
