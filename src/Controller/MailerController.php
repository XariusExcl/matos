<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class MailerController extends AbstractController
{
    public static function sendNewRequestMail(string $mailto, MailerInterface $mailer)
    {
        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('[ADMIN] Nouvelle demande d\'emprunt MATOS')
            ->htmlTemplate('mailer/admin_new_request.html.twig')
            ->context([
                'name' => 'John Doe',
                'date_start' => new \DateTime(),
                'date_end' => new \DateTime('+7 days')
            ]);

        $mailer->send($email);
    }

    public static function sendRequestConfirmMail(string $mailto, MailerInterface $mailer)
    {
        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Confirmation de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_confirm.html.twig')
            ->context([
                'name' => 'John Doe',
                'date_start' => new \DateTime(),
                'date_end' => new \DateTime('+7 days')
            ]);

            $mailer->send($email);
    }

    public static function sendRequestRefuseMail(string $mailto, MailerInterface $mailer)
    {
        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Refus de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_refuse.html.twig')
            ->context([
                'name' => 'John Doe',
                'reason' => '',
                'date_start' => new \DateTime(),
                'date_end' => new \DateTime('+7 days')
            ]);

        $mailer->send($email);
    }

    public static function sendRequestAcceptMail(string $mailto, MailerInterface $mailer)
    {
        $email = (new TemplatedEmail())
            ->to($mailto)
            ->subject('Acceptation de votre demande d\'emprunt')
            ->htmlTemplate('mailer/user_request_accept.html.twig')
            ->context([
                'name' => 'John Doe',
                'date_start' => new \DateTime(),
                'date_end' => new \DateTime('+7 days')
            ]);

        $mailer->send($email);
    }

    #[Route('/mail/test', name: 'email_test')]
    public function testEmail()
    {
        $template = $_GET['e'];
        switch ($template) {
            case 'user_request_confirm':
                return $this->render('mailer/'.$template.'.html.twig', [
                    'name' => 'John Doe',
                    'date_start' => new \DateTime(),
                    'date_end' => new \DateTime('+7 days')
                ]);
            break;
            case 'user_request_refuse':
                return $this->render('mailer/'.$template.'.html.twig', [
                    'name' => 'John Doe',
                    'reason' => 'Skill issue',
                    'date_start' => new \DateTime(),
                    'date_end' => new \DateTime('+7 days')
                ]);
            break;
            case 'user_request_accept':
                return $this->render('mailer/'.$template.'.html.twig', [
                    'name' => 'John Doe',
                    'date_start' => new \DateTime(),
                    'date_end' => new \DateTime('+7 days')
                ]);
            break;
            case 'admin_new_request':
                return $this->render('mailer/'.$template.'.html.twig', [
                    'name' => 'John Doe',
                    'date_start' => new \DateTime(),
                    'date_end' => new \DateTime('+7 days')
                ]);
            default:
                return new Response('Invalid template');
        }
    }
}
