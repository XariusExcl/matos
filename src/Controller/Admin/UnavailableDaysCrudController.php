<?php

namespace App\Controller\Admin;

use App\Entity\UnavailableDays;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UnavailableDaysCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UnavailableDays::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['dateStart' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('dateStart'),
            DateTimeField::new('dateEnd'),
            BooleanField::new('preventsLoans'),
            TextField::new('comment'),
            AssociationField::new('restrictedCategory')
        ];
    }
}
