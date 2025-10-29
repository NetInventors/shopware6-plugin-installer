<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Path;

final class CustomFieldSetCollectionFactory
{
    /**
     * @var array<string, CustomFieldSetCollection>
     */
    private static array $cache = [];

    public static function create(ContainerInterface $container): CustomFieldSetCollection
    {
        $configurationFile = Path::join(\dirname(__DIR__, 3), 'Resources/config/setup/custom-field-set.php');

        if (isset(self::$cache[$configurationFile])) {
            return self::$cache[$configurationFile];
        }

        /** @var list<class-string<CustomFieldSetInterface>> $customFieldSetConfiguration */
        $customFieldSetConfiguration = [];

        if (\is_file($configurationFile)) {
            /** @var list<class-string<CustomFieldSetInterface>> $customFieldSetConfiguration */
            $customFieldSetConfiguration = (array) require_once $configurationFile;
        }

        self::$cache[$configurationFile] = new CustomFieldSetCollection($container, $customFieldSetConfiguration);

        return self::$cache[$configurationFile];
    }
}
