<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EquipmentCategory;
use App\Entity\Loan;
use App\Entity\Equipment;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Convert a date to a timeslot
        function getTimeslot(\DateTime $date): int 
        {
            $now = new \DateTime("today");
            $diff = $date->diff($now);

            $hours = $diff->d*24 + $diff->h;

            if ($hours < 13)
                return 0;
            
            if ($hours > 14*24-12)
                return 20;
    
            // Compute weekend days between the two dates
            $skippedWeekendDays = 0;
            for ($i = 0; $i < $diff->d; $i++)
            {
                $now->modify('+1 day');
                $dw_ = $now->format('N');
                if ($dw_ > 5)
                    $skippedWeekendDays++;
            }

            return (int)($hours/13) - 2*$skippedWeekendDays;
        }

        $categories = $entityManager->getRepository(EquipmentCategory::class)->findAll();
        $equipmentRepository = $entityManager->getRepository(Equipment::class);

        $loanableEquipment = [];
        foreach($categories as $category)
        {
            $loanableEquipment[$category->getId()] = [];
            $equipments = $equipmentRepository->findLoanableByCategory($category->getId());
            foreach($equipments as $equipment)
            {
                if ($equipment->isLoanable() && $equipment->isShowInTable() && $equipment->getSubCategory()->getSlug() != "other")
                    array_push($loanableEquipment[$category->getId()], $equipment);
            }
        }

        $loans = $entityManager->getRepository(Loan::class)->findInNextTwoWeeks(new \DateTime());

        // Create dates for the next two weeks
        $dates = [];
        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $dt = new \DateTime("today");
        for ($i = 0; $i < 14; $i++)
        {
            // Skip weekends
            $dw = $dt->format('N');

            if ($dw <= 5)
                array_push($dates, $weekDays[$dw-1]." ".$dt->format('d/m'));

            $dt->modify('+1 day');
        }

        // Create a dictionary of loaned timeslots by category and equipment id
        $equipmentLoaned = [];
        foreach($loans as $loan)
        {
            foreach($loan->getEquipmentLoaned() as $equipment)
            {
                $categoryId = $equipment->getCategory()->getId();

                if (!array_key_exists($categoryId, $equipmentLoaned) || !array_key_exists($equipment->getId(), $equipmentLoaned[$categoryId]))
                    $equipmentLoaned[$categoryId][$equipment->getId()] = [["start" => getTimeslot($loan->getDepartureDate()),"end" => getTimeslot($loan->getReturnDate()), "status" => $loan->getStatus()]];
                else
                    array_push($equipmentLoaned[$categoryId][$equipment->getId()], ["start" => getTimeslot($loan->getDepartureDate()),"end" => getTimeslot($loan->getReturnDate()), "status" => $loan->getStatus()]);
            }
        }

        return $this->render('main/index.html.twig', [
            'categories' => $categories,
            'loanableEquipment' => $loanableEquipment, 
            'equipmentLoaned'=> $equipmentLoaned,
            'dates' => $dates
        ]);
    }
}
