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
use App\Entity\UnavailableDays;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag): Response
    {
        if ($parameterBag->get('SAE_MODE'))
            return $this->redirectToRoute('app_main_sae');

        $skipWeekends = $parameterBag->get('LOAN_DISABLE_WEEKENDS');
        // Convert a date to a timeslot
        function getTimeslot(\DateTime $date, $skipWeekends = true): int 
        {
            $now = new \DateTime("today");
            $diff = $date->diff($now);

            $hours = $diff->d*24 + $diff->h;

            if ($hours < 13 || $diff->invert == 0)
                return 0;
            
            if ($hours > 14*24-12)
                return $skipWeekends?20:28;
    
            // Compute weekend days between the two dates
            $skippedWeekendDays = 0;
            for ($i = 0; $i < $diff->d; $i++)
            {
                $dw_ = $now->format('N');
                if ($dw_ > 5 && $skipWeekends)
                    $skippedWeekendDays++;
                $now->modify('+1 day');
            }
            return $diff->d*2 + (($diff->h > 13)?1:0) - 2*$skippedWeekendDays;
        }

        if ($this->getParameter(('MESSAGE_CONTENT')) != null)
            $this->addFlash('info', $this->getParameter(('MESSAGE_CONTENT')));

        $audiovisualCategory = $entityManager->getRepository(EquipmentCategory::class)->findOneBy(['slug' => 'audiovisual']);
        $tableEquipment = $entityManager->getRepository(Equipment::class)->findShownInTableByCategory($audiovisualCategory->getId());
        $unavailableDays = $entityManager->getRepository(UnavailableDays::class)->findInNextTwoWeeks(new \DateTime(), $audiovisualCategory->getId());

        // Create a dictionary of unavailable timeslots
        $unavailableDaysTimeSlots = [];
        foreach($unavailableDays as $u)
            $unavailableDaysTimeSlots[$u->getId()] = [
                "slotStart" => getTimeslot($u->getDateStart(), $skipWeekends),
                "slotEnd" => getTimeslot($u->getDateEnd(), $skipWeekends),
                "timeStartEnd" => "({$u->getDateStart()->format('H:i')} - {$u->getDateEnd()->format('H:i')})",
                "preventsLoans" => $u->isPreventsLoans(),
                "comment" => $u->getComment()
            ];

        $loans = $entityManager->getRepository(Loan::class)->findInNextTwoWeeks(new \DateTime());

        // Create dates for the next two weeks
        $dates = [];
        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $dt = new \DateTime("today");
        for ($i = 0; $i < 14; $i++)
        {
            $dw = $dt->format('N');
            if ($dw <= 5 || !$skipWeekends)
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

                $equipmentTimeSlot = [
                    "start" => getTimeslot($loan->getDepartureDate()),
                    "end" => getTimeslot($loan->getReturnDate()),
                    "status" => $loan->getStatus(),
                    "info" => "{$loan->getLoaner()->getName()} ({$loan->getDepartureDate()->format("H:i")} - {$loan->getReturnDate()->format("H:i")})"
                ];

                if (!array_key_exists($categoryId, $equipmentLoaned) || !array_key_exists($equipment->getId(), $equipmentLoaned[$categoryId]))
                    $equipmentLoaned[$categoryId][$equipment->getId()] = [$equipmentTimeSlot];
                else
                    array_push($equipmentLoaned[$categoryId][$equipment->getId()], $equipmentTimeSlot);
            }
        }

        return $this->render('main/index.html.twig', [
            'audiovisualCategory' => $audiovisualCategory,
            'tableEquipment' => $tableEquipment, 
            'equipmentLoaned'=> $equipmentLoaned,
            'dates' => $dates,
            'unavailableDays' => $unavailableDaysTimeSlots
        ]);
    }

    #[Route('/sae', name: 'app_main_sae')]
    public function indexSae(EntityManagerInterface $entityManager): Response
    {
        $startDate = new \DateTime("12/09/2024");
        $endDate = new \DateTime("12/11/2024");

        function getTimeslotSae(\DateTime $date, $startDate, $endDate): int 
        {
            $diff = $startDate->diff($date);
            if ($diff->invert == 1)
                return 0;
            if ($date > $endDate)
                return 10;

            return $diff->d*3 + ($diff->h >= 20 ? 2 : ($diff->h >= 15 ? 1 : 0));
        }

        if ($this->getParameter(('MESSAGE_CONTENT')) != null)
            $this->addFlash('info', $this->getParameter(('MESSAGE_CONTENT')));

        $audiovisualCategory = $entityManager->getRepository(EquipmentCategory::class)->findOneBy(['slug' => 'audiovisual_sae']);
        $tableEquipment = $entityManager->getRepository(Equipment::class)->findShownInTableByCategory($audiovisualCategory->getId());

        $loans = $entityManager->getRepository(Loan::class)->findInNextTwoWeeks(new \DateTime());

        $dates = [];
        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $dt = clone $startDate;
        for ($i = 0; $dt < $endDate; $i++)
        {
            $dw = $dt->format('N');
            array_push($dates, $weekDays[$dw-1]." ".$dt->format('d/m'));
            $dt->modify('+1 day');
        }

        $equipmentLoaned = [];
        foreach($loans as $loan)
        {
            foreach($loan->getEquipmentLoaned() as $equipment)
            {
                $categoryId = $equipment->getCategory()->getId();
                $equipmentTimeSlot = [
                    "start" => getTimeslotSae($loan->getDepartureDate(), $startDate, $endDate),
                    "end" => getTimeslotSae($loan->getReturnDate(), $startDate, $endDate),
                    "status" => $loan->getStatus(),
                    "info" => "{$loan->getLoaner()->getName()} ({$loan->getDepartureDate()->format("H:i")} - {$loan->getReturnDate()->format("H:i")})"
                ];

                if (!array_key_exists($categoryId, $equipmentLoaned) || !array_key_exists($equipment->getId(), $equipmentLoaned[$categoryId]))
                    $equipmentLoaned[$categoryId][$equipment->getId()] = [$equipmentTimeSlot];
                else
                    array_push($equipmentLoaned[$categoryId][$equipment->getId()], $equipmentTimeSlot);
            }
        }

        return $this->render('main/index.html.twig', [
            'audiovisualCategory' => $audiovisualCategory,
            'tableEquipment' => $tableEquipment, 
            'equipmentLoaned'=> $equipmentLoaned,
            'dates' => $dates,
            'saeMode' => true
        ]);
    }

    #[Route('/my-loans', name: 'app_myloans')]
    #[IsGranted('ROLE_USER', message: 'Vous devez être connecté pour accéder à cette page.')]
    public function myLoans(): Response
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
        
        if ($loan->getLoaner() != $user) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler un prêt qui ne vous appartient pas !');
            return $this->redirectToRoute('app_myloans');
        }

        if ($loan->getStatus() != LoanStatus::PENDING->value) {
            $this->addFlash('error', 'Vous ne pouvez plus annuler ce prêt !');
            return $this->redirectToRoute('app_myloans');
        }

        $loan->setStatus(LoanStatus::CANCELLED_BY_LOANER->value);
        $entityManager->flush();
        $this->addFlash('success', 'Le prêt a bien été annulé.');
        # TODO : Send an email
        return $this->redirectToRoute('app_myloans');
    }
}
