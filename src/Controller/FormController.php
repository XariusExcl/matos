<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\AudiovisualLoanType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use App\Entity\EquipmentCategory;
use App\Entity\Loan;
use App\Entity\Equipment;


class FormController extends AbstractController
{
    #[Route('/form/audiovisual', name: 'reservation_form_audiovisual')]
    public function cameraForm(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $category = $entityManager->getRepository(EquipmentCategory::class)->findBySlug('audiovisual');

        $loan = new Loan();
        
        // Options for the form
        $options = [];

        // Get the loanable equipment for the category
        $equipmentLoanable = $entityManager->getRepository(Equipment::class)->findLoanableByCategory($category->getId());

        // Group equipment by subcategory
        $options['equipmentCategories'] = [];
        $equipmentInfo = [];
        $equipmentQuantity = [];

        foreach($equipmentLoanable as $equipment)
        {
            $key = $equipment->getSubCategory()?->getSlug() ?? "";
            if (!isset($options['equipmentCategories'][$key]))
                $options['equipmentCategories'][$key] = [$equipment];
            else
                array_push($options['equipmentCategories'][$key], $equipment);
            
            // Store equipment info by id for frontend display
            $equipmentInfo[$equipment->getId()] = $equipment;
            $equipmentQuantity[$equipment->getId()] = $equipment->getQuantity();
        }

        // Get all subcategories
        /*
        $options['subCategories'] = [];
        foreach($entityManager->getRepository(EquipmentSubCategory::class)->findAll() as $subCategory)
            $options['subCategories'][$subCategory->getName()] = $subCategory->getFormDisplayType();
        */

        // Create dates for the next two weeks
        $options['days'] = [];
        $weekDays = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
        $dt = new \DateTime("today");
        $dt->modify("+1 day");
        for ($i = 0; $i < 14; $i++)
        {
            // Skip weekends
            $dw = $dt->format('N');

            if ($dw <= 5)
                $options['days'][$weekDays[$dw-1]." ".$dt->format('d/m')] = $i;

            $dt->modify('+1 day');
        }

        // Create the form
        $form = $this->createForm(AudiovisualLoanType::class, $loan, $options);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()['audiovisual_loan'];
            
            dump($data);
            
            // Set the departure and return dates
            $hours = ['morning' => ["9:15", "12:30"], 'afternoon' => ["14:00", "17:30"], 'evening' => ["17:30", "9:15"]];
            
            if ($data['day'] < 0 || $data['day'] > 13)
                throw new \Exception("Invalid day: ".$data['day']);
            if (!isset($data['timeSlot']) || !in_array("morning", $data['timeSlot']) && !in_array("afternoon", $data['timeSlot']) && !in_array("evening", $data['timeSlot']))
                throw new \Exception("Invalid time slot: ".var_dump($data['timeSlot']));
            else {
                $data['day'] += 1;
                $start = (new \DateTime("today"))
                    ->modify("+".$data['day']." day")
                    ->modify($hours[$data['timeSlot'][0]][0]);
                
                $end = (new \DateTime("today"))
                    ->modify("+".($data['day'] + ((end($data['timeSlot']) == 'evening')? 1 : 0))." day")
                    ->modify($hours[end($data['timeSlot'])][1]);

                $loan->setDepartureDate($start);
                $loan->setReturnDate($end);
            }

            // Check if all the equipment is available
            $loans = $entityManager->getRepository(Loan::class)->findInBetweenDates($start, $end);
            $equipmentAlreadyLoaned = [];
            foreach ($loans as $loan) 
                foreach($loan->getEquipmentLoaned() as $el)
                    array_push($equipmentAlreadyLoaned, $el->getId());

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

            foreach($loanEquipment as $equipment)
            {
                if (in_array($equipment, $equipmentAlreadyLoaned))
                {
                    $this->addFlash('error','Un ou plusieurs équipements sont déjà réservés pour cette période.');
                    return $this->redirectToRoute('reservation_form_audiovisual');
                }
                $loan->addEquipmentLoaned($equipmentInfo[$equipment]);
            }

            $entityManager->persist($loan);
            $entityManager->flush();

            MailerController::sendRequestConfirmMail("John Doe", $mailer); // FIXME : This is a placeholder email

            $this->addFlash('success', 'Votre réservation a bien été enregistrée.');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('form/audiovisual.html.twig', [
            'formName' => 'Emprunt Audiovisuel',
            'form' => $form,
            'equipmentInfo' => $equipmentInfo,
            'equipmentQuantity' => json_encode($equipmentQuantity),
        ]);
    }
}