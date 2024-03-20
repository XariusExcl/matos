<?php

namespace App\Controller\Admin;

use App\Entity\Loan;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Entity\LoanStatus;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ReturnLoanType;
use Symfony\Component\HttpFoundation\Request;

class LoanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Loan::class;
    }

    public function loansPending(EntityManagerInterface $em): Response
    {
        $pendingLoans = $em->getRepository(Loan::class)->findPending();

        return $this->render('admin/loan/pending_content.html.twig', [
            'loans' => $pendingLoans,
        ]);
    }

    public function loansPendingReturn(EntityManagerInterface $em): Response
    {
        $pendingLoans = $em->getRepository(Loan::class)->findPendingReturn();

        return $this->render('admin/loan/pending_return_content.html.twig', [
            'loans' => $pendingLoans,
        ]);
    }
    
    public function loanReview(Loan $loan): Response
    {
        return $this->render('admin/loan/review_content.html.twig', [
            'loan' => $loan,
        ]);
    }

    public function loanReturn(Loan $loan, EntityManagerInterface $em): Response
    {
        // Create the form
        $options = [
            'equipmentLoaned' => $loan->getEquipmentLoaned(),
        ];

        $form = $this->createForm(ReturnLoanType::class, $loan, $options);

        $request = Request::createFromGlobals();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $request->request->all()["return_loan"];

            dump($data);

            if (!isset($data['equipment']) || count($data['equipment']) < $loan->getEquipmentLoaned()->count()) {
                $this->addFlash('error', 'Il manque des équipements à rendre.'); // FIXME : This message is displayed after one extra refresh somehow?

                return $this->render('admin/loan/return_content.html.twig', [
                    'loan' => $loan,
                    'form' => $form->createView(),
                ]);
            }

            // TODO : update the quantity of the equipment in the database
            // TODO : send an email to the user to notify him that his loan has been returned
            
            $loan->setStatus(LoanStatus::RETURNED->value);
            $em->persist($loan);
            $em->flush();
            
            $this->addFlash('success', 'Le rendu du matériel a bien été enregistré.'); // FIXME : This message is displayed after one extra refresh somehow?
            // return $this->redirectToRoute('admin'); // Can't reroute because we're in a nested controller render.
        }

        return $this->render('admin/loan/return_content.html.twig', [
            'loan' => $loan,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/loan/{id}/accept', name: 'admin_loan_accept')]
    public function loanAccepted(Loan $loan, EntityManagerInterface $em): Response
    {
        $emailComment = $_POST['loan-comment'];
        dump($emailComment);
        
        $loan->setStatus(LoanStatus::ACCEPTED->value);

        // TODO: Send an email to the user to notify him that his loan has been accepted
        $em->persist($loan);
        $em->flush();

        $this->addFlash('success', 'L\'emprunt a bien été accepté.');
        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/loan/{id}/refuse', name: 'admin_loan_refuse')]
    public function loanRefused(Loan $loan, EntityManagerInterface $em): Response
    {
        $emailComment = $_POST['loan-comment'];
        dump($emailComment);

        $loan->setStatus(LoanStatus::REFUSED->value);

        // TODO: Send an email to the user to notify him that his loan has been refused
        $em->persist($loan);
        $em->flush();

        $this->addFlash('error', 'L\'emprunt a été refusé.');
        return $this->redirectToRoute('admin');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('departure_date'),
            DateTimeField::new('return_date'),
            ChoiceField::new('status')->setChoices(LoanStatus::cases())->hideOnForm(),
            TextField::new('comment')->setFormTypeOption('disabled','disabled'),
            AssociationField::new('equipmentLoaned')
        ];
    }
    
}