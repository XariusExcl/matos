<?php
/*
 * Forked from davidannebicque/intranetV3
 * 
 * Copyright (c) 2022. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/intranetV3/src/Security/LoginCasAuthenticator.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 10/12/2022 10:25
 */

namespace App\Security;

use App\Event\CASAuthenticationFailureEvent;
use Exception;
use phpCAS;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class LoginCasAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly RouterInterface $router,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return '/sso/cas' === $request->getPathInfo();
    }

    public function authenticate(Request $request): Passport
    {
        $username = $this->getCredentials();
        if (null === $username) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            throw new CustomUserMessageAuthenticationException('Authentification CAS incorrecte. Utilisateur inconnu.');
        }

        
        // If user with email doesn't exist, create and flush it
        if ($this->userRepository->findBy(['email' => $username]) == null)
        {
            $user = new User();
            $user->setEmail($username);
            $user->setRoles(['ROLE_USER']);
            $user->setActive(true);
            $explode = explode('.', explode('@', $username)[0]);
            if (count($explode) == 1)
                $user->setName(ucfirst($explode[0]));
            else
                $user->setName(ucfirst($explode[0]) . ' ' . ucfirst($explode[1]));

            $user->setPassword('');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new SelfValidatingPassport(new UserBadge($username));
    }

    public function getCredentials(): ?string
    {
        $cas_host = $this->parameterBag->get('CAS_HOST');
        $cas_context = $this->parameterBag->get('CAS_CONTEXT');
        $cas_port = (int)$this->parameterBag->get('CAS_PORT');
        $client_service_name = $this->parameterBag->get('CAS_CLIENT_SERVICE_NAME');
        // phpCAS::setVerbose(true);
        phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context, $client_service_name);
        phpCAS::setFixedServiceURL($this->urlGenerator->generate('cas_return', [],
            UrlGeneratorInterface::ABSOLUTE_URL));
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();

        if (phpCAS::getAttribute("mail")) {
            return phpCAS::getAttribute("mail");
        }

        return null;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse(
            $this->router->generate('app_main')
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        $def_response = new RedirectResponse(
            $this->router->generate('login', ['message' => $data])
        ); // new JsonResponse($data, Response::HTTP_FORBIDDEN);

        return $def_response;

        # throw new Exception(strtr($exception->getMessageKey(), $exception->getMessageData()));
        return new RedirectResponse(
            $this->router->generate('login')
        );
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('login')
        );
    }
}
