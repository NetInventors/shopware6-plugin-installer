<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;

final class PluginInstaller implements InstallerInterface
{
    /**
     * @var list<InstallerInterface>
     */
    private array $installerCollection = [];

    public function registerInstaller(InstallerInterface $installer): void
    {
        $this->installerCollection[] = $installer;
    }

    public function install(InstallContext $installContext): void
    {
        foreach ($this->installerCollection as $installer) {
            $installer->install($installContext);
        }
    }

    public function postInstall(InstallContext $installContext): void
    {
        foreach ($this->installerCollection as $installer) {
            $installer->postInstall($installContext);
        }
    }

    public function activate(ActivateContext $activateContext): void
    {
        foreach ($this->installerCollection as $installer) {
            $installer->activate($activateContext);
        }
    }
}
