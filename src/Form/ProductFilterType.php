<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('category', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'query_builder' => function(CategoryRepository $categoryRepository) {
                return $categoryRepository->createQueryBuilder('c')->orderBy('c.name', 'ASC');
            },
            'required' => false,
            'placeholder' => 'Toutes les catégories',
            'label' => 'Catégories',
            // 'label_attr' => ['id' => 'category_label'],
            'mapped' => false, // Ne pas mapper ce champ avec une propriété de l'entité
        ])
        ->add('subcategory', EntityType::class, [
            'class' => SubCategory::class,
            'choice_label' => 'name',
            'query_builder' => function(SubCategoryRepository $subCategoryRepository) {
                return $subCategoryRepository->createQueryBuilder('sc')->orderBy('sc.name', 'ASC');
            },
            'placeholder' => 'Toutes les sous-catégories',
            'label' => 'Sous-catégories',
            // 'label_attr' => ['id' => 'subcategory_label'],
            'required' => false,
            'mapped' => false, // Ne pas mapper ce champ avec une propriété de l'entité
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Filtrer',
        ]);
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
