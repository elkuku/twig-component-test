<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use Omines\OAuth2\Client\Provider\GitlabResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class GitLabAuthenticator extends AbstractAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/connect/check/gitlab';
    }

    /**
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function authenticate(Request $request): Passport
    {
        $token = $this->getClient()->getAccessToken();

        /** @var GitlabResourceOwner $gitlabResourceOwner */
        $gitlabResourceOwner = $this->getClient()
            ->fetchUserFromToken($token);

        $user = $this->getUser($gitlabResourceOwner);

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
            [new RememberMeBadge()],
        );
    }

    private function getClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('gitlab');
    }

    private function getUser(GitlabResourceOwner $resourceOwner): User
    {
        // 1) have they logged in with GitHub before? Easy!
        if ($user = $this->userRepository->findOneBy(
            ['gitLabId' => $resourceOwner->getId()]
        )
        ) {
            return $user;
        }

        // @todo remove: Fetch user by identifier
        if ($user = $this->userRepository->findOneBy(
            ['identifier' => $resourceOwner->getUsername()]
        )
        ) {
            // @todo remove: Update existing users GitHub id
            $user->setGitLabId($resourceOwner->getId());
        } else {
            // Register new user
            $user = (new User())
                ->setIdentifier($resourceOwner->getUsername())
                ->setGitLabId($resourceOwner->getId());
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): RedirectResponse {
        if ($targetPath = $this->getTargetPath(
            $request->getSession(),
            $firewallName
        )
        ) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('default'));
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): RedirectResponse {
        $message = strtr(
            $exception->getMessageKey(),
            $exception->getMessageData()
        );

        /**
         * @var Session $session
         */
        $session = $request->getSession();
        $session->getFlashBag()->add('danger', $message);

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
