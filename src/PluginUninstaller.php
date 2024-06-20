<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

final class PluginUninstaller implements UninstallerInterface
{
    /**
     * @var list<UninstallerInterface>
     */
    private array $uninstallerCollection = [];

    public function registerUninstaller(UninstallerInterface $uninstaller): void
    {
        $this->uninstallerCollection[] = $uninstaller;
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        foreach ($this->uninstallerCollection as $uninstaller) {
            $uninstaller->uninstall($uninstallContext);
        }
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        foreach ($this->uninstallerCollection as $uninstaller) {
            $uninstaller->deactivate($deactivateContext);
        }
    }
}
