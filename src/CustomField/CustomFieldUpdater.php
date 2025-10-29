<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField;

use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CustomFieldUpdater implements UpdaterInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    #[\Override]
    public function update(UpdateContext $updateContext): void
    {
        (new CustomFieldInstaller($this->container))->install($updateContext);
    }

    #[\Override]
    public function postUpdate(UpdateContext $updateContext): void
    {
    }
}
