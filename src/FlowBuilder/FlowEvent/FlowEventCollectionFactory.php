<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Path;

class FlowEventCollectionFactory
{
    public static function create(ContainerInterface $container, string $directory): FlowEventCollection
    {
        $configurationFile = Path::join($directory, 'Resources/config/setup/flow-builder.php');

        /** @var list<class-string<FlowEventInterface>> $flowBuilderConfiguration */
        $flowBuilderConfiguration = [];

        if (\is_file($configurationFile)) {
            /** @var list<class-string<FlowEventInterface>> $flowBuilderConfiguration */
            $flowBuilderConfiguration = (array) require $configurationFile;
        }

        return new FlowEventCollection($container, $flowBuilderConfiguration);
    }
}
