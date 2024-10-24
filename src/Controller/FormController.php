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
use App\Entity\LoanStatus;
use App\Entity\User;
use App\Entity\UnavailableDays;

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

    function parseDepartureReturnDates(array &$data, array &$unavailableDays): array|bool
    {
        $timeSlots = ["0930", "1100", "1230", "1400", "1530", "1700"];

        if ($data['startDay'] < 0
        || $data['startDay'] > 13
        || $data['endDay'] < 0
        || $data['endDay'] > 13
        || $data['startDay'] > $data['endDay']
        || !in_array($data['startTimeSlot'], $timeSlots)
        || !in_array($data['endTimeSlot'], $timeSlots))
        {
            $this->addFlash('error','La date d\'emprunt est invalide.');
            return false;
        }

        $start = (new \DateTime("today"))
            ->modify("+".($data['startDay'] + 1)." day")
            ->modify("+".substr($data['startTimeSlot'], 0, 2)." hours")
            ->modify("+".substr($data['startTimeSlot'], 2, 2)." minutes");

        $end = (new \DateTime("today"))
            ->modify("+".($data['endDay'] + 1)." day")
            ->modify("+".substr($data['endTimeSlot'], 0, 2)." hours")
            ->modify("+".substr($data['endTimeSlot'], 2, 2)." minutes");

        foreach ($unavailableDays as $ud)
        {
            if (
                ($start >= $ud->getDateStart() && $start <= $ud->getDateEnd())
                || ($end >= $ud->getDateStart() && $end <= $ud->getDateEnd())
                || ($start <= $ud->getDateStart() && $end >= $ud->getDateEnd())
            )
            {
                $this->addFlash('error','La période sélectionnée est indisponible.');
                return false;
            }
        }

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

    function hasReachedMaxConcurrentLoans(User $loaner): bool
    {
        return $loaner->getLoans()->filter(function(Loan $l) {
            return ($l->getStatus() == LoanStatus::ACCEPTED->value || $l->getStatus() == LoanStatus::PENDING->value);
        })->count() >= 2;
    }

    function parseFormEquipmentData(&$data, array &$loanEquipment, array &$loanDiscriminators, array &$equipmentInfo): void
    {
        $ignored = ['comment', 'startDay', 'startTimeSlot', 'endDay', 'endTimeSlot', 'csrf_token', '_token'];
        if (is_array($data)) {
            foreach($data as $key => $value) {
                if (in_array($key, $ignored))
                    continue;

                if (is_array($value)) {
                    foreach($value as $v) {
                        $qty = $equipmentInfo[$v]->getQuantity();
                        if ($qty > 1 && $equipmentInfo[$v]->isNumbered())
                            $loanDiscriminators[$v] = random_int(1, $qty-1); // TODO : Random for now
                        
                        array_push($loanEquipment, $v);
                    }
                } else if (!empty($value)){
                    $qty = $equipmentInfo[$value]->getQuantity();
                    if ($qty > 1 && $equipmentInfo[$value]->isNumbered())
                        $loanDiscriminators[$value] = random_int(1, $qty-1); // TODO : Random for now
                    
                    array_push($loanEquipment, $value);	
                }
            }
        }
    }

    #[Route('/form/audiovisual', name: 'reservation_form_audiovisual')]
    public function audiovisualForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->hasReachedMaxConcurrentLoans($this->getUser())) {
            $this->addFlash('error','Vous avez atteint le maximum (2) de réservations en même temps.');
            return $this->redirectToRoute('app_main');
        }

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

        $unavailableDays = $entityManager->getRepository(UnavailableDays::class)->findInNextTwoWeeks(new \DateTime());

        // Create the form
        $form = $this->createForm(AudiovisualLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['audiovisual_loan'];
            
            $loan->setLoaner($this->getUser());
            $loan->setComment($data['comment']);
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data, $unavailableDays);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_audiovisual');
            
            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);
            
            // Add the equipment to the loan
            $loanEquipment = [];
            $loanDiscriminators = [];
            
            $this->parseFormEquipmentData($data, $loanEquipment, $loanDiscriminators, $equipmentInfo);

            $loan->setDiscriminators($loanDiscriminators);

            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $loans = $entityManager->getRepository(Loan::class)->findUnavailableBetweenDates($parsedDates['start'], $parsedDates['end']);
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
            'loanableDays' => json_encode(array_values($options['days'])),
            'equipmentInfoJson' => json_encode(array_map(function($e) { return [
                "quantity" => $e->getQuantity(),
                "name" => $e->getName()
            ]; }, $equipmentInfo)),
            'unavailableDays' => json_encode(array_map(function($u) { return [
                "start" => $u->getDateStart()->format('Y-m-d H:i:s'), // FIXME : ->format('c') returns ISO 8601 date, but timezone info is probably not configured properly. This will do :tm:
                "end" => $u->getDateEnd()->format('Y-m-d H:i:s'),
                "preventsLoans" => $u->isPreventsLoans()
            ]; }, $unavailableDays))
        ]);
    }
    
    #[Route('/form/vr', name: 'reservation_form_vr')]
    public function vrForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->hasReachedMaxConcurrentLoans($this->getUser())) {
            $this->addFlash('error','Vous avez atteint le maximum (2) de réservations en même temps.');
            return $this->redirectToRoute('app_main');
        }

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

        $unavailableDays = $entityManager->getRepository(UnavailableDays::class)->findInNextTwoWeeks(new \DateTime());

        // Create the form
        $form = $this->createForm(VRLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['vr_loan'];
            
            $loan->setLoaner($this->getUser());
            $loan->setComment($data['comment']);
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data, $unavailableDays);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_vr');

            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);

            // Add the equipment to the loan
            $loanEquipment = [];
            $loanDiscriminators = [];
            
            $this->parseFormEquipmentData($data, $loanEquipment, $loanDiscriminators, $equipmentInfo);

            $loan->setDiscriminators($loanDiscriminators);

            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }

            $loans = $entityManager->getRepository(Loan::class)->findUnavailableBetweenDates($parsedDates['start'], $parsedDates['end']);
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
            'loanableDays' => json_encode(array_values($options['days'])),
            'equipmentInfoJson' => json_encode(array_map(function($e) { return [
                "quantity" => $e->getQuantity(),
                "name" => $e->getName()
            ]; }, $equipmentInfo)),
            'unavailableDays' => json_encode(array_map(function($u) { return [
                "start" => $u->getDateStart()->format('Y-m-d H:i:s'), // FIXME : ->format('c') returns ISO 8601 date, but timezone info is probably not configured properly. This will do :tm:
                "end" => $u->getDateEnd()->format('Y-m-d H:i:s'),
                "preventsLoans" => $u->isPreventsLoans()
            ]; }, $unavailableDays))
        ]);
    }

    #[Route('/form/graphic-design', name: 'reservation_form_graphic_design')]
    public function graphicDesignForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $this->hasReachedMaxConcurrentLoans($this->getUser())) {
            $this->addFlash('error','Vous avez atteint le maximum (2) de réservations en même temps.');
            return $this->redirectToRoute('app_main');
        }

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

        $unavailableDays = $entityManager->getRepository(UnavailableDays::class)->findInNextTwoWeeks(new \DateTime());

        // Create the form
        $form = $this->createForm(GraphicDesignLoanType::class, $loan, $options);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['graphic_design_loan'];
            
            $loan->setLoaner($this->getUser());
            $loan->setComment($data['comment']);
            
            // Set the departure and return dates
            $parsedDates = $this->parseDepartureReturnDates($data, $unavailableDays);
            if ($parsedDates === false)
                return $this->redirectToRoute('reservation_form_graphic_design');
            
            $loan->setDepartureDate($parsedDates['start']);
            $loan->setReturnDate($parsedDates['end']);

            // Add the equipment to the loan
            $loanEquipment = [];
            $loanDiscriminators = [];
            
            $this->parseFormEquipmentData($data, $loanEquipment, $loanDiscriminators, $equipmentInfo);

            $loan->setDiscriminators($loanDiscriminators);
            
            if ($loanEquipment == [])
            {
                $this->addFlash('error','Vous devez sélectionner au moins un équipement.');
                return $this->redirectToRoute('reservation_form_audiovisual');
            }
        
            $loans = $entityManager->getRepository(Loan::class)->findUnavailableBetweenDates($parsedDates['start'], $parsedDates['end']);
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
            'loanableDays' => json_encode(array_values($options['days'])),
            'equipmentInfoJson' => json_encode(array_map(function($e) { return [
                "quantity" => $e->getQuantity(),
                "name" => $e->getName()
            ]; }, $equipmentInfo)),
            'unavailableDays' => json_encode(array_map(function($u) { return [
                "start" => $u->getDateStart()->format('Y-m-d H:i:s'), // FIXME : ->format('c') returns ISO 8601 date, but timezone info is probably not configured properly. This will do :tm:
                "end" => $u->getDateEnd()->format('Y-m-d H:i:s'),
                "preventsLoans" => $u->isPreventsLoans()
            ]; }, $unavailableDays))
        ]);
    }
}