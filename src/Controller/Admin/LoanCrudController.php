<?php

namespace App\Controller\Admin;

use App\Controller\MailerController;
use App\Entity\Loan;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use App\Entity\LoanStatus;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ReturnLoanType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;

class LoanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Loan::class;
    }
    
    public function loanReview(Loan $loan): Response
    {
        return $this->render('admin/loan/review_content.html.twig', [
            'loan' => $loan,
        ]);
    }

    public function loanReturn(Loan $loan, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $redirect = false;
        if ($loan->getStatus() == LoanStatus::ACCEPTED->value)
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
    
                if (!isset($data['equipment']) || count($data['equipment']) < $loan->getEquipmentLoaned()->count()) {
                    $this->addFlash('error', 'Il manque des équipements à rendre.'); // FIXME : This message is displayed after one extra refresh somehow?
    
                    return $this->render('admin/loan/return_content.html.twig', [
                        'loan' => $loan,
                        'form' => $form->createView(),
                        'redirect' => $redirect,
                    ]);
                }
                
                MailerController::sendLoanReturnedMail($loan, $mailer);
                
                $loan->setStatus(LoanStatus::RETURNED->value);
                $em->persist($loan);
                $em->flush();
                
                $this->addFlash('success', 'Le rendu du matériel a bien été enregistré.'); // FIXME : This message is displayed after one extra refresh somehow?
                $redirect = true;
            }
        }

        return $this->render('admin/loan/return_content.html.twig', [
            'loan' => $loan,
            'form' => $form->createView(),
            'redirect' => $redirect,
        ]);
    }

    #[Route('/admin/loan/{id}/accept', name: 'admin_loan_accept')]
    public function loanAccepted(Loan $loan, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $emailComment = $_POST['loan-comment'];

        $loan->setStatus(LoanStatus::ACCEPTED->value);
        $loan->setAdminComment($emailComment);
        $loan->setAssignee($this->getUser());

        MailerController::sendRequestAcceptMail($loan, $mailer);

        $em->persist($loan);
        $em->flush();

        $this->addFlash('success', 'L\'emprunt a bien été accepté.');
        return $this->redirectToRoute('admin');
    }

    #[Route('/admin/loan/{id}/refuse', name: 'admin_loan_refuse')]
    public function loanRefused(Loan $loan, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $emailComment = $_POST['loan-comment'];

        $loan->setStatus(LoanStatus::REFUSED->value);
        $loan->setAdminComment($emailComment);

        MailerController::sendRequestRefuseMail($loan, $mailer);

        $em->persist($loan);
        $em->flush();

        $this->addFlash('error', 'L\'emprunt a été refusé.');
        return $this->redirectToRoute('admin');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['departure_date' => 'DESC']);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('loaner'),
            DateTimeField::new('departure_date'),
            DateTimeField::new('return_date'),
            ChoiceField::new('status')->setChoices(LoanStatus::cases())->hideOnForm(),
            TextareaField::new('comment'),
            AssociationField::new('assignee'),
            AssociationField::new('equipmentLoaned')
        ];
    }
    
}