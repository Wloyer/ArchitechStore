<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function findByFilters(?string $name = null, ?string $format = null, ?string $orderBy = null, ?string $direction = 'ASC')
    {
        $qb = $this->createQueryBuilder('f');

        // Filtrer par nom de fichier (file_name dans la bdd)
        if ($name) {
            $qb->andWhere('f.fileName LIKE :name') //utilisation du camelCase pour fileName correspondant à file_name dans la table, le snake n'a pas marché
               ->setParameter('name', '%' . $name . '%');
        }

        // Filtrer par format (type)
        if ($format) {
            $qb->andWhere('f.type = :format')
               ->setParameter('format', $format);
        }

        // Gestion du tri (par défaut upload_date ou autre champ)
        if ($orderBy) {
            // On mappe les options de tri aux colonnes de la base de données
            switch ($orderBy) {
                case 'uploadDate':
                    $qb->orderBy('f.uploadDate', $direction); //pareil semantique camelCase et non snake
                    break;
                case 'size':
                    $qb->orderBy('f.size', $direction);
                    break;
                case 'fileName':
                    $qb->orderBy('f.fileName', $direction);
                    break;
                default:
                    $qb->orderBy('f.uploadDate', $direction);  // Par défaut, on trie par date d'upload
                    break;
            }
        }

        return $qb->getQuery()->getResult();
    }

    // Compter le nombre total de fichiers
    public function countTotalFiles(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // Compter le nombre de fichiers uploadés aujourd'hui
    public function countFilesUploadedToday(): int
    {
        $today = new \DateTime('today');

        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.uploadDate >= :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return File[] Returns an array of File objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?File
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
