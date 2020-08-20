<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    //******left join with sql********
    public function getAllComments(): array
    {
        $conn=$this->getEntityManager()->getConnection();
        $sql='
        SELECT C.*,u.name,u.surname,d.title FROM comment c
        JOIN user u ON u.id=c.userid
        JOIN content d ON d.id=c.contentid
        ORDER BY c.id DESC
        ';
        $stmt=$conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    //******left join with doctrine********
    public function getAllCommentsUser($userid): array
    {
        $qb= $this->createQueryBuilder('c')
            ->select('c.id,c.subject,c.comment,c.rate,c.created_at,c.status,c.contentid,d.title')
            ->leftJoin('App\Entity\Content', 'd', 'WITH','d.id=c.contentid')

            ->where('c.userid = :userid')
            ->setParameter('userid', $userid)
            ->orderBy('c.id','DESC');
        $query=$qb->getQuery();
        return $query->execute();
    }


}
