<?php

namespace Api\Repository;

use Api\Entity\ContentTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;

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
     * @param ?string $operator
     * @return ContentTranslation[] Returns an array of ContentTranslation entity
     *
     * @phpstan-param '='|'!='|'LIKE'|'REGEXP' $operator
     */
    public function findByTranslationValue(string $translationValue, string $tag, ?string $translationKey = null, ?string $operator = '='): array
    {

        // We have to use the "createNativeQuery" method because REGEXP is not supported by Doctrine DQL
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Api\Entity\ContentTranslation', 'ct');
        $rsm->addFieldResult('ct', 'id', 'id');
        $rsm->addFieldResult('ct', 'translation_key', 'translationKey');
        $rsm->addFieldResult('ct', 'translation_value', 'translationValue');
        $rsm->addFieldResult('ct', 'tag', 'tag');
        $rsm->addFieldResult('ct', 'content_id', 'contentId');

        $sqlQuery = 'select * from content_translation ct where ct.translation_value ' . $operator . ' :value and ct.tag = :tag';
        if ($translationKey)
            $sqlQuery .= ' and ct.translation_key = :key';

        $query = $this->getEntityManager()->createNativeQuery($sqlQuery, $rsm);
        $query->setParameter(':value', match ($operator) {
            'REGEXP' => strtolower(preg_replace('#\s+#', '|', $translationValue)),
            'LIKE' => '%' . $translationValue . '%',
            default => $translationValue,
        });
        $query->setParameter('tag', $tag);

        if ($translationKey !== null)
            $query->setParameter(':key', $translationKey);

        return $query->getResult();
    }
}
