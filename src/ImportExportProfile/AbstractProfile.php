<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

abstract class AbstractProfile implements ImportExportProfileInterface
{
    use ImportExportProfileTrait;

    #[\Override]
    public function getFileMimeType(): string
    {
        return 'text/csv';
    }

    #[\Override]
    public function getDelimiter(): string
    {
        return ';';
    }

    #[\Override]
    public function getEnclosure(): string
    {
        return '"';
    }

    #[\Override]
    public function isSystemDefault(): bool
    {
        return false;
    }

    #[\Override]
    public function getConfig(): array
    {
        return [
            'createEntities' => false,
            'updateEntities' => true,
        ];
    }
}
