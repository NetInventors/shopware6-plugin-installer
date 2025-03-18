<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractFlowEvent implements FlowEventInterface
{
    use FlowEventTrait;

    public function __construct(
        protected readonly ContainerInterface $container,
    ) {
    }

    #[\Override]
    public function isActive(): bool
    {
        return true;
    }
}
