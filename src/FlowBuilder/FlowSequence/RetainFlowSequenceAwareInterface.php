<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence;

interface RetainFlowSequenceAwareInterface
{
    public function shouldRemoveOnUninstall(): bool;
}
