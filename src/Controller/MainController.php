<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EquipmentCategory;
use App\Entity\Loan;
use App\Entity\Equipment;
use App\Entity\LoanStatus;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

        /*
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
        */

        $audiovisualCategory = $entityManager->getRepository(EquipmentCategory::class)->findOneBy(['slug' => 'audiovisual']);
        $tableEquipment = $entityManager->getRepository(Equipment::class)->findShownInTableByCategory($audiovisualCategory->getId());


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
            'audiovisualCategory' => $audiovisualCategory,
            'tableEquipment' => $tableEquipment, 
            'equipmentLoaned'=> $equipmentLoaned,
            'dates' => $dates
        ]);
    }

    #[Route('/my-loans', name: 'app_myloans')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page.')]
    public function myLoans(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        return $this->render('main/myloans.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/cancel-loan/{id}', name: 'app_cancel_loan')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page.')]
    public function cancelLoan(EntityManagerInterface $entityManager, $id): Response
    {
        $loan = $entityManager->getRepository(Loan::class)->find($id);
        $user = $this->getUser();
        
        if ($loan->getLoaner() != $user)
        {
            $this->addFlash('error', 'Vous ne pouvez pas annuler un prêt qui ne vous appartient pas !');
            return $this->redirectToRoute('app_myloans');
        } else {
            $loan->setStatus(LoanStatus::CANCELLED_BY_LOANER->value);
            $entityManager->flush();
            $this->addFlash('success', 'Le prêt a bien été annulé.');
            # TODO : Send an email
            return $this->redirectToRoute('app_myloans');
        }
    }
}
