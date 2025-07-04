<?php

namespace App\Controller\Admin;

use App\Entity\Loan;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
class DashboardWidgetsController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Loan::class;
    }

    public function equipmentCurrentlyOut(EntityManagerInterface $em): Response
    {
        $loans = $em->getRepository(Loan::class)->findOutPendingReturn();
        
        $equipments = [];
        $quantities = [];
        foreach ($loans as $loan) {
            foreach ($loan->getEquipmentLoaned() as $equipmentItem) {
                if (!in_array($equipmentItem, $equipments)) {
                    $equipments[] = $equipmentItem;
                    $quantities[$equipmentItem->getId()] = 1;
                } else {
                    $quantities[$equipmentItem->getId()]++;
                }
            }
        }
        
        return $this->render('admin/widget_currently_out.html.twig', [
            'equipments' => $equipments,
            'quantities' => $quantities
        ]);
    }

    public function loansPending(EntityManagerInterface $em): Response
    {
        $pendingLoans = $em->getRepository(Loan::class)->findPending();

        return $this->render('admin/widget_pending.html.twig', [
            'loans' => $pendingLoans,
        ]);
    }

    public function loansPendingReturn(EntityManagerInterface $em): Response
    {
        $pendingLoans = $em->getRepository(Loan::class)->findPendingReturn();
        
        $loans = [];

        foreach($pendingLoans as &$loan) {
            $loanDisplay = [
                "id" => $loan->getId(),
                "loaner" => $loan->getLoaner()->getName() ?? str_split($loan->getLoaner()->getEmail(), '@')[0],
                "equipmentLoaned" => count($loan->getEquipmentLoaned()),
                "assignee" => $loan->getAssignee()->getName() ?? "[Personne]",
            ];
            array_push($loans, ["type" => "D", "date" => $loan->getDepartureDate(),...$loanDisplay]);
            array_push($loans, ["type" => "R", "date" => $loan->getReturnDate(),...$loanDisplay]);
        }

        usort($loans, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        return $this->render('admin/widget_pending_return.html.twig', [
            'loans' => $loans,
        ]);
    }
}
