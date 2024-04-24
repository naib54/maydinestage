<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Stock;



use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController


{
    private AdminUrlGenerator $adminUrlGenerator;

    // Fix the typo in the method name: __construct instead of __constuct
    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function product(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(ProductCrudController::class)
            
            ->generateUrl();

            

        return $this->redirect($url);
       
    }
    
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony E Commerce'); // Fix the typo in 'Symfony'
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Product', 'fa fa-home', Product::class);
     
        yield MenuItem::linkToCrud('Category', 'fas fa-list', Category::class);

        yield MenuItem::linkToCrud('Sub_Category', 'fas fa-list', SubCategory::class);

        yield MenuItem::linkToCrud('Stock', 'fas fa-list', Stock::class);

        // yield MenuItem::linkToCrud('Sub', 'fas fa-list', SubCategory::class);
    }
}
