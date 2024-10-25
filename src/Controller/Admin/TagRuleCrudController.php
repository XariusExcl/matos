<?php

namespace App\Controller\Admin;

use App\Entity\TagRule;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class TagRuleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TagRule::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),
            FormField::addColumn(6),
            TextField::new('arg1')
            ->setLabel('Tag règle'),
            FormField::addColumn(6),
            TextField::new('arg2')
                ->setLabel('Tag désactivé'),
        ];
    }
}
