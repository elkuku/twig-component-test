<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Elkuku\SymfonyUtils\Test\ControllerBaseTest;

class ControllerAdminAccessTest extends ControllerBaseTest
{
    protected string $controllerRoot = __DIR__.'/../../src/Controller';

    /**
     * @var array<string, array<string, array<string, int>>>
     */
    protected array $exceptions
        = [
            'app_default' => [
                'statusCodes' => ['GET' => 200],
            ],
            'login'   => [
                'statusCodes' => ['GET' => 200],
            ],

        ];

    /**
     * @var array<int, string>
     */
    protected array $ignoredFiles = [
        '.gitignore',
        'BaseController.php',
        'Security/GoogleController.php',
        'Security/GitHubController.php',
        'Security/GitLabController.php',
    ];

    public function testAllRoutesAreProtected(): void
    {
        $client = static::createClient();

        /**
         * @var UserRepository $userRepository
         */
        $userRepository = static::getContainer()->get(UserRepository::class);

        /**
         * @var \Symfony\Component\Security\Core\User\UserInterface $user
         */
        $user = $userRepository->findOneBy(['identifier' => 'admin']);

        $client->loginUser($user);

        $this->runTests($client);
    }
}
