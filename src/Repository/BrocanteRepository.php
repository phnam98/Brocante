<?php

namespace App\Repository;

use App\Entity\Brocante;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Brocante|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brocante|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brocante[]    findAll()
 * @method Brocante[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrocanteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Brocante::class);
    }

    // /**
    //  * @return Brocante[] Returns an array of Brocante objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Brocante
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /* Retrouver une brocante par son id */
    public function trouveParDepartement($dpt){

        return $this->createQueryBuilder('b')
            ->andWhere('b.lieu = :id_dpt')
            ->setParameter('id_dpt', $dpt)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /* Retrouver une brocante par son id (même qu'en haut mais noms différents)*/
    public function trouveParVille($ville){

        return $this->createQueryBuilder('b')
            ->andWhere('b.lieu = :id_dpt')
            ->setParameter('id_dpt', $ville)
            ->distinct()
            ->getQuery()
            ->getResult();
    }

    /* Retrouver une brocante par son id (même qu'en haut mais noms différents)*/
    public function trouveParDate($date){

        return $this->createQueryBuilder('b')
            ->andWhere('b.date = :date%')
            ->setParameter('date', $date)
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
