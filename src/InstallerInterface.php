<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;

interface InstallerInterface
{
    public function install(InstallContext $installContext): void;

    public function postInstall(InstallContext $installContext): void;

    public function activate(ActivateContext $activateContext): void;
}
