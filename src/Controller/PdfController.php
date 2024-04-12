<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Loan;
/*
use Symfony\Component\Routing\Generator\UrlGenerator;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
*/


class PdfController extends AbstractController
{
    /*
    #[Route('/pdf/loan/{id}', name: 'pdf_loan')]
    public function pdfLoan(string $id, Pdf $knpSnappyPdf)
    {
        $pageUrl = $this->generateUrl('pdf_loan_html', ['id' => $id], UrlGenerator::ABSOLUTE_URL);

        $knpSnappyPdf->setOption('no-outline', true);
        $knpSnappyPdf->setOption('page-size','LETTER');
        $knpSnappyPdf->setOption('encoding', 'UTF-8');

        return new PdfResponse(
            $knpSnappyPdf->getOutput($pageUrl)
        );
    }
    */

    #[Route('/pdf/loan_html/{id}', name: 'pdf_loan_html')]
    public function loanAction(Loan $loan)
    {
        return $this->render('pdf/loan.html.twig', [
            'loan' => $loan
        ]);
    }
}