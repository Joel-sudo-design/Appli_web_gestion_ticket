<?php

namespace App\Repository;

use App\Entity\OpenTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpenTicket>
 */
class OpenTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenTicket::class);
    }


        public function findAllByTicketId($value): array
        {
            return $this->createQueryBuilder('o')
                ->andWhere('o.ticket = :val')
                ->setParameter('val', $value)
                ->getQuery()
                ->getResult()
            ;
        }
}
