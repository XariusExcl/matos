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
use App\Entity\UnavailableDays;

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
        $loanId = $_GET['id']; // Yes, this is bad, but EasyAdmin forced my hand

        return $this->render('admin/loan_review.html.twig', [
            'loanId' => $loanId,
        ]);
    }

    #[Route('/admin/loan/return', name: 'admin_loan_return')]
    public function loanReturn(): Response
    {
        $loanId = $_GET['id'];

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
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-inbox');
        yield MenuItem::linkToCrud('Jours "indisponibles"', 'fas fa-calendar', UnavailableDays::class);
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-home', '/');

        yield MenuItem::section('Crud');
        yield MenuItem::linkToCrud('Matos', 'fas fa-camera', Equipment::class);
        yield MenuItem::linkToCrud('Catégories', 'fas fa-tags', EquipmentCategory::class);
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-clipboard', Loan::class);
        yield MenuItem::linkToCrud('Salles', 'fas fa-door-open', Location::class);
    }
}
