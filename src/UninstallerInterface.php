<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

interface UninstallerInterface
{
    public function uninstall(UninstallContext $uninstallContext): void;

    public function deactivate(DeactivateContext $deactivateContext): void;
}
