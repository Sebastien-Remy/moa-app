<?php

namespace App\Repository;

use App\Entity\StoredFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StoredFile>
 */
class StoredFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(
            $registry,
            StoredFile::class,
        );
    }

    public function findOneByChecksum(
        string $checksum,
    ): ?StoredFile {
        return $this->createQueryBuilder('storedFile')
            ->andWhere('storedFile.checksum = :checksum')
            ->setParameter('checksum', $checksum)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
