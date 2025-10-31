<?php

declare(strict_types=1);

namespace PrepaidCardBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PrepaidCardBundle\PrepaidCardBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(PrepaidCardBundle::class)]
#[RunTestsInSeparateProcesses]
final class PrepaidCardBundleTest extends AbstractBundleTestCase
{
}
