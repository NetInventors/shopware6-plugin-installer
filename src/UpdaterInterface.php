<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\UpdateContext;

interface UpdaterInterface
{
    public function update(UpdateContext $updateContext): void;

    public function postUpdate(UpdateContext $updateContext): void;
}
