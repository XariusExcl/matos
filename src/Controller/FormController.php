<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\AudiovisualLoanType;
use App\Form\GraphicDesignLoanType;
use App\Form\VRLoanType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\EquipmentCategory;
use App\Entity\Loan;
use App\Entity\Equipment;

class FormController extends AbstractController
{
    function createLoanableDates(): array
    {
        $days = [];
        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $dt = new \DateTime("today");
        $dt->modify("+1 day");
        for ($i = 0; $i < 14; $i++)
        {
            // Skip weekends
            $dw = $dt->format('N');
            if ($dw <= 5)
                $days[$weekDays[$dw-1]." ".$dt->format('d/m')] = $i;

            $dt->modify('+1 day');
        }
        return $days;
    }

    function parseDepartureReturnDates(array $date): array|bool
    {
        $hours = ['morning' => ["9:15", "12:30"], 'afternoon' => ["14:00", "17:30"], 'evening' => ["17:30", "9:15"]];
            
        if ($date['day'] < 0 || $date['day'] > 13) {
            // throw new \Exception("Invalid day: ".$date['day']);
            $this->addFlash('error','La date d\'emprunt est invalide.');
            return false;
        }
        if (!isset($date['timeSlot']) || !in_array("morning", $date['timeSlot']) && !in_array("afternoon", $date['timeSlot']) && !in_array("evening", $date['timeSlot']))
        {
            // throw new \Exception("Invalid time slot: ".var_dump($date['timeSlot']));
            $this->addFlash('error','Le créneau horaire est invalide.');
            return false;
        }

        $date['day'] += 1;
        $start = (new \DateTime("today"))
            ->modify("+".$date['day']." day")
            ->modify($hours[$date['timeSlot'][0]][0]);
        
        $end = (new \DateTime("today"))
            ->modify("+".($date['day'] + ((end($date['timeSlot']) == 'evening')? 1 : 0))." day")
            ->modify($hours[end($date['timeSlot'])][1]);

        return ['start' => $start, 'end' => $end];
    }

    function addAndCheckEquipmentAvailability(Loan &$loan, array $loanEquipment, array $loans, array $equipmentInfo): bool
    {
        $equipmentAlreadyLoaned = [];
        foreach ($loans as $l) 
            foreach($l->getEquipmentLoaned() as $el)
                array_push($equipmentAlreadyLoaned, $el->getId());

        $equipmentAlreadyLoanedCount = array_count_values($equipmentAlreadyLoaned);

        foreach($loanEquipment as $equipmentId)
        {
            if (array_key_exists($equipmentId, $equipmentAlreadyLoanedCount))
            {
                if ($equipmentAlreadyLoanedCount[$equipmentId] >= $equipmentInfo[$equipmentId]->getQuantity())
                    return false;
            }
            dump("Adding equipment ".$equipmentId." to loan ".$loan->getId());

            $loan->addEquipmentLoaned($equipmentInfo[$equipmentId]);
        }
        return true;
    }

    function groupEquipmentBySubcategoryAndById($equipmentLoanable, &$equipmentCategories, &$equipmentInfo)
    {
        foreach($equipmentLoanable as $equipment)
        {
            $key = $equipment->getSubCategory()?->getSlug() ?? "";
            if (!isset($equipmentCategories[$key]))
            $equipmentCategories[$key] = [$equipment];
            else
                array_push($equipmentCategories[$key], $equipment);
            
            // Store equipment info by id for frontend display
            $equipmentInfo[$equipment->getId()] = $equipment;
        }
    }

    #[Route('/form/audiovisual', name: 'reservation_form_audiovisual')]
    public function audiovisualForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $category = $entityManager->getRepository(EquipmentCategory::class)->findBySlug('audiovisual');
        $loan = new Loan();
        $options = [];

        // Get the loanable equipment for the category
        $equipmentLoanable = $entityManager->getRepository(Equipment::class)->findLoanableByCategory($category->getId());

        // Group equipment by subcategory
        $options['equipmentCategories'] = [];
        $equipmentInfo = [];
        $this->groupEquipmentBySubcategoryAndById($equipmentLoanable, $options['equipmentCategories'], $equipmentInfo);

        $options['days'] = $this->createLoanableDates();

        // Create the form
        $form = $this->createForm(AudiovisualLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['audiovisual_loan'];
            
            $loan->setLoaner($this->getUser());
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_audiovisual');

            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);

            // Add the equipment to the loan
            $loanEquipment = [];
            if (!empty($data['cameras']))
                array_push($loanEquipment, $data['cameras']);

            if (!empty($data['lenses']))
                array_push($loanEquipment, $data['lenses']);

            if (!empty($data['microphones']))
                foreach($data['microphones'] as $microphone)
                    array_push($loanEquipment, $microphone);

            if (!empty($data['lights']))
                foreach($data['lights'] as $light)
                    array_push($loanEquipment, $light);

            if (!empty($data['tripods']))
                array_push($loanEquipment, $data['tripods']);

