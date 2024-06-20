<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class FlowBuilderUpdater implements UpdaterInterface
{
    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
    }

    public function update(UpdateContext $updateContext): void
    {
        $flowInstaller = new FlowBuilderInstaller($this->container, $this->directory);

        $flowInstaller->install($updateContext);
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
    }
}
