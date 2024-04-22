<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Loan;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to the MATOS API!',
            'path' => 'src/Controller/ApiController.php',
        ]);
    }

    #[Route('/api/unavailable_equipment', name: 'api_unavailable_equipment', methods: ['GET'])]
    public function equipment(Request $request, EntityManagerInterface $entityManager): Response
    {
        $s = $request->query->get('s');
        $e = $request->query->get('e');

        if (!$s || !$e) {
            $start = new \DateTime();
            $end = new \DateTime();
            $end->modify("+14 day");
        } else {
            $start = new \DateTime($s);
            $end = new \DateTime($e);
            
            // Sanitize the dates to never exceed 14 days
            $start = ($start < new \DateTime() || $start > new \DateTime("+14 day")) ? new \DateTime() : $start;
            $end = $end < new \DateTime("+14 day") ? $end : new \DateTime("+14 day");

            if ($start > $end)
                return $this->json([
                    'error' => 'Invalid date range, start date must be before end date!',
                    'startDate' => $start,
                    'endDate' => $end,
                ]);
            
            if (!$start || !$end)
                return $this->json([
                    'error' => 'Invalid date format, use YYYY-MM-DD HH:MM!',
                ]);
        }

        $loans = $entityManager->getRepository(Loan::class)->findUnavailableBetweenDates($start, $end);

        $equipmentLoaned = [];
        
        foreach ($loans as $loan) 
            foreach($loan->getEquipmentLoaned() as $el)
                array_push($equipmentLoaned, $el->getId());

        $equipment = array_count_values($equipmentLoaned);

        return $this->json([
            'equipment' => $equipment,
            'count' => count($equipment),
            'startDate' => $start,
            'endDate' => $end
        ]);
    }
}
