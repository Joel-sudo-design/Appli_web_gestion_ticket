<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }
    public function findWaitingTicketByUserId($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :val')
            ->setParameter('val', $value)
            ->andWhere('t.status = :status')
            ->setParameter('status', 'En attente')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
       ;
    }
    public function findInProgressTicketByUserId($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :val')
            ->setParameter('val', $value)
            ->andWhere('t.status = :status')
            ->setParameter('status', 'En cours')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function findResolvedTicketByUserId($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :val')
            ->setParameter('val', $value)
            ->andWhere('t.status = :status')
            ->setParameter('status', 'Résolu')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function findOneById($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.id = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    public function findLastByUserId($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
    public function findAllWaitingTicket($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', 'En attente')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function findAllinProgressTicket($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', 'En cours')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    public function findAllResolvedTicket($value): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', 'Résolu')
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
