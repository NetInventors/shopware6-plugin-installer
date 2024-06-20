<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Symfony\Component\Filesystem\Path;

class MailTemplateCollectionFactory
{
    public static function create(string $directory): MailTemplateCollection
    {
        $configurationFile = Path::join($directory, 'Resources/config/setup/mail-templates.php');

        /** @var array<class-string<MailTemplateInterface>, list<string>> $mailTemplatesConfiguration */
        $mailTemplatesConfiguration = [];

        if (\is_file($configurationFile)) {
            /** @var array<class-string<MailTemplateInterface>, list<string>> $mailTemplatesConfiguration */
            $mailTemplatesConfiguration = (array) require $configurationFile;
        }

        return new MailTemplateCollection($directory, $mailTemplatesConfiguration);
    }
}
