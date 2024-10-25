<?php

namespace App\Controller\Admin;

use App\Entity\Equipment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class EquipmentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Equipment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            AssociationField::new('category'),
            AssociationField::new('subCategory'),
            TextField::new('type'),
            ImageField::new('imageName')
                ->setBasePath('public/images/equipments')
                ->setUploadDir('public/images/equipments')
                ->hideOnIndex(),
            TextField::new('serial_number'),
            BooleanField::new('showInTable'),
            BooleanField::new('loanable'),
            TextEditorField::new('description')->hideOnIndex(),
            AssociationField::new('location'),
            AssociationField::new('loans')
                ->hideOnForm()
                ->hideOnIndex(),
            NumberField::new('quantity'),
            BooleanField::new('numbered')
        ];
    }
}
