<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Path;

final class ImportExportProfileCollectionFactory
{
    /**
     * @var array<string, ImportExportProfileCollection>
     */
    private static array $cache = [];

    public static function create(ContainerInterface $container): ImportExportProfileCollection
    {
        $configurationFile = Path::join(\dirname(__DIR__, 2), 'Resources/config/setup/import-export-profile.php');

        if (isset(self::$cache[$configurationFile])) {
            return self::$cache[$configurationFile];
        }

        /** @var list<class-string<ImportExportProfileInterface>> $importBuilderConfiguration */
        $importBuilderConfiguration = [];

        if (\is_file($configurationFile)) {
            /** @var list<class-string<ImportExportProfileInterface>> $importBuilderConfiguration */
            $importBuilderConfiguration = (array) require_once $configurationFile;
        }

        self::$cache[$configurationFile] = new ImportExportProfileCollection($container, $importBuilderConfiguration);

        return self::$cache[$configurationFile];
    }
}
