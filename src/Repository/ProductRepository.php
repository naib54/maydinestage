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


    public function findByCategoryAndSubcategory(?Category $category, ?SubCategory $subcategory): array
    {
        // Création du constructeur de requête
        $qb = $this->createQueryBuilder('p'); // 'p' représente l'alias pour l'entité Product

        // Si une catégorie est fournie, on ajoute une jointure et une condition pour la catégorie
        if ($category !== null) {
            $qb->leftJoin('p.category', 'cat') // 'cat' représente l'alias pour l'entité Category
                ->andWhere('cat = :category')
                ->setParameter('category', $category);
        }

        // Si une sous-catégorie est fournie, on ajoute une jointure et une condition pour la sous-catégorie
        if ($subcategory !== null) {
            $qb->leftJoin('p.subcategory', 'sub') 
                ->andWhere('sub = :subcategory')
                ->setParameter('subcategory', $subcategory);
        }

        // Exécution de la requête et retour des résultats
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
