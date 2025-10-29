<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

trait ImportExportProfileTrait
{
    protected string|null $id = null;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string|null
    {
        return $this->id;
    }
}
