<?php

namespace PrepaidCardBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\Command\PrepaidCardExpireCheckCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardExpireCheckCommand::class)]
#[RunTestsInSeparateProcesses]
final class PrepaidCardExpireCheckCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function onSetUp(): void
    {
        $command = self::getService(PrepaidCardExpireCheckCommand::class);
        $this->commandTester = new CommandTester($command);
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    public function testGetName(): void
    {
        $this->assertEquals('prepaid-card:expire-check', PrepaidCardExpireCheckCommand::NAME);
    }

    public function testExecuteCommand(): void
    {
        $result = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $result);
        $this->assertStringContainsString('Prepaid card expire check completed', $this->commandTester->getDisplay());
    }

    public function testCommandIsRegistered(): void
    {
        $command = self::getService(PrepaidCardExpireCheckCommand::class);
        $this->assertInstanceOf(PrepaidCardExpireCheckCommand::class, $command);
        $this->assertEquals('prepaid-card:expire-check', $command->getName());
    }
}
