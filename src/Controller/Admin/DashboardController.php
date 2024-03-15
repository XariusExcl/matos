<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Equipment;
use App\Entity\EquipmentCategory;
use App\Entity\Loan;
use App\Entity\Location;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/loan/review', name: 'admin_loan_review')]
    public function loanReview(): Response
    {
        // Get the loan id parameter from the url
        $loanId = $_GET['id']; // Yes, this is bad, but EasyAdmin forced my hand

        return $this->render('admin/loan_review.html.twig', [
            'loanId' => $loanId,
        ]);
    }

    #[Route('/admin/loan/return', name: 'admin_loan_return')]
    public function loanReturn(): Response
    {
        // Get the loan id parameter from the url
        $loanId = $_GET['id']; // Yes, this is bad, but EasyAdmin forced my hand

        return $this->render('admin/loan_return.html.twig', [
            'loanId' => $loanId,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MATOS Back office');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Admin');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Crud');
        yield MenuItem::linkToCrud('Matos', 'fas fa-list', Equipment::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-list', EquipmentCategory::class);
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-list', Loan::class);
        yield MenuItem::linkToCrud('Salles', 'fas fa-list', Location::class);
    }
}
