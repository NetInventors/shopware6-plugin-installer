<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class FlowBuilderUpdater implements UpdaterInterface
{
    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
    }

    #[\Override]
    public function update(UpdateContext $updateContext): void
    {
        $flowInstaller = new FlowBuilderInstaller($this->container, $this->directory);

        $flowInstaller->install($updateContext);
    }

    #[\Override]
    public function postUpdate(UpdateContext $updateContext): void
    {
    }
}
