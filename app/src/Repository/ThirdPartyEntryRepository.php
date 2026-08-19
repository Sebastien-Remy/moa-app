<?php

namespace App\Repository;

use App\Entity\Document;
use App\Entity\ThirdPartyEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<ThirdPartyEntry>
 */
final class ThirdPartyEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThirdPartyEntry::class);
    }

    /**
     * @return ThirdPartyEntry[]
     */
    public function findForDocument(Document $document): array
    {
        $documentId = $document->getId();

        if ($documentId === null) {
            return [];
        }

        return $this->createQueryBuilder('entry')
            ->leftJoin('entry.thirdParty', 'thirdParty')
            ->addSelect('thirdParty')
            ->leftJoin('entry.currency', 'currency')
            ->addSelect('currency')
            ->andWhere('IDENTITY(entry.document) = :documentId')
            ->setParameter(
                'documentId',
                $documentId,
                UlidType::NAME,
            )
            ->orderBy('entry.entryDate', 'ASC')
            ->addOrderBy('thirdParty.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
