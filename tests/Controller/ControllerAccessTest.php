<?php

namespace App\Tests\Controller;

use Elkuku\SymfonyUtils\Test\ControllerBaseTest;

class ControllerAccessTest extends ControllerBaseTest
{
    protected string $controllerRoot = __DIR__ . '/../../src/Controller';

    /**
     * @var array<int, string>
     */
    protected array $ignoredFiles
        = [
            '.gitignore',
            'BaseController.php',
            'Security/GoogleController.php',
            'Security/GitHubController.php',
            'Security/GitLabController.php',
        ];

    /**
     * @var array<string, array<string, array<string, int>>>
     */
    protected array $exceptions
        = [
            'app_default' => [
                'statusCodes' => ['GET' => 200],
            ],
            'login' => [
                'statusCodes' => ['GET' => 200],
            ],
        ];

    public function testAllRoutesAreProtected(): void
    {
        $this->runTests(static::createClient());
    }
}
