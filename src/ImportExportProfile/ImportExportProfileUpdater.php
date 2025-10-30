<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

use NetInventors\Shopware6PluginInstaller\UpdaterInterface;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class ImportExportProfileUpdater implements UpdaterInterface
{
    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
    }

    #[\Override]
    public function update(UpdateContext $updateContext): void
    {
        $importExportInstaller = new ImportExportProfileInstaller($this->container, $this->directory);

        $importExportInstaller->install($updateContext);
    }

    #[\Override]
    public function postUpdate(UpdateContext $updateContext): void
    {
    }
}
