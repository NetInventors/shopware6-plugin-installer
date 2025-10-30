<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class CustomFieldUpdater implements UpdaterInterface
{
    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
    }

    #[\Override]
    public function update(UpdateContext $updateContext): void
    {
        $customFieldInstaller = new CustomFieldInstaller($this->container, $this->directory);

        $customFieldInstaller->install($updateContext);
    }

    #[\Override]
    public function postUpdate(UpdateContext $updateContext): void
    {
    }
}
