<?php

namespace PrepaidCardBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class PrepaidCardExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
