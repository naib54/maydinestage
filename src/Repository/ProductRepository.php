<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByCategoryAndSubcategory(Category $category, ?SubCategory $subcategory): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('c = :category')
            ->setParameter('category', $category);

        if ($subcategory !== null) {
            $qb->leftJoin('p.subcategory', 's')
                ->andWhere('s = :subcategory')
                ->setParameter('subcategory', $subcategory);
        }

        return $qb->getQuery()->getResult();
    }



    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    
    
    //  Création d'une requête pour récuperer les produits associés à une catég. spécifique
    // public function findByCategory(string $categoryName): array
    // {
    //     return $this->createQueryBuilder('p') 
    //     ->join('p.category', 'c')
    //     ->andWhere('c.name = :category')
    //     ->setParameter('category', $categoryName)
    //     ->getQuery()
    //     ->getResult();
    // }
    
}
