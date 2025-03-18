<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Symfony\Component\Filesystem\Path;

final class MailTemplateCollectionFactory
{
    /**
     * @var array<string, MailTemplateCollection>
     */
    private static array $cache = [];

    public static function create(string $directory): MailTemplateCollection
    {
        $configurationFile = Path::join($directory, 'Resources/config/setup/mail-templates.php');

        if (isset(self::$cache[$configurationFile])) {
            return self::$cache[$configurationFile];
        }

        /** @var array<class-string<MailTemplateInterface>, list<string>> $mailTemplatesConfiguration */
        $mailTemplatesConfiguration = [];

        if (\is_file($configurationFile)) {
            /**
             * @noinspection UsingInclusionOnceReturnValueInspection
             * @var array<class-string<MailTemplateInterface>, list<string>> $mailTemplatesConfiguration
             */
            $mailTemplatesConfiguration = (array) require_once $configurationFile;
        }

        self::$cache[$configurationFile] = new MailTemplateCollection($directory, $mailTemplatesConfiguration);

        return self::$cache[$configurationFile];
    }
}
