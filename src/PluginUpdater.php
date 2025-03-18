<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller;

use Shopware\Core\Framework\Plugin\Context\UpdateContext;

final class PluginUpdater implements UpdaterInterface
{
    /**
     * @var list<UpdaterInterface>
     */
    private array $updaterCollection = [];

    public function registerUpdater(UpdaterInterface $updater): void
    {
        $this->updaterCollection[] = $updater;
    }

    #[\Override]
    public function update(UpdateContext $updateContext): void
    {
        foreach ($this->updaterCollection as $updater) {
            $updater->update($updateContext);
        }
    }

    #[\Override]
    public function postUpdate(UpdateContext $updateContext): void
    {
        foreach ($this->updaterCollection as $updater) {
            $updater->postUpdate($updateContext);
        }
    }
}