            if (!empty($data['batteries']))
                foreach($data['batteries'] as $accessory)
                    array_push($loanEquipment, $accessory);

            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $loans = $entityManager->getRepository(Loan::class)->findInBetweenDates($parsedDates['start'], $parsedDates['end']);
            if (!$this->addAndCheckEquipmentAvailability($loan, $loanEquipment, $loans, $equipmentInfo))
            {
                $this->addFlash('error','Un ou plusieurs équipements sont déjà réservés pour cette période.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $entityManager->persist($loan);
            $entityManager->flush();

            MailerController::sendRequestConfirmMail($loan, $mailer);
            MailerController::sendNewRequestMail($loan, $mailer);

            $this->addFlash('success', 'Votre réservation a bien été enregistrée.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('form/audiovisual.html.twig', [
            'formName' => 'Emprunt Audiovisuel',
            'form' => $form,
            'equipmentInfo' => $equipmentInfo,
            'equipmentInfoJson' => json_encode(array_map(function($e) { return [
                "quantity" => $e->getQuantity(),
                "name" => $e->getName()
            ]; }, $equipmentInfo))
        ]);
    }
    
    #[Route('/form/vr', name: 'reservation_form_vr')]
    public function vrForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $category = $entityManager->getRepository(EquipmentCategory::class)->findBySlug('vr');
        $loan = new Loan();
        $options = [];

        // Get the loanable equipment for the category
        $equipmentLoanable = $entityManager->getRepository(Equipment::class)->findLoanableByCategory($category->getId());

        // Group equipment by subcategory
        $options['equipmentCategories'] = [];
        $equipmentInfo = [];
        $this->groupEquipmentBySubcategoryAndById($equipmentLoanable, $options['equipmentCategories'], $equipmentInfo);

        $options['days'] = $this->createLoanableDates();

        // Create the form
        $form = $this->createForm(VRLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['vr_loan'];
            
            $loan->setLoaner($this->getUser());
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_vr');

            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);

            // Add the equipment to the loan
            $loanEquipment = [];
            if (!empty($data['headset']))
                array_push($loanEquipment, $data['headset']);

            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $loans = $entityManager->getRepository(Loan::class)->findInBetweenDates($parsedDates['start'], $parsedDates['end']);
            if (!$this->addAndCheckEquipmentAvailability($loan, $loanEquipment, $loans, $equipmentInfo))
            {
                $this->addFlash('error','Un ou plusieurs équipements sont déjà réservés pour cette période.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $entityManager->persist($loan);
            $entityManager->flush();

            MailerController::sendRequestConfirmMail($loan, $mailer);
            MailerController::sendNewRequestMail($loan, $mailer);

            $this->addFlash('success', 'Votre réservation a bien été enregistrée.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('form/vr.html.twig', [
            'formName' => 'Emprunt VR',
            'form' => $form,
            'equipmentInfo' => $equipmentInfo,
            'equipmentInfoJson' => json_encode(array_map(function($e) { return $e->getQuantity(); }, $equipmentInfo))
        ]);
    }

    #[Route('/form/graphic-design', name: 'reservation_form_graphic_design')]
    public function graphicDesignForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $category = $entityManager->getRepository(EquipmentCategory::class)->findBySlug('graphic_design');
        $loan = new Loan();
        $options = [];

        // Get the loanable equipment for the category
        $equipmentLoanable = $entityManager->getRepository(Equipment::class)->findLoanableByCategory($category->getId());

        // Group equipment by subcategory
        $options['equipmentCategories'] = [];
        $equipmentInfo = [];
        $this->groupEquipmentBySubcategoryAndById($equipmentLoanable, $options['equipmentCategories'], $equipmentInfo);

        $options['days'] = $this->createLoanableDates();

        // Create the form
        $form = $this->createForm(GraphicDesignLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['graphic_design_loan'];
            
            $loan->setLoaner($this->getUser());
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_graphic_design');
            
            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);

            // Add the equipment to the loan
            $loanEquipment = [];
            if (!empty($data['tablet']))
                array_push($loanEquipment, $data['tablet']);

            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }
        
            $loans = $entityManager->getRepository(Loan::class)->findInBetweenDates($parsedDates['start'], $parsedDates['end']);
            if (!$this->addAndCheckEquipmentAvailability($loan, $loanEquipment, $loans, $equipmentInfo))
            {
                $this->addFlash('error','Un ou plusieurs équipements sont déjà réservés pour cette période.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }
    
            $entityManager->persist($loan);
            $entityManager->flush();

            MailerController::sendRequestConfirmMail($loan, $mailer);
            MailerController::sendNewRequestMail($loan, $mailer);

            $this->addFlash('success', 'Votre réservation a bien été enregistrée.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('form/graphic_design.html.twig', [
            'formName' => 'Emprunt Graphisme & Infographie',
            'form' => $form,
            'equipmentInfo' => $equipmentInfo,
            'equipmentInfoJson' => json_encode(array_map(function($e) { return $e->getQuantity(); }, $equipmentInfo)),
        ]);
    }
}