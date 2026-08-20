<?php

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Document;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UlidType;

/**
 * @extends ServiceEntityRepository<Document>
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function existsForCurrency(Currency $currency): bool
    {
        return $this->createQueryBuilder('document')
                ->select('1')
                ->andWhere(
                    'IDENTITY(document.currency) = :currencyId'
                )
                ->setParameter(
                    'currencyId',
                    $currency->getId(),
                    UlidType::NAME,
                )
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }

    /**
     * @return list<Document>
     */
    public function findForIndex(): array
    {
        return $this->createQueryBuilder('document')
            ->leftJoin('document.thirdParty', 'thirdParty')
            ->addSelect('thirdParty')
            ->leftJoin('document.folder', 'folder')
            ->addSelect('folder')
            ->leftJoin('document.documentType', 'documentType')
            ->addSelect('documentType')
            ->leftJoin('document.status', 'status')
            ->addSelect('status')
            ->leftJoin('document.currency', 'currency')
            ->addSelect('currency')
            ->orderBy('document.issuedAt', 'DESC')
            ->addOrderBy('document.recordedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array{
     *     documents: list<Document>,
     *     total: int
     * }
     */
    public function findPaginated(
        int $page,
        int $perPage,
        ?string $search = null,
        ?string $folderId = null,
        ?string $thirdPartyId = null,
        ?string $statusId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $page = max(1, $page);
        $perPage = max(1, $perPage);

        $offset = ($page - 1) * $perPage;

        $queryBuilder = $this->createQueryBuilder('document')
            ->leftJoin('document.thirdParty', 'thirdParty')
            ->addSelect('thirdParty')
            ->leftJoin('document.folder', 'folder')
            ->addSelect('folder')
            ->leftJoin('document.documentType', 'documentType')
            ->addSelect('documentType')
            ->leftJoin('document.status', 'status')
            ->addSelect('status')
            ->leftJoin('document.currency', 'currency')
            ->addSelect('currency');

        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere('LOWER(document.reference) LIKE :search')
                ->setParameter(
                    'search',
                    '%' . mb_strtolower($search) . '%',
                );
        }

        if ($folderId !== null && $folderId !== '') {
            $queryBuilder
                ->andWhere('IDENTITY(document.folder) = :folderId')
                ->setParameter(
                    'folderId',
                    $folderId,
                    UlidType::NAME,
                );
        }

        if ($thirdPartyId !== null && $thirdPartyId !== '') {
            $queryBuilder
                ->andWhere('IDENTITY(document.thirdParty) = :thirdPartyId')
                ->setParameter(
                    'thirdPartyId',
                    $thirdPartyId,
                    UlidType::NAME,
                );
        }

        if ($statusId !== null && $statusId !== '') {
            $queryBuilder
                ->andWhere('IDENTITY(document.status) = :statusId')
                ->setParameter(
                    'statusId',
                    $statusId,
                    UlidType::NAME,
                );
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $queryBuilder
                ->andWhere('document.issuedAt IS NOT NULL')
                ->andWhere('document.issuedAt >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $queryBuilder
                ->andWhere('document.issuedAt IS NOT NULL')
                ->andWhere('document.issuedAt <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $documents = $queryBuilder
            ->orderBy('document.issuedAt', 'DESC')
            ->addOrderBy('document.recordedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();

        $countQueryBuilder = $this->createQueryBuilder('document')
            ->select('COUNT(document.id)');

        if ($search !== null && $search !== '') {
            $countQueryBuilder
                ->andWhere('LOWER(document.reference) LIKE :search')
                ->setParameter(
                    'search',
                    '%' . mb_strtolower($search) . '%',
                );
        }

        if ($folderId !== null && $folderId !== '') {
            $countQueryBuilder
                ->andWhere('IDENTITY(document.folder) = :folderId')
                ->setParameter(
                    'folderId',
                    $folderId,
                    UlidType::NAME,
                );
        }

        if ($thirdPartyId !== null && $thirdPartyId !== '') {
            $countQueryBuilder
                ->andWhere('IDENTITY(document.thirdParty) = :thirdPartyId')
                ->setParameter(
                    'thirdPartyId',
                    $thirdPartyId,
                    UlidType::NAME,
                );
        }

        if ($statusId !== null && $statusId !== '') {
            $countQueryBuilder
                ->andWhere('IDENTITY(document.status) = :statusId')
                ->setParameter(
                    'statusId',
                    $statusId,
                    UlidType::NAME,
                );
        }

        if ($dateFrom !== null && $dateFrom !== '') {
            $countQueryBuilder
                ->andWhere('document.issuedAt IS NOT NULL')
                ->andWhere('document.issuedAt >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo !== null && $dateTo !== '') {
            $countQueryBuilder
                ->andWhere('document.issuedAt IS NOT NULL')
                ->andWhere('document.issuedAt <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        $total = (int) $countQueryBuilder
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'documents' => $documents,
            'total' => $total,
        ];
    }
}
