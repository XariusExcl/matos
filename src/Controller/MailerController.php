<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Loan;
use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;

class MailerController extends AbstractController
{
    public static function sendNewRequestMail(Loan $loan, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $_ENV['MAILER_ADMIN_EMAIL'];

        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('[ADMIN] Nouvelle demande d\'emprunt MATOS')
            ->htmlTemplate('mailer/admin_new_request.html.twig')
            ->context([
                'loan' => $loan
            ]);

        $mailer->send($email);
    }

    public static function sendRequestConfirmMail(Loan $loan, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $loan->getLoaner()->getEmail();


        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Confirmation de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_confirm.html.twig')
            ->context([
                'loan' => $loan
            ]);

            $mailer->send($email);
    }

    public static function sendRequestRefuseMail(Loan $loan, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $loan->getLoaner()->getEmail();

        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Refus de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_refuse.html.twig')
            ->context([
                'loan' => $loan
            ]);

        $mailer->send($email);
    }

    public static function sendRequestAcceptMail(Loan $loan, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $loan->getLoaner()->getEmail();

        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Acceptation de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_accept.html.twig')
            ->context([
                'loan' => $loan
            ]);

        $mailer->send($email);
    }

    public static function sendLoanReturnedMail(Loan $loan, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $loan->getLoaner()->getEmail();

        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Retour de votre matériel emprunté')
            ->htmlTemplate('mailer/user_loan_returned.html.twig')
            ->context([
                'loan' => $loan
            ]);

        $mailer->send($email);
    }

    public static function sendActivationTokenMail(User $user, string $link, MailerInterface $mailer)
    {
        if($_ENV['MAILER_ENABLE'] == 0) return;
        
        if ($_ENV['APP_ENV'] === 'dev')
            $mailto = $_ENV['MAILER_DEBUG_EMAIL'];
        else
            $mailto = $user->getEmail();

        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Activation de votre compte MATOS')
            ->htmlTemplate('mailer/user_activation_token.html.twig')
            ->context([
                'user' => $user,
                'activation_link' => $link
            ]);

        $mailer->send($email);
    }

    #[Route('/mail/test', name: 'email_test')]
    public function testEmail(MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        if ($_ENV['APP_ENV'] === 'prod')
            return new Response('Petit malin :)');

        if (!isset($_GET['e']))
            return new Response('No template specified');

        if(!isset($_GET['send']))
            $send = false;
        else
            $send = true;

        // Make a fake loan to test emails
        $equipment = $entityManager->getRepository(Equipment::class)->findAll();
        $loan = new Loan();
        $loan->setLoaner($this->getUser());
        $loan->setDepartureDate(new \DateTime());
        $loan->setReturnDate(new \DateTime('+1 week'));
        $loan->addEquipmentLoaned($equipment[rand(0, count($equipment) - 1)]);
        $loan->addEquipmentLoaned($equipment[rand(0, count($equipment) - 1)]);
        $loan->addEquipmentLoaned($equipment[rand(0, count($equipment) - 1)]);
        $loan->setComment('Ceci est un test');

        $template = $_GET['e'];
        switch ($template) {
            case 'user_request_confirm':
                if ($send)
                    MailerController::sendRequestConfirmMail($loan, $mailer);
                
                return $this->render('mailer/'.$template.'.html.twig', [
                    'loan' => $loan
                ]);
            break;
            case 'user_request_refuse':
                if ($send)
                    MailerController::sendRequestRefuseMail($loan, $mailer);
                
                return $this->render('mailer/'.$template.'.html.twig', [
                    'loan' => $loan
                ]);
            break;
            case 'user_request_accept':
                if ($send)
                    MailerController::sendRequestAcceptMail($loan, $mailer);
                
                return $this->render('mailer/'.$template.'.html.twig', [
                    'loan' => $loan
                ]);
            break;
            case 'admin_new_request':
                if ($send)
                    MailerController::sendNewRequestMail($loan, $mailer);
                
                return $this->render('mailer/'.$template.'.html.twig', [
                    'loan' => $loan
                ]);
            default:
                return new Response('Invalid template');
        }
    }
}
