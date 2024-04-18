<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ProductCrudController extends AbstractCrudController

{




    public const PRODUCT_BASE_PATH ='uploads/images/product';
    public const PRODUCT_UPLOAD_DIR = 'public/uploads/images/product';
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }



    public function configureFields(string $pageName): iterable
    {
        return [
           
            TextField::new('Product_name'),
            MoneyField::new('price') 
            ->setCurrency("EUR")
            ->setCustomOption('storedAsCents', false),

     
            AssociationField::new('category')
            ->setFormTypeOption('choice_label', 'product_name'),

    
            // AssociationField::new('sub_category')
            // ->setFormTypeOption('choice_label', 'name'),


            ImageField::new('image')
            ->setBasePath(self::PRODUCT_BASE_PATH)
            ->setUploadDir(self::PRODUCT_UPLOAD_DIR)
            ->setUploadedFileNamePattern('[randomhash].[extension]'),
   
            NumberField::new('discount'),

            BooleanField::new('promotion'),
           
            
    ];
           

      
    }
 



}
