<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
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
                    'required' => false,
                    'placeholder' => 'All Categories',
                    'label_attr' => ['id' => 'category_label'],
                    // 'attr' => ['class' => 'form-select'],
                ])
                ->add('subcategory', EntityType::class, [
                    'class' => SubCategory::class,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'All Sub-Categories',
                    'label_attr' => ['id' => 'subcategory_label'],
                    // 'attr' => ['class' => 'form-select'],
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Filter',
                    // 'attr' => ['class' => 'btn btn-primary'],
                ]);
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
