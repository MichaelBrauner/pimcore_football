<?php
declare(strict_types=1);

namespace App\Tests\Functional\Command;

use App\Kernel;
use Pimcore\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ImportTeamsCommandTest extends KernelTestCase
{
    private CommandTester $cmd;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected function setUp(): void
    {
        // First, let tests believe that the process is executed as a console application.
        define('PIMCORE_CONSOLE', true);

        parent::setUp();

        $application = new Application(self::bootKernel());
        $this->cmd = new CommandTester($application->find('app:import-teams'));
    }

    protected function tearDown(): void
    {
        // Reset the constant to avoid side effects.
        define('PIMCORE_CONSOLE', false);

        restore_exception_handler();
        parent::tearDown();
    }

    public function testCommandRunsSuccessful(): void
    {
        $this->cmd->execute([
            'file' => 'assets/documents/football_teams.xlsx',
        ]);

        $this->cmd->assertCommandIsSuccessful();
    }
}
