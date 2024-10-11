<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\SignupType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    #[Route(path: '/sso/redirect/cas', name: 'sso_cas')]
    public function redirectToCas(): RedirectResponse
    {
        return $this->redirect('https://cas.univ-reims.fr/cas?service='.$this->generateUrl('cas_return', [],
                UrlGeneratorInterface::ABSOLUTE_URL));
    }

    #[Route('/signup', name: 'signup')]
    public function signup( Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($this->getParameter('ENABLE_URCA_LOGIN') === "1") {
            $this->addFlash('error', 'La création de compte par adresse mail est désactivée. Utilisez l\'option "Utiliser mon login URCA" pour vous connecter.');
            return $this->redirectToRoute('login');
        }

        $user = new User();

        $form = $this->createForm(SignupType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // If a user with the same email already exists
            if ($entityManager->getRepository(User::class)->findOneBy(['email' => $data->getEmail()]) != null) {
                // If the account is already active
                if ($data->isActive()) {
                    $this->addFlash('error', 'Ce compte existe déjà, veuillez vous connecter.');
                    return $this->redirectToRoute('signup');
                } else {
                    $this->addFlash('error', 'Ce compte existe déjà mais n\'est pas activé. Veuillez activer votre compte avec le lien de confirmation envoyé à ' . $data->getEmail() . '.');
                    return $this->redirectToRoute('login');
                }
            }

            // If the email matches the domains allowed
            $allowedDomains = ['univ-reims.fr', 'etudiant.univ-reims.fr'];
            $emailDomain = explode('@', $data->getEmail())[1];
            if (!in_array($emailDomain, $allowedDomains)) {
                $this->addFlash('error', 'Vous devez utiliser une adresse email de l\'université de Reims pour vous inscrire (univ-reims.fr).');
                return $this->redirectToRoute('signup');
            }

            $user->setEmail($data->getEmail());
            $plaintextPassword = $data->getPassword();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setActive(false);
            $user->setActivationToken(md5(random_bytes(16)));

            // Set name based on email (firstname.lastname@univ-reims.fr)
            $explode = explode('.', explode('@', $data->getEmail())[0]);
            if (count($explode) == 1)
                $user->setName(ucfirst($explode[0]));
            else
                $user->setName(ucfirst($explode[0]) . ' ' . ucfirst($explode[1]));

            // Send email to user
            $link = $this->generateUrl('activate', ['token' => $user->getActivationToken()], UrlGeneratorInterface::ABSOLUTE_URL);
            MailerController::sendActivationTokenMail($user, $link, $mailer);
            
            $this->addFlash('success', 'Votre compte a bien été créé ! Veuillez activer votre compte avec le lien de confirmation envoyé à ' . $user->getEmail() . '.');
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('login');
        }

        return $this->render('security/signup.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/activate/{token}', name: 'activate')]
    public function activate(string $token, EntityManagerInterface $entityManager): Response
    {
        if ($this->getParameter('ENABLE_URCA_LOGIN') === "1") {
            $this->addFlash('error', 'La création de compte par adresse mail est désactivée. Utilisez l\'option "Utiliser mon login URCA" pour vous connecter.');
            return $this->redirectToRoute('login');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['activationToken' => $token]);

        if ($user == null) {
            $this->addFlash('error', 'Ce token d\'activation est invalide.');
            return $this->redirectToRoute('login');
        }

        $user->setActive(true);
        $user->setActivationToken(null);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a bien été activé ! Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('login');
    }
}
