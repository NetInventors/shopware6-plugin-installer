<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Path;

final class FlowEventCollectionFactory
{
    /**
     * @var array<string, FlowEventCollection>
     */
    private static array $cache = [];

    public static function create(ContainerInterface $container, string $directory): FlowEventCollection
    {
        $configurationFile = Path::join($directory, 'Resources/config/setup/flow-builder.php');

        if (isset(self::$cache[$configurationFile])) {
            return self::$cache[$configurationFile];
        }

        /** @var list<class-string<FlowEventInterface>> $flowBuilderConfiguration */
        $flowBuilderConfiguration = [];

        if (\is_file($configurationFile)) {
            /**
             * @noinspection UsingInclusionOnceReturnValueInspection
             * @var list<class-string<FlowEventInterface>> $flowBuilderConfiguration
             */
            $flowBuilderConfiguration = (array) require_once $configurationFile;
        }

        self::$cache[$configurationFile] = new FlowEventCollection($container, $flowBuilderConfiguration);

        return self::$cache[$configurationFile];
    }
}
